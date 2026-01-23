<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserBlockController extends Controller
{
    public function block(Request $request, User $user)
    {
        $me = $request->user();
        if ($me->id === $user->id) {
            return response()->json(['error' => 'cannot_block_self'], 422);
        }
        DB::table('user_blocks')->updateOrInsert(
            ['blocker_id' => $me->id, 'blocked_id' => $user->id],
            ['updated_at' => now(), 'created_at' => now()]
        );
        return response()->json(['message' => 'blocked']);
    }

    public function unblock(Request $request, User $user)
    {
        $me = $request->user();
        DB::table('user_blocks')->where('blocker_id', $me->id)->where('blocked_id', $user->id)->delete();
        return response()->json(['message' => 'unblocked']);
    }

    public function list(Request $request)
    {
        $me = $request->user();
        $users = User::whereIn('id', function ($q) use ($me) {
            $q->select('blocked_id')->from('user_blocks')->where('blocker_id', $me->id);
        })->get(['id','name','business_name','avatar']);
        return response()->json(['blocked' => $users]);
    }
}
