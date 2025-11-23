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
        Schema::create('affiliate_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('affiliate_link_id')->constrained('affiliate_links')->onDelete('cascade');
            $table->foreignUuid('affiliate_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('converter_id')->constrained('users')->onDelete('cascade'); // User who made the purchase
            $table->foreignUuid('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->foreignUuid('purchase_id')->nullable()->constrained('purchased_notes')->onDelete('set null');
            $table->enum('conversion_type', ['signup', 'purchase', 'subscription'])->default('purchase');
            $table->decimal('transaction_amount', 12, 2)->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('converted_at');
            $table->timestamps();

            $table->index('affiliate_link_id');
            $table->index('affiliate_id');
            $table->index('converter_id');
            $table->index('transaction_id');
            $table->index('conversion_type');
            $table->index('converted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_conversions');
    }
};
