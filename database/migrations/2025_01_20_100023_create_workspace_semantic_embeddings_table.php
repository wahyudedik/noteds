<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_semantic_embeddings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->uuid('note_id')->nullable(); // Optional: link to specific note
            $table->string('content_type'); // 'note', 'folder', 'workspace', 'activity'
            $table->text('content'); // Content to embed
            $table->json('embedding')->nullable(); // Vector embedding (can be stored as JSON array)
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('workspaces')) {
                $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            }
            if (Schema::hasTable('notes')) {
                $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            }
            $table->index(['workspace_id', 'content_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_semantic_embeddings');
    }
};

