<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class BackupCleanup extends Command
{
    protected $signature = 'backup:cleanup {--retention= : Override BACKUP_RETENTION_DAYS}';
    protected $description = 'Clean up *_backup tables older than retention window';

    public function handle(): int
    {
        $retention = (int)($this->option('retention') ?? env('BACKUP_RETENTION_DAYS', config('backup.retention_days', 60)));
        if ($retention <= 0) $retention = 60;
        $driver = DB::getDriverName();
        $threshold = now()->subDays($retention);
        $tables = $this->listBackupTables($driver);
        $dropped = 0;
        foreach ($tables as $t) {
            $createdAt = $this->getTableCreateTime($driver, $t) ?? $this->guessCreatedAtFromData($t) ?? now();
            if ($createdAt <= $threshold) {
                try {
                    Schema::dropIfExists($t);
                    $dropped++;
                    Log::info('Backup table dropped', ['table' => $t, 'created_at' => $createdAt->toIso8601String(), 'retention_days' => $retention]);
                    $this->info("Dropped {$t}");
                } catch (\Throwable $e) {
                    Log::error('Failed to drop backup table', ['table' => $t, 'error' => $e->getMessage()]);
                    $this->error("Failed {$t}: {$e->getMessage()}");
                }
            }
        }
        $this->line("Dropped_count={$dropped}");
        return 0;
    }

    protected function listBackupTables(string $driver): array
    {
        if ($driver === 'mysql') {
            $rows = DB::select("SELECT TABLE_NAME as name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '%\\_backup'");
            return array_map(fn ($r) => $r->name, $rows);
        }
        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE '%_backup'");
            return array_map(fn ($r) => $r->name, $rows);
        }
        return [];
    }

    protected function getTableCreateTime(string $driver, string $table): ?Carbon
    {
        try {
            if ($driver === 'mysql') {
                $row = DB::selectOne("SELECT CREATE_TIME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table]);
                if ($row && isset($row->CREATE_TIME)) {
                    return Carbon::parse($row->CREATE_TIME);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to read table create time', ['table' => $table, 'error' => $e->getMessage()]);
        }
        return null;
    }

    protected function guessCreatedAtFromData(string $table): ?Carbon
    {
        try {
            $driver = DB::getDriverName();
            $hasCreated = false;
            if ($driver === 'mysql') {
                $col = DB::selectOne("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'created_at'", [$table]);
                $hasCreated = (bool) $col;
            } elseif ($driver === 'sqlite') {
                $cols = DB::select("PRAGMA table_info({$table})");
                $hasCreated = collect($cols)->contains(fn ($c) => ($c->name ?? '') === 'created_at');
            }
            if ($hasCreated) {
                $min = DB::table($table)->min('created_at');
                return $min ? Carbon::parse($min) : null;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to guess created_at from data', ['table' => $table, 'error' => $e->getMessage()]);
        }
        return null;
    }
}
