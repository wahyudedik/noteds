<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('posts', 'campaign_id')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropForeign(['campaign_id']);
                $table->dropIndex(['campaign_id']);
                $table->dropColumn('campaign_id');
            });
        }

        if (Schema::hasColumn('moderation_logs', 'product_review_id')) {
            Schema::table('moderation_logs', function (Blueprint $table) {
                $table->dropForeign(['product_review_id']);
                $table->dropIndex(['product_review_id']);
                $table->dropColumn('product_review_id');
            });
        }

        if (Schema::hasColumn('users', 'clipper_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('clipper_role');
            });
        }

        if (Schema::hasColumn('users', 'is_verified_seller')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_is_verified_seller_index');
                $table->dropColumn([
                    'is_verified_seller',
                    'seller_rating',
                    'low_stock_alert_threshold',
                    'low_stock_alert_enabled',
                ]);
            });
        }

        if (Schema::hasColumn('users', 'midtrans_merchant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('midtrans_merchant_id');
            });
        }

        Schema::dropIfExists('product_release_schedules');

        Schema::dropIfExists('campaign_collaborators');
        Schema::dropIfExists('campaign_variants');
        Schema::dropIfExists('campaign_templates');
        Schema::dropIfExists('campaign_wallets');
        Schema::dropIfExists('creator_wallets');
        Schema::dropIfExists('clipper_wallets');
        Schema::dropIfExists('platform_wallets');
        Schema::dropIfExists('clip_view_tracking');
        Schema::dropIfExists('clips');
        Schema::dropIfExists('top_ups');
        Schema::dropIfExists('brand_registrations');
        Schema::dropIfExists('clipper_profiles');
        Schema::dropIfExists('clipper_registrations');
        Schema::dropIfExists('campaigns');

        Schema::dropIfExists('order_modifications');
        Schema::dropIfExists('order_tracking_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('downloads');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('product_coupons');
        Schema::dropIfExists('subscription_renewals');
        Schema::dropIfExists('product_subscriptions');
        Schema::dropIfExists('product_bundle_items');
        Schema::dropIfExists('product_waitlists');
        Schema::dropIfExists('product_review_votes');
        Schema::dropIfExists('product_review_media');
        Schema::dropIfExists('product_review_replies');
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('product_pricing_rule_applications');
        Schema::dropIfExists('product_pricing_rules');
        Schema::dropIfExists('product_stock_history');
        Schema::dropIfExists('seller_performance_metrics');
        Schema::dropIfExists('seller_ratings');
        Schema::dropIfExists('seller_verifications');
        Schema::dropIfExists('products');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('user_payment_methods');
        Schema::dropIfExists('ledger_entries');

        Schema::dropIfExists('stock_predictions');
        Schema::dropIfExists('stock_signals');
        Schema::dropIfExists('stock_technical_indicators');
        Schema::dropIfExists('stock_prices');
        Schema::dropIfExists('stock_watchlists');
        Schema::dropIfExists('stock_screenings');
        Schema::dropIfExists('portfolio_recommendations');
        Schema::dropIfExists('ml_models');
        Schema::dropIfExists('stocks');
    }

    public function down(): void {}
};
