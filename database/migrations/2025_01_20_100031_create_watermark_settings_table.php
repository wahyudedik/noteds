<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('watermark_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('note_id');
            $table->boolean('enabled')->default(false);
            $table->string('type')->default('text'); // text, image, invisible
            $table->text('text')->nullable(); // Watermark text
            $table->string('text_color')->default('#000000');
            $table->integer('text_size')->default(24);
            $table->string('text_font')->nullable();
            $table->string('position')->default('center'); // top-left, top-right, center, bottom-left, bottom-right, tile
            $table->integer('opacity')->default(50); // 0-100
            $table->string('image_path')->nullable(); // Path to watermark image/logo
            $table->integer('image_size')->nullable(); // Size percentage
            $table->integer('margin')->default(10); // Margin in pixels
            $table->boolean('apply_to_images')->default(true);
            $table->boolean('apply_to_pdfs')->default(true);
            $table->json('metadata')->nullable(); // Additional settings
            $table->timestamps();

            // Add foreign key only if table exists
            if (Schema::hasTable('notes')) {
                $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            }
            $table->index('note_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watermark_settings');
    }
};

