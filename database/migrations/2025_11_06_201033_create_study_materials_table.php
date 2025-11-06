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
        Schema::create('study_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->string('type')->comment('flashcards, quiz, study_guide, mind_map');
            $table->string('title');
            $table->json('content')->comment('Structured content (flashcards, questions, etc)');
            $table->integer('item_count')->default(0)->comment('Number of items (cards, questions, etc)');
            $table->timestamps();

            $table->index('user_id');
            $table->index('note_id');
            $table->index(['user_id', 'note_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_materials');
    }
};
