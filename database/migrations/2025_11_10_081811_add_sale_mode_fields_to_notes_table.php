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
        Schema::table('notes', function (Blueprint $table) {
            $table->enum('sale_mode', ['scarcity', 'standard'])->default('scarcity')->after('is_sold');
            $table->integer('grace_period_days')->default(30)->after('sale_mode');
            $table->decimal('relist_price_multiplier', 5, 2)->default(1.5)->after('grace_period_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['sale_mode', 'grace_period_days', 'relist_price_multiplier']);
        });
    }
};
