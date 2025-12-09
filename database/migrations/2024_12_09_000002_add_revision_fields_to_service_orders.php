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
            // Revision tracking fields
            $table->integer('revision_count')->default(0)->after('admin_verification_notes'); // Total revisions requested
            $table->integer('current_revision_number')->default(0)->after('revision_count'); // Current revision being worked on
            $table->integer('max_revisions')->default(3)->after('current_revision_number'); // Max allowed revisions (set by vendor when creating order)
            $table->enum('revision_status', ['none', 'requested', 'submitted', 'pending_approval'])->default('none')->after('max_revisions'); // Current revision status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn([
                'revision_count',
                'current_revision_number',
                'max_revisions',
                'revision_status'
            ]);
        });
    }
};
