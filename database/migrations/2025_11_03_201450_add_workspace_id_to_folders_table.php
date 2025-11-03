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
        Schema::table('folders', function (Blueprint $table) {
            $table->foreignUuid('workspace_id')->nullable()->after('user_id')->constrained('workspaces')->onDelete('set null');
            $table->index(['user_id', 'workspace_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
            $table->dropIndex(['user_id', 'workspace_id']);
            $table->dropColumn('workspace_id');
        });
    }
};
