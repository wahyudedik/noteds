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
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transaction_id')->unique();
            $table->enum('from_wallet_type', ['creator', 'campaign', 'clipper', 'platform']);
            $table->uuid('from_wallet_id')->nullable();
            $table->enum('to_wallet_type', ['creator', 'campaign', 'clipper', 'platform']);
            $table->uuid('to_wallet_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('reason', ['reward', 'fee', 'refund', 'topup', 'withdrawal', 'campaign_lock', 'campaign_unlock']);
            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->json('metadata')->nullable();
            $table->uuid('admin_id')->nullable();
            $table->timestamps();
            
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['from_wallet_type', 'from_wallet_id']);
            $table->index(['to_wallet_type', 'to_wallet_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
