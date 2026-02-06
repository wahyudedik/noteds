<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notification_logs')) {
            return;
        }

        if (!Schema::hasColumn('notification_logs', 'live_stream_id')) {
            return;
        }

        $driver = DB::getDriverName();
        try {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            }
        } catch (\Throwable $e) {
        }

        Schema::table('notification_logs', function (Blueprint $table) {
            try {
                $table->dropForeign(['live_stream_id']);
            } catch (\Throwable $e) {
            }

            try {
                $table->dropColumn('live_stream_id');
            } catch (\Throwable $e) {
            }
        });

        try {
            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            }
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('notification_logs')) {
            return;
        }

        if (Schema::hasColumn('notification_logs', 'live_stream_id')) {
            return;
        }

        Schema::table('notification_logs', function (Blueprint $table) {
            if (Schema::hasTable('live_streams')) {
                $table->foreignId('live_stream_id')->nullable()->constrained('live_streams')->nullOnDelete();
                return;
            }

            $table->unsignedBigInteger('live_stream_id')->nullable();
        });
    }
};
