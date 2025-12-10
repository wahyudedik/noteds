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
        Schema::table('contests', function (Blueprint $table) {
            $table->decimal('total_prize_amount', 12, 2)->default(0)->comment('Total prize amount for the contest');
            $table->decimal('frozen_amount', 12, 2)->default(0)->comment('Amount frozen in admin wallet');
            $table->decimal('distributed_amount', 12, 2)->default(0)->comment('Amount distributed to winners');
            $table->timestamp('distributed_at')->nullable()->comment('When prizes were distributed to winners');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn([
                'total_prize_amount',
                'frozen_amount',
                'distributed_amount',
                'distributed_at'
            ]);
        });
    }
};
