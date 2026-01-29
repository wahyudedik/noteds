<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected int $retentionDays;

    public function __construct()
    {
        $this->retentionDays = (int) (env('BACKUP_RETENTION_DAYS', config('backup.retention_days', 60)));
        if ($this->retentionDays <= 0) {
            $this->retentionDays = 60;
        }
    }

    public function up(): void
    {
        $driver = DB::getDriverName();
        $now = now();
        $threshold = $now->copy()->subDays($this->retentionDays);

        $backupTables = $this->listBackupTables();
        foreach ($backupTables as $table) {
            $createdAt = $this->getTableCreateTime($driver, $table);
            if (!$createdAt) {
                $createdAt = $this->guessCreatedAtFromData($table) ?? $now;
            }
            if ($createdAt <= $threshold) {
                try {
                    Schema::dropIfExists($table);
                    Log::info('Backup table dropped', [
                        'table' => $table,
                        'created_at' => $createdAt?->toIso8601String(),
                        'retention_days' => $this->retentionDays,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to drop backup table', ['table' => $table, 'error' => $e->getMessage()]);
                }
            }
        }
    }

    public function down(): void
    {
        // Rollback safety: recreate empty backup tables if they were dropped
        // using the schema of the original tables when available.
        $originals = [
            'live_streams',
            'stream_logs',
            'stream_analytics',
            'live_chat_messages',
            'streaming_providers',
            'events',
            'event_invitations',
            'event_reminders',
            'event_categories',
        ];
        foreach ($originals as $orig) {
            $backup = $orig . '_backup';
            if (!Schema::hasTable($backup)) {
                try {
                    if (Schema::hasTable($orig)) {
                        // Create backup table with same columns as original
                        $columns = $this->getColumnsFor($orig);
                        DB::statement("CREATE TABLE {$backup} AS SELECT * FROM {$orig} WHERE 1=0");
                    } else {
                        // Create minimal table
                        Schema::create($backup, function (\Illuminate\Database\Schema\Blueprint $table) {
                            $table->uuid('id')->primary();
                            $table->timestamps();
                        });
                    }
                    Log::info('Backup table recreated (empty)', ['table' => $backup]);
                } catch (\Throwable $e) {
                    Log::error('Failed to recreate backup table', ['table' => $backup, 'error' => $e->getMessage()]);
                }
            }
        }
    }

    protected function listBackupTables(): array
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            $rows = DB::select("SELECT TABLE_NAME as name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '%\_backup'");
            return array_map(fn ($r) => $r->name, $rows);
        }
        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name LIKE '%_backup'");
            return array_map(fn ($r) => $r->name, $rows);
        }
        return [];
    }

    protected function getTableCreateTime(string $driver, string $table): ?\Illuminate\Support\Carbon
    {
        try {
            if ($driver === 'mysql') {
                $row = DB::selectOne("SELECT CREATE_TIME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table]);
                if ($row && isset($row->CREATE_TIME)) {
                    return \Illuminate\Support\Carbon::parse($row->CREATE_TIME);
                }
            } elseif ($driver === 'sqlite') {
                // SQLite does not store CREATE_TIME; fallback handled by caller
                return null;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to read table create time', ['table' => $table, 'error' => $e->getMessage()]);
        }
        return null;
    }

    protected function guessCreatedAtFromData(string $table): ?\Illuminate\Support\Carbon
    {
        try {
            // If table has created_at column, use MIN(created_at)
            $hasCreated = false;
            if (DB::getDriverName() === 'mysql') {
                $col = DB::selectOne("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'created_at'", [$table]);
                $hasCreated = (bool) $col;
            } elseif (DB::getDriverName() === 'sqlite') {
                $cols = DB::select("PRAGMA table_info({$table})");
                $hasCreated = collect($cols)->contains(fn ($c) => ($c->name ?? '') === 'created_at');
            }
            if ($hasCreated) {
                $min = DB::table($table)->min('created_at');
                return $min ? \Illuminate\Support\Carbon::parse($min) : null;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to guess created_at from data', ['table' => $table, 'error' => $e->getMessage()]);
        }
        return null;
    }

    protected function getColumnsFor(string $table): array
    {
        try {
            if (DB::getDriverName() === 'mysql') {
                $cols = DB::select("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table]);
                return array_map(fn ($c) => $c->COLUMN_NAME, $cols);
            } elseif (DB::getDriverName() === 'sqlite') {
                $cols = DB::select("PRAGMA table_info({$table})");
                return array_map(fn ($c) => $c->name, $cols);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to read columns', ['table' => $table, 'error' => $e->getMessage()]);
        }
        return [];
    }
};
