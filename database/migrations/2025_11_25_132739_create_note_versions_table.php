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
        Schema::create('note_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->integer('version_number')->default(1);
            $table->string('title');
            $table->text('content');
            $table->text('summary')->nullable();
            $table->json('metadata')->nullable(); // Store additional data like tags, attachments, etc.
            $table->text('change_description')->nullable(); // What changed in this version
            $table->boolean('is_current')->default(false); // Mark current version
            $table->timestamps();

            $table->index(['note_id', 'version_number']);
            $table->index(['note_id', 'is_current']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_versions');
    }
};
