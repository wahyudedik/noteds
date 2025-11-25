<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('refund_id')->nullable(); // Optional: linked to refund
            $table->uuid('transaction_id');
            $table->uuid('buyer_id');
            $table->uuid('seller_id');
            $table->uuid('note_id');
            $table->string('type'); // 'refund', 'quality', 'delivery', 'other'
            $table->string('status')->default('open'); // open, in_review, resolved, closed
            $table->text('buyer_complaint');
            $table->text('seller_response')->nullable();
            $table->text('admin_resolution')->nullable();
            $table->uuid('resolved_by')->nullable(); // Admin who resolved
            $table->timestamp('resolved_at')->nullable();
            $table->json('evidence')->nullable(); // Evidence files/links
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('refunds')) {
                $table->foreign('refund_id')->references('id')->on('refunds')->onDelete('set null');
            }
            if (Schema::hasTable('transactions')) {
                $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            }
            if (Schema::hasTable('users')) {
                $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            }
            if (Schema::hasTable('notes')) {
                $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            }
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};

