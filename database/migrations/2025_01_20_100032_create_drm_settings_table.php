<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drm_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('note_id');
            $table->boolean('enabled')->default(false);
            $table->boolean('encrypt_files')->default(false);
            $table->boolean('time_limited_access')->default(false);
            $table->integer('access_duration_days')->nullable(); // Days from purchase
            $table->boolean('device_limit_enabled')->default(false);
            $table->integer('max_devices')->default(3);
            $table->boolean('license_key_enabled')->default(false);
            $table->string('license_key_type')->default('per_user'); // per_user, per_device, per_download
            $table->json('metadata')->nullable(); // Additional DRM settings
            $table->timestamps();

            // Add foreign key only if table exists
            if (Schema::hasTable('notes')) {
                $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            }
            $table->index('note_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drm_settings');
    }
};

