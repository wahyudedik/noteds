<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_event_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->uuid('user_id');
            $table->enum('rsvp_status', ['accepted', 'declined', 'maybe'])->default('maybe');
            $table->timestamps();
            $table->foreign('event_id')->references('id')->on('group_events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['event_id', 'user_id']);
            $table->index('rsvp_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_event_participants');
    }
};
