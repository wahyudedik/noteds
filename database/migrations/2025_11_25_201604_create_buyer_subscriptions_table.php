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
        Schema::create('buyer_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('plan_id')->constrained('subscription_plans')->onDelete('restrict');
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->decimal('price', 12, 2);
            $table->enum('status', ['active', 'cancelled', 'expired', 'past_due', 'trialing'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->string('payment_method')->nullable(); // wallet, midtrans, etc.
            $table->string('midtrans_order_id')->nullable();
            $table->string('midtrans_token')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->foreignUuid('gifted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('gifted_to')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_gift')->default(false);
            $table->timestamp('gift_sent_at')->nullable();
            $table->json('team_members')->nullable(); // For team plans
            $table->integer('billing_cycle_count')->default(0); // Number of cycles billed
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('plan_id');
            $table->index('status');
            $table->index('next_billing_date');
            $table->index(['user_id', 'status']);
            $table->index('midtrans_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_subscriptions');
    }
};
