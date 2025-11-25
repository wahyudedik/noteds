<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drm_license_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('note_id');
            $table->uuid('user_id');
            $table->uuid('transaction_id')->nullable();
            $table->string('license_key')->unique();
            $table->string('key_type')->default('per_user'); // per_user, per_device, per_download
            $table->string('device_id')->nullable(); // For per_device keys
            $table->boolean('is_active')->default(true);
            $table->integer('download_count')->default(0);
            $table->integer('max_downloads')->nullable(); // For per_download keys
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
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
            $table->index('license_key');
            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drm_license_keys');
    }
};

