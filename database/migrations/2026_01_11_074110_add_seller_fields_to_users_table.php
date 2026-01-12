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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_verified_seller')->default(false)->after('is_verified_mentor');
            $table->decimal('seller_rating', 3, 2)->nullable()->after('is_verified_seller');
            $table->integer('low_stock_alert_threshold')->default(10)->after('seller_rating');
            $table->boolean('low_stock_alert_enabled')->default(true)->after('low_stock_alert_threshold');
            
            $table->index('is_verified_seller');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_verified_seller']);
            $table->dropColumn([
                'is_verified_seller',
                'seller_rating',
                'low_stock_alert_threshold',
                'low_stock_alert_enabled',
            ]);
        });
    }
};
