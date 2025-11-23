<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_certifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('certification_id');
            $table->string('status')->default('pending'); // pending, approved, rejected, expired
            $table->text('application_notes')->nullable(); // Notes dari user saat apply
            $table->text('admin_notes')->nullable(); // Notes dari admin saat approve/reject
            $table->uuid('approved_by')->nullable(); // Admin yang approve
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Optional expiration
            $table->json('evidence')->nullable(); // Evidence/portfolio links
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('certification_id')->references('id')->on('certifications')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'certification_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_certifications');
    }
};

