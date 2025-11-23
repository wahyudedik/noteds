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
        Schema::create('search_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable(); // Nullable for guest users
            $table->string('ip_address', 45)->nullable(); // For guest tracking
            $table->text('query'); // Search query string
            $table->json('filters')->nullable(); // All filter parameters as JSON
            $table->integer('result_count')->default(0); // Number of results
            $table->timestamp('searched_at')->useCurrent();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'searched_at']);
            $table->index('ip_address');
            $table->index('searched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_histories');
    }
};
