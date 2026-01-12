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
        Schema::create('product_pricing_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->enum('rule_type', ['time_based', 'stock_based', 'demand_based']);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            
            // Time-based fields
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('days_of_week')->nullable();
            
            // Stock-based fields
            $table->integer('stock_threshold')->nullable();
            $table->enum('stock_condition', ['below', 'above', 'equals'])->nullable();
            
            // Demand-based fields
            $table->integer('sales_period_days')->nullable();
            $table->integer('sales_threshold')->nullable();
            $table->enum('demand_condition', ['high', 'low'])->nullable();
            
            // Pricing adjustment
            $table->enum('adjustment_type', ['fixed', 'percentage']);
            $table->decimal('adjustment_value', 15, 2);
            $table->decimal('base_price_override', 15, 2)->nullable();
            $table->integer('max_applications')->nullable();
            $table->integer('application_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index('product_id');
            $table->index('is_active');
            $table->index('rule_type');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_pricing_rules', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::dropIfExists('product_pricing_rules');
    }
};
