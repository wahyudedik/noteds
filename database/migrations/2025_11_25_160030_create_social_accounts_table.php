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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // google, facebook, github
            $table->string('provider_id'); // ID dari provider
            $table->string('provider_token')->nullable(); // OAuth token
            $table->string('provider_refresh_token')->nullable(); // Refresh token
            $table->json('provider_data')->nullable(); // Additional data dari provider
            $table->timestamps();

            // Unique constraint: one social account per provider per user
            $table->unique(['user_id', 'provider']);
            // Unique constraint: one provider_id per provider
            $table->unique(['provider', 'provider_id']);
            $table->index('user_id');
            $table->index('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
