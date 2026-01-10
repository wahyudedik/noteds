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
        Schema::create('order_tracking_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->enum('status', ['pending', 'paid', 'completed', 'cancelled', 'processing', 'shipped', 'delivered'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->nullable();
            $table->string('message')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->index('order_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_tracking_history', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['updated_by']);
        });
        Schema::dropIfExists('order_tracking_history');
    }
};
