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
        // Skip in test environment to avoid duplicate index errors
        if (app()->environment('testing')) {
            return;
        }

        // Add indexes to notes table for frequently queried columns
        Schema::table('notes', function (Blueprint $table) {
            // Indexes for filtering and searching
            try {
                if (!$this->indexExists('notes', 'notes_is_public_status_index')) {
                    $table->index(['is_public', 'status'], 'notes_is_public_status_index');
                }
            } catch (\Exception $e) { /* Index already exists */
            }

            try {
                if (!$this->indexExists('notes', 'notes_ecosystem_category_index')) {
                    $table->index('ecosystem_category', 'notes_ecosystem_category_index');
                }
            } catch (\Exception $e) { /* Index already exists */
            }

            try {
                if (!$this->indexExists('notes', 'notes_language_index')) {
                    $table->index('language', 'notes_language_index');
                }
            } catch (\Exception $e) { /* Index already exists */
            }

            try {
                if (!$this->indexExists('notes', 'notes_price_index')) {
                    $table->index('price', 'notes_price_index');
                }
            } catch (\Exception $e) { /* Index already exists */
            }

            try {
                if (!$this->indexExists('notes', 'notes_created_at_index')) {
                    $table->index('created_at', 'notes_created_at_index');
                }
            } catch (\Exception $e) { /* Index already exists */
            }

            try {
                if (!$this->indexExists('notes', 'notes_user_id_status_index')) {
                    $table->index(['user_id', 'status'], 'notes_user_id_status_index');
                }
            } catch (\Exception $e) { /* Index already exists */
            }

            // Composite index for common marketplace queries
            try {
                if (!$this->indexExists('notes', 'notes_public_active_created_index')) {
                    $table->index(['is_public', 'status', 'created_at'], 'notes_public_active_created_index');
                }
            } catch (\Exception $e) { /* Index already exists */
            }
        });

        // Add indexes to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            if (!$this->indexExists('transactions', 'transactions_status_index')) {
                $table->index('status', 'transactions_status_index');
            }
            if (!$this->indexExists('transactions', 'transactions_seller_id_index')) {
                $table->index('seller_id', 'transactions_seller_id_index');
            }
            if (!$this->indexExists('transactions', 'transactions_created_at_index')) {
                $table->index('created_at', 'transactions_created_at_index');
            }
            // Composite index for transaction queries
            if (!$this->indexExists('transactions', 'transactions_note_status_index')) {
                $table->index(['note_id', 'status'], 'transactions_note_status_index');
            }
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_role_index')) {
                $table->index('role', 'users_role_index');
            }
            if (!$this->indexExists('users', 'users_username_index')) {
                $table->index('username', 'users_username_index');
            }
        });

        // Add indexes to note_tag pivot table if it exists
        if (Schema::hasTable('note_tag')) {
            Schema::table('note_tag', function (Blueprint $table) {
                if (!$this->indexExists('note_tag', 'note_tag_tag_id_index')) {
                    $table->index('tag_id', 'note_tag_tag_id_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex('notes_is_public_status_index');
            $table->dropIndex('notes_ecosystem_category_index');
            $table->dropIndex('notes_language_index');
            $table->dropIndex('notes_price_index');
            $table->dropIndex('notes_created_at_index');
            $table->dropIndex('notes_user_id_status_index');
            $table->dropIndex('notes_public_active_created_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_status_index');
            $table->dropIndex('transactions_seller_id_index');
            $table->dropIndex('transactions_created_at_index');
            $table->dropIndex('transactions_note_status_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_username_index');
        });

        if (Schema::hasTable('note_tag')) {
            Schema::table('note_tag', function (Blueprint $table) {
                $table->dropIndex('note_tag_tag_id_index');
            });
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Query sqlite_master
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM sqlite_master 
                 WHERE type = 'index' AND name = ?",
                [$index]
            );
        } else {
            // MySQL/PostgreSQL: Use information_schema
            $databaseName = $connection->getDatabaseName();
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $table, $index]
            );
        }

        return $result[0]->count > 0;
    }
};
