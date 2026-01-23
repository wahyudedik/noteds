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
        // First, update existing 'clipper' values to 'marketplace' if any exist
        DB::table('refunds')
            ->where('wallet_type', 'clipper')
            ->update(['wallet_type' => 'marketplace']);
        
        // Then change wallet_type enum from ['creator', 'clipper'] to ['creator', 'marketplace']
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE refunds MODIFY COLUMN wallet_type ENUM('creator', 'marketplace') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ['creator', 'clipper']
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE refunds MODIFY COLUMN wallet_type ENUM('creator', 'clipper') NOT NULL");
        }
    }
};
