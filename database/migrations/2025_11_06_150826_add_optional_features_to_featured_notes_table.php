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
        Schema::table('featured_notes', function (Blueprint $table) {
            // Scheduled ads: allow scheduling for future dates
            $table->date('scheduled_date')->nullable()->after('end_date');
            
            // A/B Testing: variant identifier (A, B, C, etc.)
            $table->string('variant', 10)->nullable()->after('location');
            
            // Bulk discount: discount percentage for multiple locations
            $table->decimal('discount_percent', 5, 2)->default(0)->after('price');
            
            // Custom duration: flag to indicate if duration is custom (not 7/14/30)
            $table->boolean('is_custom_duration')->default(false)->after('duration_days');
            
            // Parent ID: for tracking bulk purchases (multiple locations in one purchase)
            $table->uuid('parent_id')->nullable()->after('user_id');
            
            // Add indexes
            $table->index('scheduled_date');
            $table->index('variant');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('featured_notes', function (Blueprint $table) {
            $table->dropColumn([
                'scheduled_date',
                'variant',
                'discount_percent',
                'is_custom_duration',
                'parent_id',
            ]);
        });
    }
};
