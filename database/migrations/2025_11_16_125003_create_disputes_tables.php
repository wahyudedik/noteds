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
        Schema::create('service_order_disputes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('service_order_id');
            $table->uuid('initiated_by'); // buyer_id or vendor_id
            $table->text('reason');
            $table->enum('status', ['open', 'under_review', 'resolved', 'escalated'])->default('open');
            $table->text('resolution')->nullable(); // Description of how dispute was resolved
            $table->enum('resolution_type', ['refund_buyer', 'payment_vendor', 'partial_refund', 'custom'])->nullable();
            $table->uuid('resolved_by')->nullable(); // Admin who resolved it
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('service_order_id')->references('id')->on('service_orders')->onDelete('cascade');
            $table->foreign('initiated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');

            $table->index('service_order_id');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('dispute_evidence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('dispute_id');
            $table->uuid('submitted_by');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('dispute_id')->references('id')->on('service_order_disputes')->onDelete('cascade');
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');

            $table->index('dispute_id');
            $table->index('submitted_by');
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->uuid('active_dispute_id')->nullable()->after('revision_status');
            $table->foreign('active_dispute_id')->references('id')->on('service_order_disputes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['active_dispute_id']);
            $table->dropColumn('active_dispute_id');
        });

        Schema::dropIfExists('dispute_evidence');
        Schema::dropIfExists('service_order_disputes');
    }
};
