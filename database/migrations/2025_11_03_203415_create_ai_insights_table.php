<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DEPRECATED: This migration creates the ai_insights table.
 * AI features have been removed from the application (January 2025).
 * This migration is kept for backward compatibility with existing databases.
 * The table can be dropped in the future if needed.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_insights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('workspace_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['weekly_summary', 'topic_detection', 'context_link', 'auto_summary'])->default('weekly_summary');
            $table->string('title')->nullable();
            $table->text('content'); // JSON or text content
            $table->json('metadata')->nullable(); // Additional structured data
            $table->date('insight_date')->nullable(); // For weekly summaries, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'type', 'insight_date']);
            $table->index(['workspace_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_insights');
    }
};
