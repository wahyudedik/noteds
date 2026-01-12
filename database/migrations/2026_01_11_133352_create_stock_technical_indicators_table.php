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
        Schema::create('stock_technical_indicators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stock_id');
            $table->date('date');
            $table->decimal('sma_5', 15, 2)->nullable();
            $table->decimal('sma_10', 15, 2)->nullable();
            $table->decimal('sma_20', 15, 2)->nullable();
            $table->decimal('sma_50', 15, 2)->nullable();
            $table->decimal('sma_200', 15, 2)->nullable();
            $table->decimal('ema_12', 15, 2)->nullable();
            $table->decimal('ema_26', 15, 2)->nullable();
            $table->decimal('rsi', 5, 2)->nullable();
            $table->decimal('macd', 15, 2)->nullable();
            $table->decimal('macd_signal', 15, 2)->nullable();
            $table->decimal('macd_histogram', 15, 2)->nullable();
            $table->decimal('bollinger_upper', 15, 2)->nullable();
            $table->decimal('bollinger_middle', 15, 2)->nullable();
            $table->decimal('bollinger_lower', 15, 2)->nullable();
            $table->decimal('stochastic_k', 5, 2)->nullable();
            $table->decimal('stochastic_d', 5, 2)->nullable();
            $table->decimal('adx', 5, 2)->nullable();
            $table->decimal('atr', 15, 2)->nullable();
            $table->decimal('volatility', 5, 4)->nullable();
            $table->timestamps();

            $table->foreign('stock_id')->references('id')->on('stocks')->onDelete('cascade');
            $table->unique(['stock_id', 'date']);
            $table->index('stock_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_technical_indicators');
    }
};
