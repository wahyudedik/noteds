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
        Schema::create('campaign_wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id');
            $table->decimal('total_budget', 15, 2)->default(0);
            $table->decimal('remaining_budget', 15, 2)->default(0);
            $table->decimal('locked_amount', 15, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->unique('campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_wallets');
    }
};
