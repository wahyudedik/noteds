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
        Schema::create('bookmark_tag', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bookmark_id');
            $table->uuid('tag_id');
            $table->timestamps();

            $table->foreign('bookmark_id')->references('id')->on('bookmarks')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('bookmark_tags')->onDelete('cascade');
            $table->unique(['bookmark_id', 'tag_id']);
            $table->index('bookmark_id');
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmark_tag');
    }
};
