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
        // Check if notifications table exists
        if (!Schema::hasTable('notifications')) {
            // Create notifications table with UUID support
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->string('notifiable_type');
                $table->string('notifiable_id'); // Use string for UUID support
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                
                $table->index(['notifiable_type', 'notifiable_id']);
            });
        } else {
            // Table exists, modify the column
            Schema::table('notifications', function (Blueprint $table) {
                // Change notifiable_id from bigint to string to support UUID
                $table->string('notifiable_id')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                // Revert back to bigint (if needed)
                // Note: This might fail if there are UUID values in the column
                $table->unsignedBigInteger('notifiable_id')->change();
            });
        }
    }
};
