<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->uuid('user_id');
            $table->uuid('task_id')->nullable(); // Optional: link to task
            $table->uuid('note_id')->nullable(); // Optional: link to note
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('remind_at');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('workspaces')) {
                $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            }
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            if (Schema::hasTable('workspace_tasks')) {
                $table->foreign('task_id')->references('id')->on('workspace_tasks')->onDelete('cascade');
            }
            if (Schema::hasTable('notes')) {
                $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            }
            $table->index(['workspace_id', 'user_id', 'remind_at']);
            $table->index('is_completed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_reminders');
    }
};

