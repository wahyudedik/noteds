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
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('service_order_id')->index();
            $table->uuid('approver_id')->index();
            $table->enum('approver_type', ['buyer', 'admin'])->default('buyer');
            $table->enum('action', [
                'work_submitted',
                'work_approved',
                'work_rejected',
                'payment_released',
                'payment_rejected',
                'refund_issued'
            ]);
            $table->text('notes')->nullable();
            $table->timestamp('action_at')->useCurrent();
            $table->timestamps();

            // Foreign keys
            $table->foreign('service_order_id')
                ->references('id')
                ->on('service_orders')
                ->onDelete('cascade');
            $table->foreign('approver_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};
