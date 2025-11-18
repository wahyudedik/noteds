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
        Schema::table('notes', function (Blueprint $table) {
            $table->string('video_preview')->nullable()->after('thumbnails');
            $table->string('video_preview_thumbnail')->nullable()->after('video_preview');
            $table->integer('video_preview_duration')->nullable()->after('video_preview_thumbnail'); // Duration in seconds
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['video_preview', 'video_preview_thumbnail', 'video_preview_duration']);
        });
    }
};
