<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('caption')->nullable();
            $table->string('media_path');
            $table->enum('media_type', ['image', 'video']);
            $table->timestamp('expires_at')->index();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('reactions_count')->default(0);
            $table->timestamps();
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
