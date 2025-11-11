<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display activity feed.
     */
    public function index(Request $request): View
    {
        $activities = Activity::with(['user', 'subject'])
            ->when($request->type, function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('activity.index', compact('activities'));
    }

    /**
     * Display activity feed for followed users.
     */
    public function following(Request $request): View
    {
        $followingIds = auth()->user()->following()->pluck('following_id');

        $activities = Activity::whereIn('user_id', $followingIds)
            ->with(['user', 'subject'])
            ->when($request->type, function ($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('activity.following', compact('activities'));
    }
}
