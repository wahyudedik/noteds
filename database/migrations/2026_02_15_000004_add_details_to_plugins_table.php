<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->enum('category', ['web', 'desktop', 'mobile'])->default('web')->after('type');
            $table->json('screenshots')->nullable()->after('thumbnail_url'); // Array of URLs
            $table->text('system_requirements')->nullable()->after('description');
            $table->string('file_path')->nullable()->after('storage_path'); // Path to downloadable file
            $table->string('file_size')->nullable()->after('file_path');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->after('enabled');
        });
    }

    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropColumn(['category', 'screenshots', 'system_requirements', 'file_path', 'file_size', 'status']);
        });
    }
};
