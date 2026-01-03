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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('platform_commission_percentage', 5, 2)->nullable()->after('total');
            $table->decimal('platform_commission_flat', 15, 2)->default(0)->after('platform_commission_percentage');
            $table->decimal('platform_commission_total', 15, 2)->default(0)->after('platform_commission_flat');
            $table->decimal('seller_amount', 15, 2)->nullable()->after('platform_commission_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'platform_commission_percentage',
                'platform_commission_flat',
                'platform_commission_total',
                'seller_amount',
            ]);
        });
    }
};
