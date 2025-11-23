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
        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('affiliate_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->enum('payout_method', ['wallet', 'bank_transfer', 'paypal', 'other'])->default('wallet');
            $table->string('payout_reference')->nullable();
            $table->text('payout_details')->nullable()->comment('JSON for payment details (account number, etc)');
            $table->integer('commission_count')->default(0)->comment('Number of commissions included');
            $table->text('notes')->nullable();
            $table->foreignUuid('processed_by')->nullable()->constrained('users')->onDelete('set null')->comment('Admin who processed');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('affiliate_id');
            $table->index('status');
            $table->index('payout_method');
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_payouts');
    }
};
