<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable();
            $table->string('action'); // login, logout, password_change, etc.
            $table->string('description');
            $table->json('data')->nullable(); // IP, user_agent, changes, etc.
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Indexes untuk query efficiency
            $table->index('action');
            $table->index('created_at');
            $table->index(['user_id', 'action']);
            $table->index(['action', 'created_at']);
        });

        // SQLite doesn't support fullText on multiple columns - skip for now
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->fullText(['action', 'description']); // For full-text search
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
