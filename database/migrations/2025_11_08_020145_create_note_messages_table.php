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
        Schema::create('note_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('conversation_id');
            $table->uuid('sender_id');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('note_conversations')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_messages');
    }
};
