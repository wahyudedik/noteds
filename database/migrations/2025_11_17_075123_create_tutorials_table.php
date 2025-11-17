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
        Schema::create('tutorials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('content');
            $table->enum('category', ['design', 'web', 'photo', 'business'])->default('web');
            $table->string('thumbnail')->nullable();
            $table->string('video_url')->nullable();
            $table->foreignUuid('author_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['published', 'draft'])->default('draft');
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['status', 'category']);
            $table->index('featured');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutorials');
    }
};
