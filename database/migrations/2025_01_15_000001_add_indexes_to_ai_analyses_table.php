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
        if (!Schema::hasTable('ai_analyses')) {
            return;
        }

        Schema::table('ai_analyses', function (Blueprint $table) {
            // Add composite index for faster lookups
            $table->index(['user_id', 'note_id', 'analysis_type'], 'ai_analyses_user_note_type_idx');
            
            // Add index for note_id (for recommendations)
            $table->index('note_id', 'ai_analyses_note_id_idx');
            
            // Add index for user_id (for user's analysis history)
            $table->index('user_id', 'ai_analyses_user_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('ai_analyses')) {
            return;
        }

        Schema::table('ai_analyses', function (Blueprint $table) {
            $table->dropIndex('ai_analyses_user_note_type_idx');
            $table->dropIndex('ai_analyses_note_id_idx');
            $table->dropIndex('ai_analyses_user_id_idx');
        });
    }
};

