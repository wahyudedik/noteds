<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('story_highlights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('title');
            $table->string('cover_image')->nullable();
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('story_highlight_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('story_highlight_id');
            $table->foreign('story_highlight_id')->references('id')->on('story_highlights')->onDelete('cascade');
            $table->uuid('story_id');
            $table->foreign('story_id')->references('id')->on('stories')->onDelete('cascade');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            $table->unique(['story_highlight_id', 'story_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_highlight_items');
        Schema::dropIfExists('story_highlights');
    }
};
