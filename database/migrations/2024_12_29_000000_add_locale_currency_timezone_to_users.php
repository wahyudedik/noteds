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
            // Add locale, currency, and timezone columns if they don't exist
            if (!Schema::hasColumn('users', 'locale')) {
                $table->string('locale')->default('en')->after('role');
            }
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency')->default('IDR')->after('locale');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('UTC')->after('currency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['locale', 'currency', 'timezone']);
        });
    }
};
