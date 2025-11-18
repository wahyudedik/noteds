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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->json('interests')->nullable(); // Array of interest tags/categories
            $table->json('preferred_categories')->nullable(); // Array of preferred ecosystem categories
            $table->json('preferred_tags')->nullable(); // Array of preferred tag IDs
            $table->json('browsing_history_summary')->nullable(); // Summary of browsing patterns
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
