<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_subscription_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subscription_id');
            $table->uuid('transaction_id')->nullable(); // Linked transaction
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending, success, failed, refunded
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->text('failure_reason')->nullable();
            $table->integer('attempt_number')->default(1);
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('note_subscriptions')) {
                $table->foreign('subscription_id')->references('id')->on('note_subscriptions')->onDelete('cascade');
            }
            if (Schema::hasTable('transactions')) {
                $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            }
            $table->index('status');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_subscription_payments');
    }
};

