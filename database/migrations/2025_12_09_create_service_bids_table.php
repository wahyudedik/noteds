<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_bids', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_order_id')->index()->constrained()->onDelete('cascade');
            $table->foreignUuid('vendor_id')->index()->constrained('users')->onDelete('cascade');
            $table->decimal('proposed_amount', 12, 2);
            $table->text('proposal_notes')->nullable();
            $table->integer('estimated_days')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'withdrawn'])->default('pending')->index();
            $table->timestamp('submitted_at');
            $table->timestamp('responded_at')->nullable();
            $table->string('response_reason')->nullable();
            $table->timestamps();

            // Unique constraint: One bid per vendor per order
            $table->unique(['service_order_id', 'vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_bids');
    }
};
