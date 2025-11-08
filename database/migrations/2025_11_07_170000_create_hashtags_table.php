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
        Schema::create('hashtags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->integer('posts_count')->default(0);
            $table->timestamps();
            
            $table->index('slug');
            $table->index('posts_count');
        });

        Schema::create('post_hashtags', function (Blueprint $table) {
            $table->foreignUuid('post_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('hashtag_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['post_id', 'hashtag_id']);
            $table->index('post_id');
            $table->index('hashtag_id');
        });

        Schema::create('post_mentions', function (Blueprint $table) {
            $table->foreignUuid('post_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['post_id', 'user_id']);
            $table->index('post_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_mentions');
        Schema::dropIfExists('post_hashtags');
        Schema::dropIfExists('hashtags');
    }
};

