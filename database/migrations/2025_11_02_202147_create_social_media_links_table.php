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
        Schema::create('social_media_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('platform'); // facebook, twitter, linkedin, instagram, youtube, tiktok, etc.
            $table->string('name'); // Display name
            $table->string('url');
            $table->string('icon')->nullable(); // SVG path or icon class
            $table->string('color')->nullable(); // Hex color for icon
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('platform');
            $table->index('is_active');
            $table->index('order');
            $table->unique('platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_links');
    }
};
