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
            $table->timestamp('scheduled_at')->nullable()->after('created_at');
            $table->enum('publish_status', ['draft', 'scheduled', 'published'])->default('published')->after('status');
            $table->index('scheduled_at');
            $table->index('publish_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['scheduled_at']);
            $table->dropIndex(['publish_status']);
            $table->dropColumn(['scheduled_at', 'publish_status']);
        });
    }
};
