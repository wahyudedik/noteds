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
            $table->string('video_link', 500)->nullable()->after('video_format');
            $table->string('audio_link', 500)->nullable()->after('audio_genre');
            $table->string('design_preview_link', 500)->nullable()->after('design_format');
            $table->string('photo_gallery_link', 500)->nullable()->after('photo_format');
            $table->string('code_demo_link', 500)->nullable()->after('code_type');
            $table->string('theme_preview_link', 500)->nullable()->after('theme_type');
            $table->string('three_d_preview_link', 500)->nullable()->after('three_d_type');
            $table->string('demo_link', 500)->nullable()->after('three_d_preview_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn([
                'video_link',
                'audio_link',
                'design_preview_link',
                'photo_gallery_link',
                'code_demo_link',
                'theme_preview_link',
                'three_d_preview_link',
                'demo_link',
            ]);
        });
    }
};
