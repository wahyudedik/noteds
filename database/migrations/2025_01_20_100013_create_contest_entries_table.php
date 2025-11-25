<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contest_id');
            $table->uuid('user_id');
            $table->uuid('note_id');
            $table->text('submission_notes')->nullable(); // Why this note should win
            $table->integer('vote_count')->default(0);
            $table->string('status')->default('pending'); // pending, approved, rejected, disqualified
            $table->text('rejection_reason')->nullable();
            $table->uuid('reviewed_by')->nullable(); // Admin yang review
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('contests')) {
                $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');
            }
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            }
            if (Schema::hasTable('notes')) {
                $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            }
            $table->unique(['contest_id', 'note_id']); // One note per contest
            $table->index(['contest_id', 'user_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_entries');
    }
};

