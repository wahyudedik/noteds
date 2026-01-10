<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Make product_id nullable for bulk orders
            $table->uuid('product_id')->nullable()->change();
            
            // Add bulk order flag
            $table->boolean('is_bulk_order')->default(false)->after('product_id');
            
            // Add tracking fields
            $table->boolean('tracking_enabled')->default(true)->after('is_bulk_order');
            $table->timestamp('last_tracked_at')->nullable()->after('tracking_enabled');
            
            // Add cancellation fields
            $table->text('cancellation_reason')->nullable()->after('payment_status');
            $table->uuid('cancelled_by')->nullable()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            
            // Add foreign key for cancelled_by
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
            $table->index('is_bulk_order');
            $table->index('tracking_enabled');
            $table->index('last_tracked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if bulk orders exist before dropping columns
        $hasBulkOrders = false;
        try {
            if (Schema::hasColumn('orders', 'is_bulk_order')) {
                $hasBulkOrders = DB::table('orders')->where('is_bulk_order', true)->exists();
            }
        } catch (\Exception $e) {
            // If check fails, assume no bulk orders exist
            $hasBulkOrders = false;
        }
        
        Schema::table('orders', function (Blueprint $table) use ($hasBulkOrders) {
            $table->dropForeign(['cancelled_by']);
            $table->dropIndex(['is_bulk_order']);
            $table->dropIndex(['tracking_enabled']);
            $table->dropIndex(['last_tracked_at']);
            
            $table->dropColumn([
                'is_bulk_order',
                'tracking_enabled',
                'last_tracked_at',
                'cancellation_reason',
                'cancelled_by',
                'cancelled_at',
            ]);
        });
        
        // Change product_id back to not nullable if safe (no bulk orders)
        if (!$hasBulkOrders) {
            try {
                Schema::table('orders', function (Blueprint $table) {
                    $table->uuid('product_id')->nullable(false)->change();
                });
            } catch (\Exception $e) {
                // If change fails (e.g., null values exist), leave as nullable
                // This is safer than failing the entire rollback
            }
        }
    }
};
