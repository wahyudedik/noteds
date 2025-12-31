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
        // Add indexes to notifications table if they don't exist
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                // Check if index doesn't exist before adding
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes('notifications');
                
                if (!isset($indexesFound['notifications_notifiable_type_notifiable_id_index'])) {
                    $table->index(['notifiable_type', 'notifiable_id']);
                }
                if (!isset($indexesFound['notifications_read_at_index'])) {
                    $table->index('read_at');
                }
                if (!isset($indexesFound['notifications_created_at_index'])) {
                    $table->index('created_at');
                }
            });
        }

        // Additional indexes for better query performance
        Schema::table('top_ups', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('midtrans_order_id');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index(['admin_id', 'created_at']);
        });

        Schema::table('clip_view_tracking', function (Blueprint $table) {
            $table->index('is_valid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropIndex(['notifiable_type', 'notifiable_id']);
                $table->dropIndex(['read_at']);
                $table->dropIndex(['created_at']);
            });
        }

        Schema::table('top_ups', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['midtrans_order_id']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['admin_id', 'created_at']);
        });

        Schema::table('clip_view_tracking', function (Blueprint $table) {
            $table->dropIndex(['is_valid']);
        });
    }
};

