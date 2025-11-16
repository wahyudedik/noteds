<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_quotes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_order_id')->index()->constrained()->onDelete('cascade');
            $table->foreignUuid('vendor_id')->index()->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->json('milestones')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_quotes');
    }
};


