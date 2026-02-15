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
            if (Schema::hasColumn('posts', 'campaign_id')) {
                $table->uuid('series_id')->nullable()->after('campaign_id');
            } else {
                $table->uuid('series_id')->nullable();
            }
            $table->unsignedInteger('series_order')->nullable()->after('series_id');
            $table->boolean('is_series_root')->default(false)->after('series_order');
            
            $table->foreign('series_id')->references('id')->on('posts')->onDelete('set null');
            $table->index('series_id');
            $table->index('is_series_root');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropColumn(['series_id', 'series_order', 'is_series_root']);
        });
    }
};
