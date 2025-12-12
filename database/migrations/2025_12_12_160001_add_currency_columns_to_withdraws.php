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
        Schema::table('withdraws', function (Blueprint $table) {
            // Add currency tracking columns
            $table->string('currency', 3)->default('IDR')->after('amount')->comment('Currency of the withdrawal amount');
            $table->decimal('original_amount', 12, 2)->nullable()->after('currency')->comment('Original amount in base currency (IDR)');
            $table->string('original_currency', 3)->default('IDR')->after('original_amount')->comment('Original currency (always IDR)');
            $table->decimal('exchange_rate', 10, 6)->default(1)->after('original_currency')->comment('Exchange rate used for conversion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdraws', function (Blueprint $table) {
            $table->dropColumn(['currency', 'original_amount', 'original_currency', 'exchange_rate']);
        });
    }
};
