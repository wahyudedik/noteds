<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('type'); // 'monthly', 'themed', 'custom'
            $table->string('theme')->nullable(); // Theme untuk themed contests
            $table->string('status')->default('draft'); // draft, open, voting, closed, winners_announced
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('voting_start_date')->nullable();
            $table->timestamp('voting_end_date')->nullable();
            $table->integer('max_entries_per_user')->default(1);
            $table->json('prizes')->nullable(); // [{position: 1, type: 'cash', value: 100}, {type: 'badge', badge_id: '...'}]
            $table->json('rules')->nullable(); // Contest rules
            $table->string('banner_image')->nullable();
            $table->uuid('created_by')->nullable(); // Admin yang membuat contest
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};

