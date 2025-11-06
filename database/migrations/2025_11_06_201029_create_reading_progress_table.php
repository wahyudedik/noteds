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
        Schema::create('reading_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->integer('progress_percentage')->default(0)->comment('0-100');
            $table->integer('last_position')->default(0)->comment('Last scroll position or character position');
            $table->integer('total_characters')->default(0)->comment('Total characters in note');
            $table->integer('read_characters')->default(0)->comment('Characters read so far');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Unique constraint: one user can only have one progress per note
            $table->unique(['user_id', 'note_id']);
            
            $table->index('user_id');
            $table->index('note_id');
            $table->index('last_read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_progress');
    }
};
