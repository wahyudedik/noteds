<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('note_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('note_id');
            $table->uuid('user_id');
            $table->string('tier')->default('basic'); // basic, premium
            $table->decimal('monthly_price', 12, 2);
            $table->string('status')->default('active'); // active, cancelled, expired, suspended
            $table->timestamp('started_at');
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');
            $table->timestamp('next_billing_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->text('cancellation_reason')->nullable();
            $table->integer('billing_cycle_count')->default(0); // Number of successful renewals
            $table->timestamps();

            $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['note_id', 'user_id']); // One subscription per user per note
            $table->index('status');
            $table->index('next_billing_date');
            $table->index('current_period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('note_subscriptions');
    }
};

