<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop table if it exists (from failed migration)
        Schema::dropIfExists('workspace_knowledge_graph');
        
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

            // Add foreign key only if table exists
            if (Schema::hasTable('workspaces')) {
                $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            }
            $table->index(['workspace_id', 'entity_type', 'entity_id'], 'ws_kg_entity_idx');
            $table->index('entity_name', 'ws_kg_name_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_knowledge_graph');
    }
};

