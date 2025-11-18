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
        Schema::create('note_share_referrals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('note_id')->constrained('notes')->onDelete('cascade');
            $table->foreignUuid('sharer_id')->constrained('users')->onDelete('cascade');
            $table->string('referral_token', 64)->unique();
            $table->integer('click_count')->default(0);
            $table->integer('purchase_count')->default(0);
            $table->decimal('total_commission_earned', 12, 2)->default(0);
            $table->decimal('total_revenue_generated', 12, 2)->default(0);
            $table->timestamps();

            $table->index('note_id');
            $table->index('sharer_id');
            $table->index('referral_token');
            $table->index(['note_id', 'sharer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_share_referrals');
    }
};
