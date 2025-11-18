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
        Schema::create('share_points', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained('notes')->onDelete('cascade');
            $table->foreignUuid('share_referral_id')->nullable()->constrained('note_share_referrals')->onDelete('set null');
            $table->integer('points')->default(0);
            $table->string('action')->default('share'); // share, click, purchase
            $table->date('earned_date'); // For monthly aggregation
            $table->timestamps();

            $table->index('user_id');
            $table->index('note_id');
            $table->index('earned_date');
            $table->index(['user_id', 'earned_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_points');
    }
};
