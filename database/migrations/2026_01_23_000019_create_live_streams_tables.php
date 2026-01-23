<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('live_streams')) {
            Schema::create('live_streams', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('status', ['scheduled','live','ended'])->default('scheduled');
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->enum('provider', ['custom_hls','youtube','twitch'])->default('custom_hls');
                $table->string('ingest_url')->nullable();
                $table->string('stream_key')->nullable();
                $table->string('playback_url')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('live_chat_messages')) {
            Schema::create('live_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->text('content');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stream_analytics')) {
            Schema::create('stream_analytics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('live_stream_id')->constrained('live_streams')->onDelete('cascade');
                $table->unsignedBigInteger('views_count')->default(0);
                $table->unsignedBigInteger('chat_count')->default(0);
                $table->unsignedBigInteger('duration_seconds')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stream_analytics');
        Schema::dropIfExists('live_chat_messages');
        Schema::dropIfExists('live_streams');
    }
};
