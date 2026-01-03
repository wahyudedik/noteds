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
            $table->string('link_url')->nullable()->after('content');
            $table->string('link_preview_title')->nullable()->after('link_url');
            $table->text('link_preview_description')->nullable()->after('link_preview_title');
            $table->string('link_preview_image')->nullable()->after('link_preview_description');
            $table->string('link_preview_site_name')->nullable()->after('link_preview_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'link_url',
                'link_preview_title',
                'link_preview_description',
                'link_preview_image',
                'link_preview_site_name',
            ]);
        });
    }
};
