<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // 'abandoned_cart', 'new_note_following', 'weekly_digest', 'sequence', 'custom'
            $table->string('subject');
            $table->longText('html_content');
            $table->longText('text_content')->nullable();
            $table->json('variables')->nullable(); // Available variables for this template
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};

