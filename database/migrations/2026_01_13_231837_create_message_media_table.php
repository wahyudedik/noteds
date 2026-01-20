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
        Schema::create('message_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // In bytes
            $table->string('thumbnail_path')->nullable(); // For images
            $table->unsignedInteger('duration')->nullable(); // For voice messages, in seconds
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
            
            $table->index('message_id');
            $table->index(['message_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_media');
    }
};
