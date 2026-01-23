<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('conversation_keys')) {
            Schema::create('conversation_keys', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('conversation_id')->constrained('conversations')->cascadeOnDelete();
                $table->unsignedInteger('version')->default(1);
                $table->string('algorithm')->default('AES-GCM');
                $table->text('encrypted_key');
                $table->timestamp('rotated_at')->nullable();
                $table->timestamps();
                $table->unique(['conversation_id', 'version'], 'conv_keys_unique');
            });
        }

        if (!Schema::hasTable('conversation_key_access_logs')) {
            Schema::create('conversation_key_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // fetch | rotate
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['conversation_id', 'user_id', 'action'], 'conv_key_logs_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_key_access_logs');
        Schema::dropIfExists('conversation_keys');
    }
};
