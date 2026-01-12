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
        Schema::create('portfolio_recommendations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('risk_profile', ['conservative', 'moderate', 'aggressive']);
            $table->decimal('investment_amount', 15, 2);
            $table->integer('investment_horizon');
            $table->json('allocation');
            $table->decimal('expected_return', 5, 4)->nullable();
            $table->decimal('expected_risk', 5, 4)->nullable();
            $table->decimal('sharpe_ratio', 5, 4)->nullable();
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_recommendations');
    }
};
