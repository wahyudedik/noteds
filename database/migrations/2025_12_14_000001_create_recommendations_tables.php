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
        // Recommendations tracking table
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->string('algorithm')->index(); // collaborative, content_based, trending, profile_based
            $table->decimal('score', 8, 4)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
            $table->index(['note_id', 'score']);
        });

        // Recommendation impressions (when shown to user)
        Schema::create('recommendation_impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->string('context'); // homepage, marketplace, similar_notes, etc
            $table->string('algorithm')->nullable();
            $table->integer('position')->default(0);
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
            $table->index(['note_id', 'created_at']);
        });

        // Recommendation clicks (when user clicks on recommendation)
        Schema::create('recommendation_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('impression_id')->nullable()->constrained('recommendation_impressions')->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->string('context');
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
            $table->index(['note_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendation_clicks');
        Schema::dropIfExists('recommendation_impressions');
        Schema::dropIfExists('recommendations');
    }
};
