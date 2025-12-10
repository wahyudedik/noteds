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
        Schema::create('contest_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true)->comment('Enable/disable contest feature');
            $table->decimal('platform_fee_percentage', 5, 2)->default(10)->comment('Platform fee percentage when contest created');
            $table->text('terms_and_conditions')->nullable()->comment('Terms for contest creation');
            $table->text('approval_guidelines')->nullable()->comment('Guidelines for admin approval');
            $table->integer('max_contests_per_buyer')->default(10)->comment('Max active contests per buyer');
            $table->integer('max_prize_amount')->nullable()->comment('Max total prize amount allowed');
            $table->boolean('require_kyc')->default(false)->comment('Require KYC for contest creation');
            $table->boolean('auto_distribute_prizes')->default(true)->comment('Auto distribute prizes after selecting winners');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contest_settings');
    }
};
