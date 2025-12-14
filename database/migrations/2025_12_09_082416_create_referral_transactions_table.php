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
        Schema::create('referral_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('referral_id')->nullable();
            $table->uuid('user_id');
            $table->uuid('admin_id')->nullable();
            $table->decimal('amount', 15, 2);
            // Allow both legacy and more specific reward types used across services and seeders
            $table->enum('type', [
                'signup',
                'transaction',
                'signup_bonus',
                'transaction_commission',
            ])->default('signup_bonus');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('referral_id')->references('id')->on('referrals')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('user_id');
            $table->index('admin_id');
            $table->index('status');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_transactions');
    }
};
