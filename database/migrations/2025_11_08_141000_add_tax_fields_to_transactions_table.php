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
            $table->decimal('tax_percent', 5, 2)->default(0)->after('creator_commission');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
            $table->boolean('tax_inclusive')->default(true)->after('tax_amount');
            $table->string('tax_country_code', 3)->nullable()->after('tax_inclusive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['tax_percent', 'tax_amount', 'tax_inclusive', 'tax_country_code']);
        });
    }
};

