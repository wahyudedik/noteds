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
        Schema::create('affiliate_commission_tiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('min_conversions')->default(0)->comment('Minimum conversions to qualify');
            $table->decimal('min_revenue', 12, 2)->default(0)->comment('Minimum revenue to qualify');
            $table->decimal('tier_1_rate', 5, 2)->default(10.0)->comment('Tier 1 commission rate %');
            $table->decimal('tier_2_rate', 5, 2)->default(5.0)->comment('Tier 2 commission rate %');
            $table->decimal('tier_3_rate', 5, 2)->default(2.0)->comment('Tier 3 commission rate %');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_commission_tiers');
    }
};
