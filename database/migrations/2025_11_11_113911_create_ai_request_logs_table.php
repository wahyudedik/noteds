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
        Schema::create('ai_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'summary', 'tags', 'qa', 'embedding', etc.
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('set null');
            $table->float('duration'); // Duration in seconds
            $table->boolean('success')->default(true);
            $table->text('error')->nullable();
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();

            $table->index('type');
            $table->index('user_id');
            $table->index('success');
            $table->index('created_at');
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_request_logs');
    }
};
