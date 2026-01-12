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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('low_stock_threshold')->nullable()->after('stock');
            $table->timestamp('stock_alert_sent_at')->nullable()->after('low_stock_threshold');
            $table->decimal('base_price', 15, 2)->nullable()->after('price');
            $table->decimal('current_dynamic_price', 15, 2)->nullable()->after('base_price');
            $table->boolean('pricing_rules_enabled')->default(false)->after('current_dynamic_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'low_stock_threshold',
                'stock_alert_sent_at',
                'base_price',
                'current_dynamic_price',
                'pricing_rules_enabled',
            ]);
        });
    }
};
