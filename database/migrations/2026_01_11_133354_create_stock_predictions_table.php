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
        Schema::create('stock_predictions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stock_id');
            $table->uuid('ml_model_id');
            $table->date('prediction_date');
            $table->date('target_date');
            $table->decimal('predicted_price', 15, 2);
            $table->decimal('confidence_score', 5, 4);
            $table->decimal('lower_bound', 15, 2)->nullable();
            $table->decimal('upper_bound', 15, 2)->nullable();
            $table->integer('prediction_horizon');
            $table->decimal('actual_price', 15, 2)->nullable();
            $table->decimal('prediction_error', 15, 4)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreign('ml_model_id')->references('id')->on('ml_models')->onDelete('cascade');
            $table->unique(['stock_id', 'ml_model_id', 'prediction_date', 'target_date'], 'stock_pred_unique');
            $table->index('stock_id');
            $table->index('target_date');
            $table->index('prediction_horizon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_predictions');
    }
};
