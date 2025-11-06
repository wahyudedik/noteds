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
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->string('analysis_type')->comment('analyzer, qa, comparison, extractor');
            $table->text('summary')->nullable();
            $table->json('key_points')->nullable();
            $table->json('insights')->nullable();
            $table->json('topics')->nullable();
            $table->string('difficulty_level')->nullable()->comment('beginner, intermediate, advanced');
            $table->integer('estimated_time_minutes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional analysis data');
            $table->timestamps();

            $table->index('user_id');
            $table->index('note_id');
            $table->index(['user_id', 'note_id', 'analysis_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
