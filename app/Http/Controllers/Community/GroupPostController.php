<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupPost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GroupPostController extends Controller
{
    public function index(Request $request, string $slug): Response
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $posts = GroupPost::where('group_id', $group->id)
            ->with('user')
            ->latest()
            ->paginate(20);
        return Inertia::render('Groups/Show', [
            'group' => $group,
            'posts' => $posts,
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        $user = $request->user();
        $member = GroupMember::where('group_id', $group->id)->where('user_id', $user->id)->where('status', 'active')->first();
        if (!$member) {
            abort(403);
        }
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visibility' => 'nullable|in:members,public',
        ]);
        GroupPost::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'title' => $request->title,
            'content' => $request->content,
            'visibility' => $request->visibility ?: 'members',
        ]);
        return back()->with('success', 'Post created.');
    }

    public function update(Request $request, string $slug, GroupPost $post)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        if ($post->group_id !== $group->id || $post->user_id !== $request->user()->id) {
            abort(403);
        }
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'visibility' => 'nullable|in:members,public',
        ]);
        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'visibility' => $request->visibility ?: 'members',
        ]);
        return back()->with('success', 'Post updated.');
    }

    public function destroy(Request $request, string $slug, GroupPost $post)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        if ($post->group_id !== $group->id || $post->user_id !== $request->user()->id) {
            abort(403);
        }
        $post->delete();
        return back()->with('success', 'Post deleted.');
    }
}
