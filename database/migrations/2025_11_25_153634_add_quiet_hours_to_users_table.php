<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'quiet_hours_start')) {
                $table->time('quiet_hours_start')->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'quiet_hours_end')) {
                $table->time('quiet_hours_end')->nullable()->after('quiet_hours_start');
            }
            if (!Schema::hasColumn('users', 'quiet_hours_enabled')) {
                $table->boolean('quiet_hours_enabled')->default(false)->after('quiet_hours_end');
            }
            // Note: timezone column might already exist from i18n migration
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->nullable()->after('quiet_hours_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['quiet_hours_start', 'quiet_hours_end', 'timezone', 'quiet_hours_enabled']);
        });
    }
};
