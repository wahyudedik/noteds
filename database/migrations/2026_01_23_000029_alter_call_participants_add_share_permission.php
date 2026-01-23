<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('call_participants', function (Blueprint $table) {
            $table->boolean('can_share_screen')->default(true)->after('video_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('call_participants', function (Blueprint $table) {
            $table->dropColumn('can_share_screen');
        });
    }
};
