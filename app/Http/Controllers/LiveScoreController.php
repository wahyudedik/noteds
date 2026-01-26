<?php

namespace App\Http\Controllers;

use App\Services\FootballDataService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LiveScoreController extends Controller
{
    protected $footballService;

    public function __construct(FootballDataService $footballService)
    {
        $this->footballService = $footballService;
    }

    /**
     * API Endpoint for Live Score Widget
     */
    public function getLiveScores()
    {
        $matches = $this->footballService->getLiveMatches();
        return response()->json($matches);
    }

    /**
     * Full Page View for All Matches
     */
    public function index()
    {
        // Get scheduled matches (including live ones)
        $matches = $this->footballService->getScheduledMatches();

        return Inertia::render('Explorer/LiveScore/Index', [
            'matches' => $matches,
        ]);
    }
}
