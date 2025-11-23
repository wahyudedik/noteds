<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_protection_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('money_back_guarantee_enabled')->default(true);
            $table->integer('money_back_guarantee_days')->default(7); // Days within which refund can be requested
            $table->boolean('auto_approve_refunds')->default(false); // Auto-approve refunds within guarantee period
            $table->decimal('max_refund_amount', 12, 2)->nullable(); // Max refund amount (null = unlimited)
            $table->json('refund_policy_rules')->nullable(); // Custom refund policy rules
            $table->boolean('quality_assurance_enabled')->default(true);
            $table->json('quality_check_criteria')->nullable(); // Quality check criteria
            $table->boolean('dispute_resolution_enabled')->default(true);
            $table->integer('dispute_resolution_days')->default(14); // Days to resolve dispute
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_protection_settings');
    }
};

