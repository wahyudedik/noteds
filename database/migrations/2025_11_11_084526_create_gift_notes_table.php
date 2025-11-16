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
        Schema::create('gift_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('gifter_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('recipient_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'sent', 'claimed', 'expired'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['gifter_id', 'status']);
            $table->index(['recipient_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_notes');
    }
};
