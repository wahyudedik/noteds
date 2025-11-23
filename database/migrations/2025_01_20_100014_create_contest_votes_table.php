<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contest_id');
            $table->uuid('entry_id');
            $table->uuid('user_id');
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');
            $table->foreign('entry_id')->references('id')->on('contest_entries')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['contest_id', 'user_id']); // One vote per user per contest
            $table->index(['entry_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_votes');
    }
};

