<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drm_access_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('note_id');
            $table->uuid('user_id');
            $table->uuid('transaction_id')->nullable(); // Related purchase transaction
            $table->string('device_id')->nullable(); // Device identifier (hash of user agent + IP)
            $table->string('device_fingerprint')->nullable(); // More detailed device fingerprint
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action')->default('download'); // download, view, access
            $table->string('file_path')->nullable();
            $table->string('license_key')->nullable();
            $table->timestamp('accessed_at');
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('notes')) {
                $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            }
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            if (Schema::hasTable('transactions')) {
                $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            }
            $table->index(['note_id', 'user_id']);
            $table->index('device_id');
            $table->index('accessed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drm_access_logs');
    }
};

