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
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->uuid('collection_id')->nullable()->after('post_id');
            $table->foreign('collection_id')->references('id')->on('bookmark_collections')->onDelete('set null');
            $table->index('collection_id');
        });

        // Drop existing unique constraint and create new one
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'post_id']);
        });

        // Add new unique constraint that includes collection_id
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->unique(['user_id', 'post_id', 'collection_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'post_id', 'collection_id']);
            $table->dropForeign(['collection_id']);
            $table->dropIndex(['collection_id']);
            $table->dropColumn('collection_id');
            $table->unique(['user_id', 'post_id']);
        });
    }
};
