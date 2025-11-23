<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_email_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->boolean('new_note_notifications')->default(true);
            $table->boolean('weekly_digest')->default(true);
            $table->boolean('abandoned_cart_emails')->default(true);
            $table->boolean('marketing_emails')->default(true);
            $table->boolean('sequence_emails')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_email_preferences');
    }
};

