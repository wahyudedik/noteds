<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class FootballDataService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.football_data.base_url');
        $this->apiKey = config('services.football_data.key');
    }

    /**
     * Get live matches (In Play)
     */
    public function getLiveMatches()
    {
        // Cache for 60 seconds to avoid hitting rate limits (10 req/min for free tier)
        return Cache::remember('football_live_matches', 60, function () {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->get("{$this->baseUrl}/matches", [
                'status' => 'LIVE',
            ]);

            if ($response->successful()) {
                return $response->json()['matches'] ?? [];
            }

            return [];
        });
    }

    /**
     * Get all scheduled matches for today
     */
    public function getScheduledMatches()
    {
        return Cache::remember('football_scheduled_matches', 300, function () { // Cache 5 mins
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'X-Auth-Token' => $this->apiKey,
            ])->get("{$this->baseUrl}/matches", [
                'dateFrom' => now()->format('Y-m-d'),
                'dateTo' => now()->format('Y-m-d'),
            ]);

            if ($response->successful()) {
                return $response->json()['matches'] ?? [];
            }

            return [];
        });
    }
}
