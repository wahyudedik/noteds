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
        Schema::create('work_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_id');
            $table->integer('revision_number')->default(1); // Revision 1, 2, 3, etc
            $table->uuid('requested_by'); // Who requested the revision (buyer_id or vendor_id)
            $table->text('request_reason')->nullable(); // Why revision is requested
            $table->enum('status', ['pending', 'submitted', 'accepted', 'rejected'])->default('pending');
            $table->text('submission_notes')->nullable(); // Notes from vendor when submitting revision
            $table->timestamp('submitted_at')->nullable();
            $table->uuid('submitted_by')->nullable(); // Who submitted the revision (vendor_id)
            $table->text('rejection_reason')->nullable(); // If rejected, why
            $table->timestamp('rejected_at')->nullable();
            $table->uuid('rejected_by')->nullable();
            $table->timestamps();

            $table->foreign('service_order_id')->references('id')->on('service_orders')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');

            $table->index('service_order_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_revisions');
    }
};
