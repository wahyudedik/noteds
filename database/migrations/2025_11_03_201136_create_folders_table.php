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
        Schema::create('folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('parent_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->string('name');
            $table->string('color', 7)->nullable(); // Hex color code for folder icon
            $table->text('description')->nullable();
            $table->integer('order')->default(0); // For custom sorting
            $table->timestamps();

            $table->index(['user_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
