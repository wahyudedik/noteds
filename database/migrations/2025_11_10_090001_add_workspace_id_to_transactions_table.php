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
            $table->foreignUuid('workspace_id')->nullable()->after('note_id')->constrained('workspaces')->onDelete('cascade');
            
            // Update index to include workspace_id
            $table->dropIndex(['buyer_id', 'note_id']);
            $table->index(['buyer_id', 'note_id']);
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

