<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escrows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id')->unique();
            $table->uuid('buyer_id');
            $table->uuid('seller_id');
            $table->uuid('note_id');
            $table->decimal('amount', 12, 2);
            $table->decimal('escrow_fee', 12, 2)->default(0);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, funded, released, refunded, disputed
            $table->timestamp('funded_at')->nullable();
            $table->timestamp('auto_release_at')->nullable(); // Auto-release date
            $table->integer('auto_release_days')->default(7); // Days until auto-release
            $table->timestamp('released_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->uuid('released_by')->nullable(); // Buyer or admin who released
            $table->uuid('refunded_by')->nullable(); // Admin who refunded
            $table->text('release_notes')->nullable();
            $table->text('refund_reason')->nullable();
            $table->uuid('dispute_id')->nullable(); // Linked dispute
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            $table->foreign('released_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('refunded_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('dispute_id')->references('id')->on('disputes')->onDelete('set null');
            $table->index('status');
            $table->index('auto_release_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escrows');
    }
};

