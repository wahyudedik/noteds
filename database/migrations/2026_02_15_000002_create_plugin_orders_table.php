<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugin_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('plugin_id');
            $table->decimal('price_paid', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'rejected'])->default('pending');
            $table->string('proof_file')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
            $table->index('user_id');
            $table->index('plugin_id');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_orders');
    }
};
