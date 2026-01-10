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
        Schema::create('comment_reactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('comment_id');
            $table->string('emoji', 10); // Store emoji character
            $table->unsignedBigInteger('count')->default(0);
            $table->timestamps();
            
            $table->foreign('comment_id')->references('id')->on('comments')->onDelete('cascade');
            $table->unique(['comment_id', 'emoji']);
            $table->index('comment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};
