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
        Schema::create('stock_signals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stock_id');
            $table->enum('signal_type', ['buy', 'sell', 'hold']);
            $table->decimal('signal_strength', 3, 2);
            $table->date('signal_date');
            $table->enum('source', ['ml_prediction', 'technical_analysis', 'ensemble']);
            $table->uuid('ml_model_id')->nullable();
            $table->json('technical_indicators')->nullable();
            $table->text('reason')->nullable();
            $table->decimal('price_target', 15, 2)->nullable();
            $table->decimal('stop_loss', 15, 2)->nullable();
            $table->decimal('take_profit', 15, 2)->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'very_high']);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->foreign('ml_model_id')->references('id')->on('ml_models')->onDelete('set null');
            $table->index('stock_id');
            $table->index('signal_type');
            $table->index('signal_date');
            $table->index('source');
            $table->index('risk_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_signals');
    }
};
