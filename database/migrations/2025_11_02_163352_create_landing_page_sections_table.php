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
        Schema::create('landing_page_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('section_type', [
                'hero',
                'features',
                'how_it_works',
                'premium_benefits',
                'trust_indicators',
                'testimonials',
                'promo',
                'cms_pages',
                'custom'
            ])->default('custom');
            $table->string('title')->nullable(); // Main title/heading
            $table->string('subtitle')->nullable(); // Subtitle/description
            $table->json('content'); // Flexible JSON content for section-specific data
            $table->string('image_url')->nullable(); // Background or featured image
            $table->string('background_color')->nullable(); // Hex color or CSS class
            $table->string('text_color')->nullable(); // Text color
            $table->string('alignment')->default('center'); // left, center, right
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable(); // For promo sections
            $table->date('valid_until')->nullable(); // For promo sections
            $table->timestamps();

            $table->index('section_type');
            $table->index('is_active');
            $table->index('order');
            $table->index(['section_type', 'is_active']);
            $table->index(['valid_from', 'valid_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_page_sections');
    }
};
