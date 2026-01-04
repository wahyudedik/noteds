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
        // Add 'refund' and 'adjustment' to transactions.type enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('sale', 'withdrawal', 'deposit', 'refund', 'adjustment') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('sale', 'withdrawal', 'deposit') NOT NULL");
    }
};
