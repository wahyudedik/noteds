<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Notification;
use App\Notifications\GroupInviteNotification;

class GroupInviteController extends Controller
{
    protected function ensureAdmin(Request $request, Group $group): void
    {
        if ($group->owner_id === $request->user()->id) {
            return;
        }
        $member = GroupMember::where('group_id', $group->id)->where('user_id', $request->user()->id)->first();
        if (!$member || !in_array($member->role, ['admin', 'moderator'])) {
            abort(403);
        }
    }

    public function index(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $this->ensureAdmin($request, $group);
        $invites = GroupInvite::where('group_id', $group->id)->latest()->paginate(20);
        return Inertia::render('Groups/Invites', [
            'group' => $group,
            'invites' => $invites,
        ]);
    }

    public function createEmail(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $this->ensureAdmin($request, $group);
        $data = $request->validate([
            'email' => 'required|email',
            'expires_at' => 'nullable|date',
        ]);
        $token = Str::random(40);
        $invite = GroupInvite::create([
            'group_id' => $group->id,
            'inviter_id' => $request->user()->id,
            'email' => $data['email'],
            'token' => $token,
            'expires_at' => $data['expires_at'] ?? now()->addDays(7),
        ]);
        Notification::route('mail', $data['email'])->notify(new GroupInviteNotification($invite));
        event(new \App\Events\GroupInviteCreated($invite));
        return back()->with([
            'success' => 'Invite created.',
            'invite_link' => route('groups.invites.show', $invite->token),
        ]);
    }

    public function createLink(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $this->ensureAdmin($request, $group);
        $data = $request->validate([
            'expires_at' => 'nullable|date',
        ]);
        $token = Str::random(40);
        $invite = GroupInvite::create([
            'group_id' => $group->id,
            'inviter_id' => $request->user()->id,
            'token' => $token,
            'expires_at' => $data['expires_at'] ?? now()->addDays(7),
        ]);
        return back()->with([
            'success' => 'Invite link generated.',
            'invite_link' => route('groups.invites.show', $invite->token),
        ]);
    }

    public function show(string $token)
    {
        $invite = GroupInvite::where('token', $token)->firstOrFail();
        $group = Group::findOrFail($invite->group_id);
        return Inertia::render('Groups/InviteAccept', [
            'group' => $group,
            'invite' => $invite,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invite = GroupInvite::where('token', $token)->firstOrFail();
        if ($invite->expires_at && now()->greaterThan($invite->expires_at)) {
            return back()->withErrors(['invite' => 'Invite expired.']);
        }
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }
        if ($invite->email && strtolower($invite->email) !== strtolower($user->email)) {
            return back()->withErrors(['invite' => 'Email mismatch.']);
        }
        $group = Group::findOrFail($invite->group_id);
        $exists = GroupMember::where('group_id', $group->id)->where('user_id', $user->id)->first();
        if (!$exists) {
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'role' => 'member',
                'status' => 'active',
            ]);
        }
        $invite->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
        $invite->increment('click_count');
        $invite->last_clicked_at = now();
        $invite->save();

        if ($invite->inviter) {
            app(\App\Services\GamificationService::class)->awardAction($invite->inviter, 'invite_accepted', [
                'group_invite_id' => $invite->id,
                'group_id' => $group->id,
            ]);
        }
        return redirect()->route('groups.show', $group->slug)->with('success', 'Joined group.');
    }

    public function decline(Request $request, string $token)
    {
        $invite = GroupInvite::where('token', $token)->firstOrFail();
        $invite->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);
        $invite->increment('click_count');
        $invite->last_clicked_at = now();
        $invite->save();
        return back()->with('info', 'Invite declined.');
    }
}
