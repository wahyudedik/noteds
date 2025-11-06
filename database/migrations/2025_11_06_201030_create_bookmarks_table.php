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
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable()->comment('Custom title for bookmark');
            $table->text('note_text')->nullable()->comment('User notes about this bookmark');
            $table->string('section_id')->nullable()->comment('ID of section/bookmarked area');
            $table->text('section_text')->nullable()->comment('Text content of bookmarked section');
            $table->integer('position')->default(0)->comment('Position in note (character or line number)');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index('note_id');
            $table->index(['user_id', 'note_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
