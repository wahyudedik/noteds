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
        Schema::create('levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['seller', 'buyer'])->default('seller');
            $table->integer('level_order')->default(0); // For sorting (1, 2, 3, etc.)
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // e.g., 🥉, 🥈, 🥇, 💎
            $table->string('color')->default('gray'); // e.g., bronze, silver, gold, platinum, diamond
            $table->decimal('commission_discount_percent', 5, 2)->default(0); // Discount on platform commission
            $table->boolean('priority_support')->default(false);
            $table->boolean('early_access')->default(false);
            $table->text('benefits')->nullable(); // JSON or text description of benefits
            $table->string('criteria_type')->nullable(); // e.g., total_sales, total_revenue, purchase_count, spending
            $table->decimal('criteria_value', 12, 2)->default(0); // Threshold value
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('level_order');
            $table->index('is_active');
            $table->index(['type', 'level_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
