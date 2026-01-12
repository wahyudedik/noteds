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
        Schema::create('campaign_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id');
            $table->string('variant_name');
            $table->decimal('cpm', 10, 2);
            $table->integer('allocation_percent')->default(0); // 0-100
            $table->enum('status', ['active', 'paused', 'completed'])->default('active');
            $table->integer('total_views')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->decimal('performance_score', 8, 4)->nullable();
            $table->boolean('is_winner')->default(false);
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->index(['campaign_id', 'status']);
            $table->index('is_winner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_variants');
    }
};
