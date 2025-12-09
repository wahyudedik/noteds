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
        Schema::table('service_orders', function (Blueprint $table) {
            // Work submission status tracking
            $table->enum('work_status', [
                'not_submitted',
                'submitted',
                'approved',
                'rejected'
            ])->default('not_submitted')->after('status');

            // Buyer approval tracking
            $table->enum('buyer_approval_status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending')->after('work_status');

            $table->timestamp('buyer_approved_at')->nullable()->after('buyer_approval_status');
            $table->longText('buyer_approval_notes')->nullable()->after('buyer_approved_at');

            // Admin verification tracking
            $table->uuid('admin_verified_by')->nullable()->after('buyer_approval_notes');
            $table->timestamp('admin_verified_at')->nullable()->after('admin_verified_by');
            $table->longText('admin_verification_notes')->nullable()->after('admin_verified_at');

            // Payment release tracking
            $table->enum('release_request_status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending')->after('admin_verification_notes');

            $table->timestamp('release_requested_at')->nullable()->after('release_request_status');

            // Foreign key for admin_verified_by
            $table->foreign('admin_verified_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropForeign(['admin_verified_by']);
            $table->dropColumn([
                'work_status',
                'buyer_approval_status',
                'buyer_approved_at',
                'buyer_approval_notes',
                'admin_verified_by',
                'admin_verified_at',
                'admin_verification_notes',
                'release_request_status',
                'release_requested_at',
            ]);
        });
    }
};
