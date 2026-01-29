<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        if ($driver === 'mysql') {
            $database = $connection->getDatabaseName();
            $result = DB::select(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$database, $table, $indexName]
            );
            return !empty($result) && (int)$result[0]->count > 0;
        }
        if ($driver === 'pgsql') {
            $result = DB::select(
                "SELECT COUNT(*) as count FROM pg_indexes 
                 WHERE schemaname = ANY (current_schemas(false)) AND tablename = ? AND indexname = ?",
                [$table, $indexName]
            );
            return !empty($result) && (int)$result[0]->count > 0;
        }
        if ($driver === 'sqlite') {
            $result = DB::select(
                "SELECT COUNT(*) as count FROM sqlite_master 
                 WHERE type = 'index' AND tbl_name = ? AND name = ?",
                [$table, $indexName]
            );
            return !empty($result) && (int)$result[0]->count > 0;
        }
        return false;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            // payment_status is frequently queried alone
            if (!$this->indexExists('orders', 'orders_payment_status_index')) {
                $table->index('payment_status');
            }
            
            // Individual status index for queries filtering by status only
            if (!$this->indexExists('orders', 'orders_status_index')) {
                $table->index('status');
            }
            
            // Individual user_id index (composite exists but individual helps with single-column queries)
            if (!$this->indexExists('orders', 'orders_user_id_index')) {
                $table->index('user_id');
            }
            
            // Individual product_id index
            if (!$this->indexExists('orders', 'orders_product_id_index')) {
                $table->index('product_id');
            }
            
            // Composite index for payment_status + status queries
            if (!$this->indexExists('orders', 'orders_payment_status_status_index')) {
                $table->index(['payment_status', 'status']);
            }
            
            // Index for created_at (frequently used in date range queries)
            if (!$this->indexExists('orders', 'orders_created_at_index')) {
                $table->index('created_at');
            }
        });

        // Transactions table indexes
        Schema::table('transactions', function (Blueprint $table) {
            // reference_id is queried in PaymentController
            if (!$this->indexExists('transactions', 'transactions_reference_id_index')) {
                $table->index('reference_id');
            }
            
            // Individual status index
            if (!$this->indexExists('transactions', 'transactions_status_index')) {
                $table->index('status');
            }
            
            // Individual user_id index (composite exists but individual helps)
            if (!$this->indexExists('transactions', 'transactions_user_id_index')) {
                $table->index('user_id');
            }
            
            // Index for created_at (frequently used in date range queries)
            if (!$this->indexExists('transactions', 'transactions_created_at_index')) {
                $table->index('created_at');
            }
        });

        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            // Individual user_id index (composite exists but individual helps)
            if (!$this->indexExists('products', 'products_user_id_index')) {
                $table->index('user_id');
            }
            
            // Individual is_active index
            if (!$this->indexExists('products', 'products_is_active_index')) {
                $table->index('is_active');
            }
            
            // Index for created_at (frequently used in sorting)
            if (!$this->indexExists('products', 'products_created_at_index')) {
                $table->index('created_at');
            }
        });

        // Clips table - verify key indexes exist (most should already exist, but adding if missing)
        Schema::table('clips', function (Blueprint $table) {
            // Individual campaign_id index (composite exists but individual helps)
            if (!$this->indexExists('clips', 'clips_campaign_id_index')) {
                $table->index('campaign_id');
            }
            
            // Individual clipper_id index (composite exists but individual helps)
            if (!$this->indexExists('clips', 'clips_clipper_id_index')) {
                $table->index('clipper_id');
            }
            
            // Index for created_at
            if (!$this->indexExists('clips', 'clips_created_at_index')) {
                $table->index('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['product_id']);
            $table->dropIndex(['payment_status', 'status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['reference_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('clips', function (Blueprint $table) {
            $table->dropIndex(['campaign_id']);
            $table->dropIndex(['clipper_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
