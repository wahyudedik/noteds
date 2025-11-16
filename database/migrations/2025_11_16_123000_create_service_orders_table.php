<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->index()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('budget', 12, 2)->default(0);
            $table->enum('status', ['draft', 'submitted', 'quoted', 'in_progress', 'completed', 'cancelled'])->default('submitted')->index();
            $table->decimal('escrow_amount', 12, 2)->default(0);
            $table->json('milestones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};


