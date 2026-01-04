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
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('activity_type'); // login, profile_change, security_change, transaction, withdrawal, product_upload, product_download
            $table->string('action'); // e.g., 'logged_in', 'password_changed', 'email_changed', 'profile_updated', 'transaction_created', 'withdrawal_requested', etc.
            $table->string('description')->nullable(); // Human-readable description
            $table->json('metadata')->nullable(); // Additional context (old_value, new_value, transaction_id, withdrawal_id, etc.)
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('location')->nullable(); // Optional: geolocation from IP
            $table->timestamp('created_at');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
