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
        Schema::create('user_points', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('points')->default(0);
            $table->string('action')->nullable(); // purchase, review, share, etc.
            $table->string('source_type')->nullable(); // polymorphic: note, transaction, review, etc.
            $table->uuid('source_id')->nullable(); // polymorphic ID
            $table->text('description')->nullable();
            $table->date('expires_at')->nullable(); // For expiration system
            $table->boolean('is_redeemed')->default(false);
            $table->uuid('redemption_id')->nullable(); // Link to redemption if used
            $table->timestamps();

            $table->index('user_id');
            $table->index('action');
            $table->index(['source_type', 'source_id']);
            $table->index('expires_at');
            $table->index('is_redeemed');
            $table->index(['user_id', 'expires_at']);
        });

        Schema::create('point_redemptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('redemption_type'); // discount, premium_feature, etc.
            $table->string('redemption_code')->nullable(); // For discount codes
            $table->integer('points_used')->default(0);
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->integer('premium_days')->nullable(); // For premium feature redemption
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'active', 'used', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('redemption_type');
            $table->index('redemption_code');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_redemptions');
        Schema::dropIfExists('user_points');
    }
};
