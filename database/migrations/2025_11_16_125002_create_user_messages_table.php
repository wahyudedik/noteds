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
        Schema::create('user_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sender_id');
            $table->uuid('recipient_id');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');

            $table->index(['sender_id', 'recipient_id']);
            $table->index(['recipient_id', 'read_at']);
            $table->index('created_at');
        });

        Schema::create('message_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->string('file_path');
            $table->string('original_filename');
            $table->integer('file_size'); // in bytes
            $table->string('mime_type')->nullable();
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('user_messages')->onDelete('cascade');

            $table->index('message_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('sent_messages_count')->default(0)->after('bio');
            $table->integer('received_messages_count')->default(0)->after('sent_messages_count');
            $table->integer('unread_messages_count')->default(0)->after('received_messages_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sent_messages_count', 'received_messages_count', 'unread_messages_count']);
        });

        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('user_messages');
    }
};
