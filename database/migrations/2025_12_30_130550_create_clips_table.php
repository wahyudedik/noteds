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
        Schema::create('clips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id');
            $table->uuid('clipper_id');
            $table->string('content_url');
            $table->enum('platform', ['tiktok', 'instagram', 'youtube', 'other']);
            $table->string('platform_content_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->integer('valid_views')->default(0);
            $table->decimal('estimated_reward', 15, 2)->default(0);
            $table->decimal('pending_reward', 15, 2)->default(0);
            $table->decimal('approved_reward', 15, 2)->default(0);
            $table->decimal('rejected_reward', 15, 2)->default(0);
            $table->timestamp('submitted_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('clipper_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['campaign_id', 'status']);
            $table->index(['clipper_id', 'status']);
            $table->index('status');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clips');
    }
};
