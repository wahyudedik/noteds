<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('privacy', ['public', 'private', 'secret'])->default('public');
            $table->uuid('owner_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('privacy');
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
