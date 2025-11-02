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
        Schema::create('documentations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content'); // Rich HTML content from Quill
            $table->text('summary')->nullable(); // Brief summary/description
            $table->enum('category', [
                'wiki',
                'screenshot_guide',
                'link_reference',
                'troubleshooting',
                'api_documentation',
                'video_tutorial'
            ])->default('wiki');
            $table->string('icon')->nullable(); // Icon/SVG for visual identification
            $table->json('links')->nullable(); // Array of related links
            $table->json('screenshots')->nullable(); // Array of screenshot paths
            $table->json('video_urls')->nullable(); // Array of video URLs (YouTube, etc)
            $table->json('tags')->nullable(); // Tags for search/filtering
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0);
            $table->integer('helpful_count')->default(0);
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
            $table->index('order');
            $table->index(['category', 'is_active']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentations');
    }
};
