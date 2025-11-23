<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->uuid('created_by');
            $table->uuid('assigned_to')->nullable();
            $table->uuid('note_id')->nullable(); // Optional: link to note
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo'); // todo, in_progress, completed, cancelled
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('tags')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('note_id')->references('id')->on('notes')->onDelete('set null');
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'assigned_to']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_tasks');
    }
};

