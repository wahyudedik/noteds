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
        Schema::create('product_review_replies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_review_id')->unique();
            $table->uuid('seller_id');
            $table->text('content');
            $table->timestamps();
            
            $table->foreign('product_review_id')->references('id')->on('product_reviews')->onDelete('cascade');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('seller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_review_replies');
    }
};
