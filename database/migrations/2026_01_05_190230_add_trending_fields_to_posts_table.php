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
        Schema::table('posts', function (Blueprint $table) {
            $table->decimal('trending_score', 10, 4)->default(0)->after('reposts_count');
            $table->timestamp('last_trending_calculated_at')->nullable()->after('trending_score');
            $table->index('trending_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['trending_score', 'last_trending_calculated_at']);
        });
    }
};
