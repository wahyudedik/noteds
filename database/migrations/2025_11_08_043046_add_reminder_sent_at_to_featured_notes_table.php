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
        Schema::table('featured_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('featured_notes', 'reminder_sent_at')) {
                $table->timestamp('reminder_sent_at')->nullable()->after('end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('featured_notes', function (Blueprint $table) {
            if (Schema::hasColumn('featured_notes', 'reminder_sent_at')) {
                $table->dropColumn('reminder_sent_at');
            }
        });
    }
};
