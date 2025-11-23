<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id');
            $table->uuid('user_id');
            $table->uuid('sequence_id')->nullable(); // For sequence emails
            $table->string('status')->default('pending'); // pending, sent, failed, bounced
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Additional data like note_id, etc.
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('email_campaigns')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sequence_id')->references('id')->on('email_sequences')->onDelete('set null');
            $table->index(['campaign_id', 'user_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
    }
};

