<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_invites', function (Blueprint $table) {
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('last_clicked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('group_invites', function (Blueprint $table) {
            $table->dropColumn(['open_count', 'click_count', 'last_opened_at', 'last_clicked_at']);
        });
    }
};
