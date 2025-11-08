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
        Schema::create('note_review_replies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('review_id');
            $table->uuid('user_id');
            $table->uuid('parent_id')->nullable();
            $table->text('message');
            $table->timestamps();

            $table->foreign('review_id')->references('id')->on('note_reviews')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('note_review_replies')->cascadeOnDelete();

            $table->index(['review_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_review_replies');
    }
};
