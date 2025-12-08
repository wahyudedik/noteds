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
        Schema::create('note_share_commissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('share_referral_id')->constrained('note_share_referrals')->onDelete('cascade');
            $table->foreignUuid('seller_id')->constrained('users')->onDelete('cascade');
            $table->uuid('transaction_id')->nullable();
            $table->decimal('commission_amount', 15, 2);
            $table->decimal('commission_percent', 5, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->string('month')->comment('Y-m format, e.g., 2025-12');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('seller_id');
            $table->index(['seller_id', 'status']);
            $table->index(['month', 'status']);
            $table->index('share_referral_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_share_commissions');
    }
};
