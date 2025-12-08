<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_fraud_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('affiliate_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignUuid('converter_id')->nullable()->constrained('users')->onDelete('cascade');

            // IP & Device tracking
            $table->string('ip_address')->index();
            $table->string('user_agent')->nullable();
            $table->string('device_fingerprint')->nullable()->index();

            // Activity type
            $table->enum('activity_type', [
                'click',
                'conversion',
                'payout_request',
            ])->index();

            // Fraud indicators
            $table->json('fraud_indicators')->nullable(); // Array of detected fraud patterns
            $table->integer('risk_score')->default(0); // 0-100
            $table->boolean('is_flagged')->default(false)->index();

            // Metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['affiliate_id', 'activity_type']);
            $table->index(['converter_id', 'activity_type']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['is_flagged', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_fraud_logs');
    }
};
