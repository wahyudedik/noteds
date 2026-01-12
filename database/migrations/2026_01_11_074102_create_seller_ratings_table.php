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
        Schema::create('seller_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('seller_id');
            $table->uuid('buyer_id');
            $table->uuid('order_id')->nullable();
            $table->decimal('rating', 3, 2);
            $table->decimal('review_rating', 3, 2)->nullable();
            $table->decimal('fulfillment_rating', 3, 2)->nullable();
            $table->decimal('response_rating', 3, 2)->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->index('seller_id');
            $table->index('buyer_id');
            $table->index('order_id');
            $table->unique(['seller_id', 'buyer_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_ratings', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropForeign(['buyer_id']);
            $table->dropForeign(['order_id']);
        });
        Schema::dropIfExists('seller_ratings');
    }
};
