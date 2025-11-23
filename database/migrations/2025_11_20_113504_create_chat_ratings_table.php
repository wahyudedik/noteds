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
        Schema::create('chat_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->uuid('rater_id')->comment('User who gives the rating');
            $table->uuid('rated_user_id')->comment('User being rated');
            $table->integer('rating')->comment('Rating 1-5 stars');
            $table->text('comment')->nullable()->comment('Optional comment about the conversation');
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('note_conversations')->cascadeOnDelete();
            $table->foreign('rater_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('rated_user_id')->references('id')->on('users')->cascadeOnDelete();
            
            // One rating per user per conversation
            $table->unique(['conversation_id', 'rater_id']);
            $table->index(['rated_user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_ratings');
    }
};
