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
        // Drop old table if exists (singular name)
        Schema::dropIfExists('comment_edit_history');

        // Create new table with correct name (plural)
        if (!Schema::hasTable('comment_edit_histories')) {
            Schema::create('comment_edit_histories', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('comment_id');
                $table->uuid('user_id');
                $table->text('content');
                $table->timestamp('edited_at');
                $table->timestamps();
                
                $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('comment_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_edit_histories');
        
        // Recreate old table if needed for rollback
        if (!Schema::hasTable('comment_edit_history')) {
            Schema::create('comment_edit_history', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('comment_id');
                $table->uuid('user_id');
                $table->text('content');
                $table->timestamp('edited_at');
                $table->timestamps();
                
                $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index('comment_id');
            });
        }
    }
};
