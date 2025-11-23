<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_winners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('contest_id');
            $table->uuid('entry_id');
            $table->uuid('user_id');
            $table->integer('position'); // 1 = first place, 2 = second, etc.
            $table->json('prizes_awarded')->nullable(); // Track what prizes were given
            $table->boolean('prizes_distributed')->default(false);
            $table->timestamp('prizes_distributed_at')->nullable();
            $table->timestamps();

            $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');
            $table->foreign('entry_id')->references('id')->on('contest_entries')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['contest_id', 'position']); // One winner per position
            $table->index(['contest_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_winners');
    }
};

