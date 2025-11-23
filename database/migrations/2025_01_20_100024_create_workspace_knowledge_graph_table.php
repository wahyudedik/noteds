<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_knowledge_graph', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->string('entity_type'); // 'note', 'folder', 'user', 'concept', 'topic'
            $table->uuid('entity_id'); // ID of the entity
            $table->string('entity_name'); // Name/label of the entity
            $table->json('relationships')->nullable(); // Relationships to other entities
            $table->json('properties')->nullable(); // Entity properties
            $table->json('tags')->nullable(); // Tags/keywords
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->index(['workspace_id', 'entity_type', 'entity_id']);
            $table->index('entity_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_knowledge_graph');
    }
};

