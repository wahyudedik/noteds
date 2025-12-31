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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('creator_id');
            $table->string('title');
            $table->text('description');
            $table->decimal('cpm', 10, 2);
            $table->decimal('max_budget', 15, 2);
            $table->decimal('max_reward_per_clipper', 15, 2)->nullable();
            $table->integer('duration_days');
            $table->enum('status', ['draft', 'active', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('total_views')->default(0);
            $table->integer('total_clips')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['creator_id', 'status']);
            $table->index('status');
            $table->index('started_at');
            $table->index('ended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
