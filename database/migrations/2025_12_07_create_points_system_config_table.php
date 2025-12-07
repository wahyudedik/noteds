<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_system_config', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Config key-value
            $table->string('key', 100)->unique()->index();
            $table->longText('value');
            $table->enum('type', ['string', 'integer', 'decimal', 'boolean', 'json'])->default('string');

            // Metadata
            $table->text('description')->nullable();
            $table->enum('category', ['earning', 'redemption', 'marketplace', 'fraud_prevention', 'general'])
                ->default('general')->index();

            // Status
            $table->boolean('is_active')->default(true)->index();

            // Tracking
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_system_config');
    }
};
