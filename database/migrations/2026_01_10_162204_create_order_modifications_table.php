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
        Schema::create('order_modifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->enum('modification_type', ['quantity', 'product', 'coupon', 'all'])->default('quantity');
            $table->json('old_data');
            $table->json('new_data');
            $table->uuid('modified_by');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_modifications', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['modified_by']);
        });
        Schema::dropIfExists('order_modifications');
    }
};
