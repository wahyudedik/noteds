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
        Schema::create('seller_performance_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('seller_id')->unique();
            $table->integer('total_orders')->default(0);
            $table->integer('completed_orders')->default(0);
            $table->integer('cancelled_orders')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('average_order_value', 15, 2)->default(0);
            $table->decimal('fulfillment_rate', 5, 2)->default(0);
            $table->decimal('average_response_time_hours', 8, 2)->nullable();
            $table->decimal('total_rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('seller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_performance_metrics', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
        });
        Schema::dropIfExists('seller_performance_metrics');
    }
};
