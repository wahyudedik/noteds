<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Carbon;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);

// Bootstrap the application
$kernel->bootstrap();

/** @var \Illuminate\Database\DatabaseManager $db */
$db = $app->make('db');

$now = Carbon::now();

$updated = $db->table('users')->update([
    'is_active' => true,
    'suspended_at' => null,
    'email_verified_at' => $now,
]);

$total = $db->table('users')->count();
$active = $db->table('users')->where('is_active', true)->count();
$inactive = $db->table('users')->where(function ($q) {
    $q->where('is_active', false)->orWhereNull('is_active');
})->count();
$suspended = $db->table('users')->whereNotNull('suspended_at')->count();

$sample = $db->table('users')
    ->select('email', 'is_active', 'suspended_at')
    ->orderBy('updated_at', 'desc')
    ->limit(5)
    ->get();

echo "Updated users: {$updated}\n";
echo "Total users: {$total}\n";
echo "Active: {$active}, Inactive: {$inactive}, Suspended: {$suspended}\n";
echo "Sample (email, is_active, suspended_at):\n";
foreach ($sample as $row) {
    $susp = $row->suspended_at ?? 'null';
    $isActive = $row->is_active ? 'true' : 'false';
    echo " - {$row->email} | {$isActive} | {$susp}\n";
}
