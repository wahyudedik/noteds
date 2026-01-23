<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_accessibility_preferences')) {
        Schema::create('user_accessibility_preferences', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->text('data'); // encrypted JSON
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        }
        if (!Schema::hasTable('a11y_usage_logs')) {
        Schema::create('a11y_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable();
            $table->string('feature');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        }
        if (!Schema::hasTable('a11y_reports')) {
        Schema::create('a11y_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->nullable();
            $table->string('context')->nullable(); // page or component
            $table->json('report')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('a11y_reports');
        Schema::dropIfExists('a11y_usage_logs');
        Schema::dropIfExists('user_accessibility_preferences');
    }
};
