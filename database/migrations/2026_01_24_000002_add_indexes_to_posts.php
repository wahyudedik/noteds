<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $existing = collect(DB::select("SHOW INDEX FROM `posts`"))->pluck('Key_name');
        Schema::table('posts', function (Blueprint $table) use ($existing) {
            if (! $existing->contains('posts_created_at_index')) {
                $table->index('created_at', 'posts_created_at_index');
            }
            if (! $existing->contains('posts_upvotes_count_index')) {
                $table->index('upvotes_count', 'posts_upvotes_count_index');
            }
            if (! $existing->contains('posts_comments_count_index')) {
                $table->index('comments_count', 'posts_comments_count_index');
            }
            if (! $existing->contains('posts_reposts_count_index')) {
                $table->index('reposts_count', 'posts_reposts_count_index');
            }
        });
    }
    public function down(): void
    {
        $existing = collect(DB::select("SHOW INDEX FROM `posts`"))->pluck('Key_name');
        Schema::table('posts', function (Blueprint $table) use ($existing) {
            if ($existing->contains('posts_created_at_index')) {
                $table->dropIndex('posts_created_at_index');
            }
            if ($existing->contains('posts_upvotes_count_index')) {
                $table->dropIndex('posts_upvotes_count_index');
            }
            if ($existing->contains('posts_comments_count_index')) {
                $table->dropIndex('posts_comments_count_index');
            }
            if ($existing->contains('posts_reposts_count_index')) {
                $table->dropIndex('posts_reposts_count_index');
            }
        });
    }
};
