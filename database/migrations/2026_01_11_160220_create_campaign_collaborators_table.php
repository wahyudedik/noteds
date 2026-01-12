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
        Schema::create('campaign_collaborators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id');
            $table->uuid('user_id');
            $table->enum('role', ['co_creator', 'manager', 'viewer'])->default('co_creator');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->boolean('can_edit')->default(true);
            $table->boolean('can_manage_budget')->default(false);
            $table->boolean('can_activate')->default(false);
            $table->uuid('invited_by');
            $table->timestamp('invited_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('invited_by')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['campaign_id', 'user_id']);
            $table->index(['campaign_id', 'status']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_collaborators');
    }
};
