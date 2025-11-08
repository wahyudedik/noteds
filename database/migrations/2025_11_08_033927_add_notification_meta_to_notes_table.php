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
        Schema::table('notes', function (Blueprint $table) {
            if (!Schema::hasColumn('notes', 'notification_meta')) {
                $table->json('notification_meta')->nullable()->after('preview_percentage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            if (Schema::hasColumn('notes', 'notification_meta')) {
                $table->dropColumn('notification_meta');
            }
        });
    }
};
