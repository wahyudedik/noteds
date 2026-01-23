<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('badge_icon')->nullable();
            $table->text('description')->nullable();
            $table->json('criteria')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('verification_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('type_id');
            $table->string('status')->default('pending'); // pending|approved|rejected
            $table->json('data')->nullable(); // form fields
            $table->json('documents')->nullable(); // array of file paths or metadata
            $table->uuid('reviewer_id')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('verification_types')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['user_id','type_id','status']);
        });

        Schema::create('user_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('type_id');
            $table->timestamp('verified_at');
            $table->timestamps();
            $table->unique(['user_id','type_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('verification_types')->onDelete('cascade');
        });

        Schema::create('verification_audits', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('action'); // submit|approve|reject|update
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['request_id','user_id','action']);
            $table->foreign('request_id')->references('id')->on('verification_requests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_audits');
        Schema::dropIfExists('user_verifications');
        Schema::dropIfExists('verification_requests');
        Schema::dropIfExists('verification_types');
    }
};
