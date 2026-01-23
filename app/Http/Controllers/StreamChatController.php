<?php

namespace App\Http\Controllers;

use App\Events\LiveChatMessageCreated;
use App\Models\LiveChatMessage;
use App\Models\LiveStream;
use App\Models\GroupMember;
use Illuminate\Http\Request;

class StreamChatController extends Controller
{
    public function store(Request $request, LiveStream $liveStream)
    {
        $this->authorize('view', $liveStream);
        if ($liveStream->group_only && $liveStream->group_id) {
            $isMember = GroupMember::where('group_id', $liveStream->group_id)->where('user_id', $request->user()->id)->exists();
            if (!$isMember) {
                return response()->json(['error' => 'forbidden'], 403);
            }
        }
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);
        $msg = LiveChatMessage::create([
            'live_stream_id' => $liveStream->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);
        broadcast(new LiveChatMessageCreated($msg))->toOthers();
        return response()->json(['success' => true, 'message' => $msg]);
    }
}
