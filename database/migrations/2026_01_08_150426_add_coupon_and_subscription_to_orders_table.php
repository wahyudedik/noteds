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
            $table->uuid('coupon_id')->nullable()->after('seller_amount');
            $table->decimal('discount_amount', 15, 2)->nullable()->after('coupon_id');
            $table->boolean('is_subscription_order')->default(false)->after('discount_amount');
            $table->uuid('subscription_id')->nullable()->after('is_subscription_order');
            
            $table->foreign('coupon_id')->references('id')->on('product_coupons')->onDelete('set null');
            $table->foreign('subscription_id')->references('id')->on('product_subscriptions')->onDelete('set null');
            $table->index('coupon_id');
            $table->index('subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropForeign(['subscription_id']);
            $table->dropIndex(['coupon_id']);
            $table->dropIndex(['subscription_id']);
            
            $table->dropColumn([
                'coupon_id',
                'discount_amount',
                'is_subscription_order',
                'subscription_id',
            ]);
        });
    }
};
