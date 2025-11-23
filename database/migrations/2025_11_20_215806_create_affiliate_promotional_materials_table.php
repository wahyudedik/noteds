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
        Schema::create('affiliate_promotional_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('affiliate_link_id')->constrained('affiliate_links')->onDelete('cascade');
            $table->string('name');
            $table->string('type')->default('banner'); // banner, link, text
            $table->string('size')->nullable(); // 728x90, 300x250, 468x60, etc.
            $table->text('html_code')->nullable(); // HTML code for banner/link
            $table->string('image_path')->nullable(); // Path to banner image
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('affiliate_link_id');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_promotional_materials');
    }
};
