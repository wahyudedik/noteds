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
        Schema::create('clip_view_tracking', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('clip_id');
            $table->integer('views_count');
            $table->timestamp('tracked_at');
            $table->decimal('stability_score', 5, 2)->nullable();
            $table->boolean('is_valid')->default(true);
            $table->timestamps();
            
            $table->foreign('clip_id')->references('id')->on('clips')->onDelete('cascade');
            $table->index(['clip_id', 'tracked_at']);
            $table->index('tracked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clip_view_tracking');
    }
};
