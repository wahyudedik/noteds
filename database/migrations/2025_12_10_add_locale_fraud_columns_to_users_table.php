<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add columns that don't exist
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency')->default('USD')->index();
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('UTC');
            }
            if (!Schema::hasColumn('users', 'locale')) {
                $table->string('locale')->default('en');
            }
            if (!Schema::hasColumn('users', 'last_ip_address')) {
                $table->string('last_ip_address')->nullable();
            }
            if (!Schema::hasColumn('users', 'last_user_agent')) {
                $table->string('last_user_agent')->nullable();
            }
            if (!Schema::hasColumn('users', 'device_fingerprint')) {
                $table->string('device_fingerprint')->nullable()->index();
            }
            if (!Schema::hasColumn('users', 'is_fraud_suspected')) {
                $table->boolean('is_fraud_suspected')->default(false)->index();
            }
            if (!Schema::hasColumn('users', 'fraud_notes')) {
                $table->text('fraud_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'currency',
                'timezone',
                'locale',
                'last_ip_address',
                'last_user_agent',
                'device_fingerprint',
                'is_fraud_suspected',
                'fraud_notes',
            ]);
        });
    }
};
