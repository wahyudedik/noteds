<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_insights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('workspace_id');
            $table->string('type'); // 'weekly_digest', 'anomaly', 'trend', 'recommendation'
            $table->string('category')->nullable(); // 'activity', 'performance', 'collaboration', 'content'
            $table->string('title');
            $table->text('description');
            $table->json('data')->nullable(); // Insight data/metrics
            $table->string('severity')->nullable(); // 'low', 'medium', 'high' (for anomalies)
            $table->boolean('is_read')->default(false);
            $table->uuid('created_for_user_id')->nullable(); // User-specific insight
            $table->timestamp('generated_at');
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('workspaces')) {
                $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            }
            if (Schema::hasTable('users')) {
                $table->foreign('created_for_user_id')->references('id')->on('users')->onDelete('cascade');
            }
            $table->index(['workspace_id', 'type', 'generated_at']);
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_insights');
    }
};

