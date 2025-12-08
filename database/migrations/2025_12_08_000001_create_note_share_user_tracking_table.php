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
        Schema::create('note_share_user_tracking', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('share_referral_id')->constrained('note_share_referrals')->onDelete('cascade');
            $table->uuid('user_id')->nullable()->comment('User yang melakukan share (null jika anonymous)');
            $table->integer('share_count')->default(1)->comment('Berapa kali user share link ini');
            $table->timestamps();

            $table->unique(['share_referral_id', 'user_id']);
            $table->index(['share_referral_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_share_user_tracking');
    }
};
