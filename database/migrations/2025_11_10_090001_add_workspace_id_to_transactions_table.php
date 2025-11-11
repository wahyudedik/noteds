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
        Schema::table('transactions', function (Blueprint $table) {
            // Add workspace_id column first (without foreign key)
            $table->uuid('workspace_id')->nullable()->after('note_id');
            
            // Add foreign key constraint
            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            
            // Update indexes - drop old index first (if it exists and not used by foreign key)
            // Note: We can't drop the index if it's used by foreign key, so we'll keep it and add new ones
            // The original index ['buyer_id', 'note_id'] is still useful for note transactions
            $table->index(['buyer_id', 'workspace_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
            $table->dropIndex(['buyer_id', 'workspace_id']);
            $table->dropColumn('workspace_id');
        });
    }
};

