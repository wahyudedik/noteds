<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_timelines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->uuid('user_id'); // User who created the timeline event
            $table->string('event_type'); // 'task_created', 'task_completed', 'note_created', 'note_updated', 'member_joined', 'custom'
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('related_id')->nullable(); // ID of related entity (task, note, etc.)
            $table->string('related_type')->nullable(); // Type of related entity
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamp('event_date');
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['workspace_id', 'event_date']);
            $table->index(['workspace_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_timelines');
    }
};

