<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        try {
            if ($driver === 'mysql') DB::statement('SET FOREIGN_KEY_CHECKS=0');
            if ($driver === 'sqlite') DB::statement('PRAGMA foreign_keys = OFF');
        } catch (\Throwable $e) {}

        $tables = [
            'event_categories',
            'event_invitations',
            'event_reminders',
            'events',
        ];
        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                try {
                    DB::statement("CREATE TABLE IF NOT EXISTS {$t}_backup AS SELECT * FROM {$t}");
                } catch (\Throwable $e) {}
                Schema::dropIfExists($t);
            }
        }

        try {
            if ($driver === 'mysql') DB::statement('SET FOREIGN_KEY_CHECKS=1');
            if ($driver === 'sqlite') DB::statement('PRAGMA foreign_keys = ON');
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        $tables = [
            'events',
            'event_invitations',
            'event_reminders',
            'event_categories',
        ];
        foreach ($tables as $t) {
            if (!Schema::hasTable($t) && Schema::hasTable("{$t}_backup")) {
                try {
                    DB::statement("CREATE TABLE {$t} AS SELECT * FROM {$t}_backup");
                } catch (\Throwable $e) {}
            }
        }
    }
};
