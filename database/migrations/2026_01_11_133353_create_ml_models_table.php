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
        Schema::create('ml_models', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('model_type', ['lstm', 'transformer', 'cnn_lstm', 'ensemble']);
            $table->uuid('stock_id')->nullable();
            $table->string('model_version');
            $table->enum('status', ['training', 'active', 'archived', 'failed']);
            $table->timestamp('training_started_at')->nullable();
            $table->timestamp('training_completed_at')->nullable();
            $table->json('metrics')->nullable();
            $table->json('hyperparameters')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('prediction_horizon');
            $table->boolean('is_best_model')->default(false);
            $table->timestamps();

            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->index('model_type');
            $table->index('stock_id');
            $table->index('status');
            $table->index('prediction_horizon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_models');
    }
};
