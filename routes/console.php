<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule subscription renewal check (run daily at 00:00)
Schedule::command('subscriptions:renew')
    ->daily()
    ->at('00:00')
    ->timezone('Asia/Jakarta')
    ->description('Auto-renew subscriptions or expire if insufficient wallet balance');

// Schedule featured notes expiry check (run daily at 01:00)
Schedule::command('featured:expire')
    ->daily()
    ->at('01:00')
    ->timezone('Asia/Jakarta')
    ->description('Expire featured notes that have passed their end date');
