<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id')->nullable();
            $table->string('name');
            $table->string('trigger_event'); // 'user_registered', 'first_purchase', 'no_purchase_7days', etc.
            $table->integer('delay_days')->default(0); // Days after trigger to send
            $table->integer('delay_hours')->default(0); // Hours after trigger to send
            $table->text('subject');
            $table->longText('content');
            $table->integer('order')->default(0); // Order in sequence
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('email_campaigns')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sequences');
    }
};

