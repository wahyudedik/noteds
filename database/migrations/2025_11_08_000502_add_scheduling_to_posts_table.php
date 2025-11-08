<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('is_hidden');
            $table->dateTime('scheduled_at')->nullable()->after('is_published');
            $table->dateTime('published_at')->nullable()->after('scheduled_at');
        });

        DB::table('posts')->update([
            'is_published' => true,
            'scheduled_at' => null,
            'published_at' => DB::raw('COALESCE(published_at, created_at)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['is_published', 'scheduled_at', 'published_at']);
        });
    }
};
