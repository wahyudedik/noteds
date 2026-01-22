<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Group::query()->where('is_active', true);
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($privacy = $request->query('privacy')) {
            $query->where('privacy', $privacy);
        }
        $groups = $query->latest()->paginate(20);
        return Inertia::render('Groups/Index', [
            'groups' => $groups,
            'filters' => [
                'q' => $search,
                'privacy' => $privacy,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'privacy' => 'required|in:public,private,secret',
        ]);
        $user = $request->user();
        $group = Group::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(6),
            'description' => $request->description,
            'privacy' => $request->privacy,
            'owner_id' => $user->id,
            'is_active' => true,
        ]);
        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => 'admin',
            'status' => 'active',
        ]);
        return redirect()->route('groups.show', $group->slug)->with('success', 'Group created.');
    }

    public function show(Request $request, string $slug): Response
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $members = GroupMember::where('group_id', $group->id)->with('user')->get();
        return Inertia::render('Groups/Show', [
            'group' => $group,
            'members' => $members,
        ]);
    }

    public function join(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $user = $request->user();
        $exists = GroupMember::where('group_id', $group->id)->where('user_id', $user->id)->first();
        if ($exists) {
            return back()->with('info', 'Already a member.');
        }
        $status = $group->privacy === 'public' ? 'active' : 'pending';
        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => 'member',
            'status' => $status,
        ]);
        return back()->with('success', 'Join request submitted.');
    }

    public function leave(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $user = $request->user();
        GroupMember::where('group_id', $group->id)->where('user_id', $user->id)->delete();
        return back()->with('success', 'Left group.');
    }

    public function approve(Request $request, string $slug, string $memberId)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        if ($group->owner_id !== $request->user()->id) {
            abort(403);
        }
        $member = GroupMember::where('id', $memberId)->where('group_id', $group->id)->firstOrFail();
        $member->update(['status' => 'active']);
        return back()->with('success', 'Member approved.');
    }

    public function changeRole(Request $request, string $slug, string $memberId)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        if ($group->owner_id !== $request->user()->id) {
            abort(403);
        }
        $request->validate([
            'role' => 'required|in:admin,moderator,member',
        ]);
        $member = GroupMember::where('id', $memberId)->where('group_id', $group->id)->firstOrFail();
        $member->update(['role' => $request->role]);
        return back()->with('success', 'Role updated.');
    }
}
