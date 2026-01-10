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
        Schema::create('product_review_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_review_id');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->unsignedInteger('file_size');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            
            $table->foreign('product_review_id')->references('id')->on('product_reviews')->onDelete('cascade');
            $table->index('product_review_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_review_media');
    }
};
