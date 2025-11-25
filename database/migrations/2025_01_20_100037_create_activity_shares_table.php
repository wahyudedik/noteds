<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_shares', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('activity_id');
            $table->uuid('user_id');
            $table->string('platform')->nullable(); // facebook, twitter, linkedin, copy_link, etc.
            $table->text('message')->nullable(); // Optional message when sharing
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('activities')) {
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            }
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            $table->index('activity_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_shares');
    }
};


