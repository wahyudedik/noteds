<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['conversation_id', 'created_at'], 'messages_conv_created_idx');
            $table->index(['user_id', 'created_at'], 'messages_user_created_idx');
            $table->index('type', 'messages_type_idx');
        });
        Schema::table('read_receipts', function (Blueprint $table) {
            $table->index(['message_id', 'user_id'], 'receipts_message_user_idx');
            $table->index('created_at', 'receipts_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_conv_created_idx');
            $table->dropIndex('messages_user_created_idx');
            $table->dropIndex('messages_type_idx');
        });
        Schema::table('read_receipts', function (Blueprint $table) {
            $table->dropIndex('receipts_message_user_idx');
            $table->dropIndex('receipts_created_idx');
        });
    }
};
