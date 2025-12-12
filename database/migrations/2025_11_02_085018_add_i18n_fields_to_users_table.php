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
            // Only add columns if they don't already exist (from previous migration)
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency', 3)->default('IDR')->after('wallet_balance');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 50)->default('Asia/Jakarta')->after('currency');
            }
            if (!Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 5)->default('id')->after('timezone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['currency', 'timezone', 'locale']);
        });
    }
};
