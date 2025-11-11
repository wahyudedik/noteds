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
        Schema::dropIfExists('note_embeddings');
        
        Schema::create('note_embeddings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->string('content_hash', 64); // Hash of content for change detection (SHA256 = 64 chars)
            $table->json('embedding'); // Vector embedding (stored as JSON array)
            $table->integer('dimension')->default(768); // Embedding dimension
            $table->string('model')->nullable(); // Model used for embedding
            $table->timestamps();

            $table->index('note_id');
            $table->index('content_hash');
            $table->index(['note_id', 'content_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_embeddings');
    }
};
