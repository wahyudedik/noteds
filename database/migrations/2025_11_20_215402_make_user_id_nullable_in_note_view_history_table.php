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
        Schema::table('note_view_history', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['user_id']);
            
            // Make user_id nullable
            $table->uuid('user_id')->nullable()->change();
            
            // Re-add foreign key with set null on delete
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('note_view_history', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['user_id']);
            
            // Make user_id not nullable again
            $table->uuid('user_id')->nullable(false)->change();
            
            // Re-add foreign key with cascade
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
