<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('search_histories', function (Blueprint $table) {
            $table->boolean('zero_result')->default(false)->index();
            $table->unsignedInteger('duration_ms')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('search_histories', function (Blueprint $table) {
            $table->dropColumn(['zero_result', 'duration_ms']);
        });
    }
};
