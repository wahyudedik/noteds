<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Laravel Expert", "Design Pro"
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('category'); // 'development', 'design', 'marketing', etc.
            $table->string('icon')->nullable(); // Icon untuk certification
            $table->string('color')->default('#667eea'); // Color untuk badge
            $table->json('requirements')->nullable(); // Requirements untuk mendapatkan certification
            $table->text('benefits')->nullable(); // Benefits dari certification ini
            $table->boolean('requires_application')->default(true); // Apakah perlu apply atau auto
            $table->boolean('requires_approval')->default(true); // Apakah perlu admin approval
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};

