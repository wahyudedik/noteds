<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_virtual')->default(false);
            $table->string('meeting_url')->nullable();
            $table->string('privacy')->default('public'); // public/private
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('share_token')->unique();
            $table->string('status')->default('scheduled'); // scheduled/cancelled/completed
            $table->unsignedInteger('max_attendees')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'start_at']);
        });

        Schema::create('event_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->uuid('user_id');
            $table->string('status')->default('pending'); // pending/accepted/declined/maybe
            $table->dateTime('responded_at')->nullable();
            $table->json('channels')->nullable(); // ['email','app','sms']
            $table->timestamps();
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['event_id', 'user_id']);
            $table->index(['event_id', 'status']);
        });

        Schema::create('event_reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->unsignedInteger('minutes_before');
            $table->string('channel')->default('app'); // app/email/sms
            $table->timestamps();
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->index(['event_id', 'minutes_before']);
        });

        Schema::create('event_categories', function (Blueprint $table) {
            $table->uuid('event_id');
            $table->uuid('category_id');
            $table->primary(['event_id', 'category_id']);
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_categories');
        Schema::dropIfExists('event_reminders');
        Schema::dropIfExists('event_invitations');
        Schema::dropIfExists('events');
    }
};
