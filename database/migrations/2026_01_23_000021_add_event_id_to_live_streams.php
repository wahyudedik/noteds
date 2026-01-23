<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_streams', function (Blueprint $table) {
            if (!Schema::hasColumn('live_streams', 'event_id')) {
                $table->foreignUuid('event_id')->nullable()->constrained('group_events')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_streams', function (Blueprint $table) {
            if (Schema::hasColumn('live_streams', 'event_id')) {
                $table->dropForeign(['event_id']);
                $table->dropColumn('event_id');
            }
        });
    }
};
