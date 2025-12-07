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
        Schema::create('points_pricing_config', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g., "Basic Discount", "Premium Feature"
            $table->enum('type', ['discount', 'premium_feature']); // Type of redemption
            $table->integer('points_required'); // How many points needed
            $table->decimal('discount_amount', 12, 2)->nullable(); // For discount type
            $table->integer('discount_percent')->nullable(); // Percentage discount
            $table->integer('premium_days')->nullable(); // For premium feature type
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('daily_limit')->nullable(); // Max redemptions per day across all users
            $table->integer('user_limit')->nullable(); // Max redemptions per user
            $table->timestamp('expires_at')->nullable(); // When this offer expires
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
            $table->index('points_required');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_pricing_config');
    }
};
