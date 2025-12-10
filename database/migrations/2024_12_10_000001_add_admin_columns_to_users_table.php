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
        // Add admin columns to users table if they don't exist
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false)->after('is_verified');
            }
            if (!Schema::hasColumn('users', 'ban_reason')) {
                $table->text('ban_reason')->nullable()->after('is_banned');
            }
            if (!Schema::hasColumn('users', 'banned_until')) {
                $table->timestamp('banned_until')->nullable()->after('ban_reason');
            }
            if (!Schema::hasColumn('users', 'kyc_verified')) {
                $table->boolean('kyc_verified')->default(false)->after('is_verified');
            }
            if (!Schema::hasColumn('users', 'kyc_notes')) {
                $table->text('kyc_notes')->nullable()->after('kyc_verified');
            }
            if (!Schema::hasColumn('users', 'kyc_verified_at')) {
                $table->timestamp('kyc_verified_at')->nullable()->after('kyc_notes');
            }
            if (!Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumnIfExists('is_banned');
            $table->dropColumnIfExists('ban_reason');
            $table->dropColumnIfExists('banned_until');
            $table->dropColumnIfExists('kyc_verified');
            $table->dropColumnIfExists('kyc_notes');
            $table->dropColumnIfExists('kyc_verified_at');
            $table->dropColumnIfExists('last_activity_at');
        });
    }
};
