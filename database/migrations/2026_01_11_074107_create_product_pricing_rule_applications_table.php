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
        Schema::create('product_pricing_rule_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('rule_id');
            $table->uuid('product_id');
            $table->uuid('order_id')->nullable();
            $table->decimal('original_price', 15, 2);
            $table->decimal('adjusted_price', 15, 2);
            $table->decimal('adjustment_amount', 15, 2);
            $table->timestamp('applied_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('rule_id')->references('id')->on('product_pricing_rules')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->index('rule_id');
            $table->index('product_id');
            $table->index('order_id');
            $table->index('applied_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_pricing_rule_applications', function (Blueprint $table) {
            $table->dropForeign(['rule_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['order_id']);
        });
        Schema::dropIfExists('product_pricing_rule_applications');
    }
};
