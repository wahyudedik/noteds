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
            $table->integer('preview_percentage')->default(0)->after('preview_content')->comment('Percentage of content to show as preview (0-100). 0 = fully locked, 100 = fully visible');
            $table->json('thumbnails')->nullable()->after('preview_percentage')->comment('Array of thumbnail image paths (max 5 images)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['preview_percentage', 'thumbnails']);
        });
    }
};
