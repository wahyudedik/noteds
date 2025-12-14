<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('User who owns this token');

            $table->string('name')->comment('Human-readable token name/description');
            $table->string('token', 64)->unique()->index()
                ->comment('SHA256 hashed API token for authentication');

            // Scope-based access control
            $table->json('scopes')->nullable()
                ->comment('JSON array of allowed scopes (e.g., ["notes.read", "notes.write"])');

            // Token usage tracking
            $table->dateTime('last_used_at')->nullable()
                ->comment('Timestamp of last API request using this token');

            // Token lifecycle management
            $table->dateTime('expires_at')->nullable()
                ->comment('Token expiration date - null means no expiration');
            $table->boolean('revoked')->default(false)->index()
                ->comment('Whether token has been manually revoked');

            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['user_id', 'revoked']);
            $table->index(['revoked', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
