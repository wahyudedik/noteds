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
            $table->text('preview_content')->nullable()->after('content'); // 300 chars preview
            $table->json('attachments')->nullable()->after('preview_content'); // Array of file paths
            $table->integer('file_count')->default(0)->after('attachments'); // Number of attached files
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['preview_content', 'attachments', 'file_count']);
        });
    }
};
