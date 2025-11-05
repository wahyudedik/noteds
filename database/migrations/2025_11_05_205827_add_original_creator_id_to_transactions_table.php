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
            $table->foreignUuid('original_creator_id')->nullable()->after('seller_id')->constrained('users')->onDelete('cascade');
            $table->decimal('platform_fee', 12, 2)->default(0)->after('commission');
            $table->decimal('creator_commission', 12, 2)->default(0)->after('platform_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['original_creator_id']);
            $table->dropColumn(['original_creator_id', 'platform_fee', 'creator_commission']);
        });
    }
};
