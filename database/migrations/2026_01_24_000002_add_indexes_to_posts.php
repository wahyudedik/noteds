<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $indexes = [
            'created_at' => 'posts_created_at_index',
            'upvotes_count' => 'posts_upvotes_count_index',
            'comments_count' => 'posts_comments_count_index',
            'reposts_count' => 'posts_reposts_count_index',
        ];
        foreach ($indexes as $column => $indexName) {
            if (!$this->indexExists('posts', $indexName)) {
                Schema::table('posts', function (Blueprint $table) use ($column, $indexName) {
                    $table->index($column, $indexName);
                });
            }
        }
    }
    public function down(): void
    {
        $names = [
            'posts_created_at_index',
            'posts_upvotes_count_index',
            'posts_comments_count_index',
            'posts_reposts_count_index',
        ];
        foreach ($names as $indexName) {
            if ($this->indexExists('posts', $indexName)) {
                Schema::table('posts', function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        }
    }
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection()->getName();
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            $result = DB::select('SHOW INDEX FROM ' . DB::getTablePrefix() . $table . ' WHERE Key_name = ?', [$indexName]);
            return !empty($result);
        }
        if ($driver === 'pgsql') {
            $result = DB::select('SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?', [$table, $indexName]);
            return !empty($result);
        }
        if ($driver === 'sqlite') {
            $result = DB::select('PRAGMA index_list(' . $table . ')');
            foreach ($result as $row) {
                if (($row->name ?? $row['name'] ?? null) === $indexName) {
                    return true;
                }
            }
            return false;
        }
        // Fallback: attempt drop to check existence (not ideal). Assume false.
        return false;
    }
};
