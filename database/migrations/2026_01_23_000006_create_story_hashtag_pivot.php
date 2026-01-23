<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('story_hashtag', function (Blueprint $table) {
            $table->uuid('story_id');
            $table->unsignedBigInteger('hashtag_id');
            $table->timestamps();
            $table->primary(['story_id', 'hashtag_id']);
            $table->foreign('story_id')->references('id')->on('stories')->onDelete('cascade');
            $table->foreign('hashtag_id')->references('id')->on('hashtags')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_hashtag');
    }
};
