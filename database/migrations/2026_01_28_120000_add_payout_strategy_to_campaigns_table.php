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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->enum('payout_strategy', ['cpm', 'multi_equal_split', 'single_winner'])
                ->default('cpm')
                ->after('ab_test_status');
            $table->integer('per_account_view_target')
                ->nullable()
                ->after('payout_strategy');
            $table->integer('global_target_views')
                ->nullable()
                ->after('per_account_view_target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['payout_strategy', 'per_account_view_target', 'global_target_views']);
        });
    }
};
