<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Locale settings
            $table->string('currency')->default('USD')->index(); // USD, EUR, IDR, etc
            $table->string('timezone')->default('UTC');
            $table->string('locale')->default('en'); // en, id, ar

            // Device/IP tracking
            $table->string('last_ip_address')->nullable();
            $table->string('last_user_agent')->nullable();
            $table->string('device_fingerprint')->nullable()->index();

            // Fraud flags
            $table->boolean('is_fraud_suspected')->default(false)->index();
            $table->text('fraud_notes')->nullable();
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
