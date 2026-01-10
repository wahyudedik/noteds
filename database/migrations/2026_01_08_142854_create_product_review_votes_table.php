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
        Schema::create('product_review_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('product_review_id');
            $table->enum('vote_type', ['helpful', 'not_helpful'])->default('helpful');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_review_id')->references('id')->on('product_reviews')->onDelete('cascade');
            
            // Unique constraint: one vote per user per review
            $table->unique(['user_id', 'product_review_id']);
            $table->index('product_review_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_review_votes');
    }
};
