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
        Schema::table('product_reviews', function (Blueprint $table) {
            // Add new columns first
            $table->enum('status', ['active', 'moderated', 'archived'])->default('active')->after('comment');
            $table->boolean('is_verified_purchase')->default(false)->after('status');
            $table->unsignedBigInteger('helpful_count')->default(0)->after('is_verified_purchase');
            $table->boolean('is_locked')->default(false)->after('helpful_count');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            
            // Add indexes
            $table->index('status');
            $table->index('is_verified_purchase');
            $table->index('helpful_count');
            
            // Note: We keep the existing unique constraint on ['user_id', 'product_id', 'order_id']
            // This allows multiple reviews per user/product (when order_id is NULL)
            // But only one verified review per order (when order_id is provided)
            // Application logic enforces one verified review per order
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['is_verified_purchase']);
            $table->dropIndex(['helpful_count']);
            
            $table->dropColumn([
                'status',
                'is_verified_purchase',
                'helpful_count',
                'is_locked',
                'locked_at',
            ]);
        });
    }
};
