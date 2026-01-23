<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TrendingSeeder extends Seeder
{
    public function run(): void
    {
        app(\App\Services\TrendingService::class)->calculateTrendingScores();
    }
}
