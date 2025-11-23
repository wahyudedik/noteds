<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable(); // Nullable for guest users
            $table->uuid('note_id');
            $table->string('email')->nullable(); // For guest users
            $table->string('ip_address')->nullable();
            $table->timestamp('viewed_at');
            $table->timestamp('email_sent_at')->nullable();
            $table->integer('email_count')->default(0); // Track how many emails sent
            $table->boolean('purchased')->default(false);
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            $table->index(['user_id', 'note_id']);
            $table->index(['email', 'note_id']);
            $table->index('purchased');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};

