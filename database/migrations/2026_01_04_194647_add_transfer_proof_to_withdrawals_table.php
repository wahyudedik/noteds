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
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->json('transfer_proof_approve')->nullable()->after('admin_notes');
            $table->json('transfer_proof_complete')->nullable()->after('transfer_proof_approve');
            $table->timestamp('transfer_proof_approve_uploaded_at')->nullable()->after('transfer_proof_complete');
            $table->timestamp('transfer_proof_complete_uploaded_at')->nullable()->after('transfer_proof_approve_uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn([
                'transfer_proof_approve',
                'transfer_proof_complete',
                'transfer_proof_approve_uploaded_at',
                'transfer_proof_complete_uploaded_at',
            ]);
        });
    }
};
