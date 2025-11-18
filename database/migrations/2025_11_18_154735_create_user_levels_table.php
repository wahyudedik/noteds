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
        Schema::create('user_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('level_id')->constrained('levels')->onDelete('cascade');
            $table->enum('type', ['seller', 'buyer'])->default('seller');
            $table->timestamp('achieved_at')->useCurrent();
            $table->text('notes')->nullable(); // e.g., "Achieved 100 sales"
            $table->timestamps();

            $table->unique(['user_id', 'type']); // One level per type per user
            $table->index('user_id');
            $table->index('level_id');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_levels');
    }
};
