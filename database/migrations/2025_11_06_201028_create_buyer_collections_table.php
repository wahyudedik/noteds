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
        Schema::create('buyer_collections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6')->comment('Hex color for collection');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('buyer_collection_notes', function (Blueprint $table) {
            $table->foreignUuid('collection_id')->constrained('buyer_collections')->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();

            // Unique constraint: one note can only be in a collection once
            $table->unique(['collection_id', 'note_id']);

            $table->index('collection_id');
            $table->index('note_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_collection_notes');
        Schema::dropIfExists('buyer_collections');
    }
};
