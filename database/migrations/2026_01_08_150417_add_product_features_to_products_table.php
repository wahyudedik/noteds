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
            // Variants
            $table->uuid('parent_product_id')->nullable()->after('user_id');
            $table->enum('variant_type', ['size', 'color', 'version'])->nullable()->after('parent_product_id');
            $table->string('variant_value')->nullable()->after('variant_type');
            
            // Bundles
            $table->boolean('is_bundle')->default(false)->after('variant_value');
            $table->decimal('bundle_price', 15, 2)->nullable()->after('is_bundle');
            $table->decimal('bundle_discount_percentage', 5, 2)->nullable()->after('bundle_price');
            
            // Subscriptions
            $table->boolean('is_subscription')->default(false)->after('bundle_discount_percentage');
            $table->enum('subscription_interval', ['daily', 'weekly', 'monthly', 'yearly'])->nullable()->after('is_subscription');
            $table->integer('subscription_duration')->nullable()->after('subscription_interval');
            $table->integer('trial_days')->nullable()->after('subscription_duration');
            
            // Waitlist
            $table->boolean('is_waitlist_enabled')->default(false)->after('trial_days');
            $table->integer('waitlist_notify_at_stock')->nullable()->after('is_waitlist_enabled');
            
            // Foreign key for parent product
            $table->foreign('parent_product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Indexes
            $table->index('parent_product_id');
            $table->index('is_bundle');
            $table->index('is_subscription');
            $table->index('is_waitlist_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['parent_product_id']);
            $table->dropIndex(['parent_product_id']);
            $table->dropIndex(['is_bundle']);
            $table->dropIndex(['is_subscription']);
            $table->dropIndex(['is_waitlist_enabled']);
            
            $table->dropColumn([
                'parent_product_id',
                'variant_type',
                'variant_value',
                'is_bundle',
                'bundle_price',
                'bundle_discount_percentage',
                'is_subscription',
                'subscription_interval',
                'subscription_duration',
                'trial_days',
                'is_waitlist_enabled',
                'waitlist_notify_at_stock',
            ]);
        });
    }
};
