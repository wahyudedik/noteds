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
        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('wallet_type', ['creator', 'clipper']);
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['refund', 'adjustment']);
            $table->text('reason')->nullable();
            $table->uuid('admin_id');
            $table->text('admin_notes')->nullable();
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->uuid('ledger_entry_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ledger_entry_id')->references('id')->on('ledger_entries')->onDelete('set null');
            
            $table->index('user_id');
            $table->index('wallet_type');
            $table->index('admin_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
