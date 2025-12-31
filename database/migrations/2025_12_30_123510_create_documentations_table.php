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
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content'); // HTML atau Markdown
            $table->string('category')->nullable(); // e.g., 'getting-started', 'marketplace', 'clipper', 'api'
            $table->text('excerpt')->nullable(); // ringkasan singkat
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->integer('views_count')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'order']);
            $table->index('category');
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
