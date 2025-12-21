<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('purpose_type', [
                'idea_business',
                'ask_question',
                'share_experience',
                'find_partner',
                'find_tools',
                'validate_idea'
            ]);
            $table->string('title');
            $table->text('content');
            $table->boolean('is_validated_post')->default(false);
            $table->unsignedBigInteger('upvotes_count')->default(0);
            $table->unsignedBigInteger('downvotes_count')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);
            $table->enum('status', ['active', 'moderated', 'archived'])->default('active');
            $table->timestamps();
            $table->index('purpose_type');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
