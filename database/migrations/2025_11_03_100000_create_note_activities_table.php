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
        Schema::create('note_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // 'created', 'updated', 'deleted', 'tagged', 'published', etc.
            $table->text('description')->nullable(); // Human-readable description
            $table->json('changes')->nullable(); // Store field changes (old_value -> new_value)
            $table->json('metadata')->nullable(); // Additional metadata (tags added, files uploaded, etc.)
            $table->timestamps();

            $table->index('note_id');
            $table->index('user_id');
            $table->index('action');
            $table->index(['note_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_activities');
    }
};
