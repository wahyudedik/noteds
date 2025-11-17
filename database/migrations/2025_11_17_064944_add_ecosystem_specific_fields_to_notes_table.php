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
            // Code ecosystem fields
            $table->string('code_language', 50)->nullable()->after('ecosystem_category'); // PHP, JS, Python, etc.
            $table->string('code_framework', 50)->nullable()->after('code_language'); // Laravel, React, Vue, etc.
            $table->string('code_type', 50)->nullable()->after('code_framework'); // plugin, script, library, component
            
            // Photo ecosystem fields
            $table->string('photo_resolution', 50)->nullable()->after('code_type'); // e.g., "1920x1080", "4K"
            $table->string('photo_type', 50)->nullable()->after('photo_resolution'); // stock, portrait, landscape, product, event
            $table->string('photo_format', 50)->nullable()->after('photo_type'); // jpeg, jpg, png, raw
            
            // Design ecosystem fields
            $table->string('design_type', 50)->nullable()->after('photo_format'); // logo, flyer, icon, illustration, print, branding
            $table->string('design_format', 50)->nullable()->after('design_type'); // ai, psd, eps, pdf, svg
            
            // Audio ecosystem fields
            $table->integer('audio_duration')->nullable()->after('design_format'); // Duration in seconds
            $table->string('audio_format', 50)->nullable()->after('audio_duration'); // mp3, wav, flac, aac
            $table->string('audio_genre', 50)->nullable()->after('audio_format'); // Music genre
            
            // Video ecosystem fields
            $table->integer('video_duration')->nullable()->after('audio_genre'); // Duration in seconds
            $table->string('video_resolution', 50)->nullable()->after('video_duration'); // e.g., "1920x1080", "4K"
            $table->string('video_format', 50)->nullable()->after('video_resolution'); // mp4, mov, avi, webm
            
            // Theme ecosystem fields
            $table->string('theme_platform', 50)->nullable()->after('video_format'); // wordpress, shopify, html, drupal, magento
            $table->string('theme_type', 50)->nullable()->after('theme_platform'); // business, ecommerce, blog, portfolio
            
            // 3D ecosystem fields
            $table->string('three_d_format', 50)->nullable()->after('theme_type'); // obj, fbx, blend, dae, 3ds
            $table->string('three_d_type', 50)->nullable()->after('three_d_format'); // model, texture, rig, animation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            // Code ecosystem fields
            $table->dropColumn([
                'code_language',
                'code_framework',
                'code_type',
                // Photo ecosystem fields
                'photo_resolution',
                'photo_type',
                'photo_format',
                // Design ecosystem fields
                'design_type',
                'design_format',
                // Audio ecosystem fields
                'audio_duration',
                'audio_format',
                'audio_genre',
                // Video ecosystem fields
                'video_duration',
                'video_resolution',
                'video_format',
                // Theme ecosystem fields
                'theme_platform',
                'theme_type',
                // 3D ecosystem fields
                'three_d_format',
                'three_d_type',
            ]);
        });
    }
};
