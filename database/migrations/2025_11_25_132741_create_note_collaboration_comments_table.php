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
        Schema::create('note_collaboration_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('parent_id')->nullable()->constrained('note_collaboration_comments')->onDelete('cascade');
            $table->text('content');
            $table->string('target_type')->nullable(); // 'line', 'section', 'general'
            $table->string('target_reference')->nullable(); // Line number, section ID, etc.
            $table->json('target_position')->nullable(); // Store position data (line, column, etc.)
            $table->enum('status', ['open', 'resolved', 'archived'])->default('open');
            $table->foreignUuid('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamps();

            $table->index(['note_id', 'status']);
            $table->index(['note_id', 'parent_id']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_collaboration_comments');
    }
};
