<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DEPRECATED: This migration creates the ai_feature_usages table.
 * AI features have been removed from the application (January 2025).
 * This migration is kept for backward compatibility with existing databases.
 * The table can be dropped in the future if needed.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_feature_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('feature', 100);
            $table->boolean('is_paid')->default(false);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->string('status', 20)->default('success');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'feature']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_feature_usages');
    }
};

