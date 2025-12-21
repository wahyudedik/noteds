<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule MediaStack articles fetch 3x per day
Schedule::command('mediastack:fetch')->dailyAt('08:00');
Schedule::command('mediastack:fetch')->dailyAt('14:00');
Schedule::command('mediastack:fetch')->dailyAt('20:00');
