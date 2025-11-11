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
        Schema::table('workspaces', function (Blueprint $table) {
            // Add original creator for commission tracking
            $table->foreignUuid('original_creator_id')->nullable()->after('owner_id')->constrained('users')->onDelete('set null');
            
            // Add sale mode fields (same as notes)
            $table->string('sale_mode')->default('scarcity')->after('is_for_sale'); // 'scarcity' or 'standard'
            $table->integer('grace_period_days')->default(30)->after('sale_mode'); // Grace period for repurchase
            $table->decimal('relist_price_multiplier', 5, 2)->default(1.5)->after('grace_period_days'); // Multiplier for repurchase after grace period
            
            // Add discount price
            $table->decimal('discount_price', 12, 2)->nullable()->after('price');
            
            // Add attachments and thumbnails (for bundle workspace)
            $table->json('attachments')->nullable()->after('marketplace_description'); // Array of file paths
            $table->json('thumbnails')->nullable()->after('attachments'); // Array of thumbnail paths
            $table->integer('file_count')->default(0)->after('thumbnails'); // Number of attached files
            
            // Add status field (same as notes)
            $table->string('status')->default('active')->after('file_count'); // 'active', 'sold', 'inactive'
            
            // Add is_public field (for marketplace visibility)
            $table->boolean('is_public')->default(false)->after('status');
            
            // Add is_sold field (for scarcity mode tracking)
            $table->boolean('is_sold')->default(false)->after('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropForeign(['original_creator_id']);
            $table->dropColumn([
                'original_creator_id',
                'sale_mode',
                'grace_period_days',
                'relist_price_multiplier',
                'discount_price',
                'attachments',
                'thumbnails',
                'file_count',
                'status',
                'is_public',
                'is_sold',
            ]);
        });
    }
};

