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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->uuid('admin_id')->nullable();
            $table->string('action');
            $table->string('target_type');
            $table->uuid('target_id');
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->text('notes')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['target_type', 'target_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
