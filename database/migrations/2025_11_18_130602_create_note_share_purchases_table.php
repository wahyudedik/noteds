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
        Schema::create('note_share_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('share_referral_id')->constrained('note_share_referrals')->onDelete('cascade');
            $table->foreignUuid('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignUuid('buyer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('purchase_amount', 12, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->enum('commission_status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index('share_referral_id');
            $table->index('transaction_id');
            $table->index('buyer_id');
            $table->index('commission_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_share_purchases');
    }
};
