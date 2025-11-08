<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('note_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('note_id');
            $table->uuid('buyer_id');
            $table->uuid('seller_id');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('note_id')->references('id')->on('notes')->cascadeOnDelete();
            $table->foreign('buyer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('seller_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['note_id', 'buyer_id', 'seller_id'], 'note_conversations_unique_triplet');
            $table->index(['seller_id', 'last_message_at']);
            $table->index(['buyer_id', 'last_message_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_conversations');
    }
};
