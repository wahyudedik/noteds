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
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('url');
            $table->string('url_hash', 64)->unique();
            $table->string('source');
            $table->text('image')->nullable();
            $table->string('category')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('language', 10)->default('id');
            $table->string('country', 10)->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();

            // Indexes for performance
            $table->index('category');
            $table->index('published_at');
            $table->index('fetched_at');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
