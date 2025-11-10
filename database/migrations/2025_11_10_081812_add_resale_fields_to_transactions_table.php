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
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('resale_price', 12, 2)->nullable()->after('amount');
            $table->timestamp('sold_at')->nullable()->after('resale_price');
            $table->timestamp('grace_period_ends_at')->nullable()->after('sold_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['resale_price', 'sold_at', 'grace_period_ends_at']);
        });
    }
};
