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
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('affiliate_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('conversion_id')->constrained('affiliate_conversions')->onDelete('cascade');
            $table->foreignUuid('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->integer('tier')->default(1)->comment('1 = direct, 2 = level 2, 3 = level 3, etc');
            $table->foreignUuid('parent_affiliate_id')->nullable()->constrained('users')->onDelete('set null')->comment('Parent affiliate for multi-tier');
            $table->decimal('commission_rate', 5, 2)->default(0)->comment('Commission percentage');
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('transaction_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->uuid('payout_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('affiliate_id');
            $table->index('conversion_id');
            $table->index('transaction_id');
            $table->index('tier');
            $table->index('status');
            $table->index('payout_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
