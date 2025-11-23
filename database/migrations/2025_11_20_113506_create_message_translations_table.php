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
        Schema::create('message_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->string('target_language', 10)->comment('Target language code (en, id, ar)');
            $table->text('translated_message')->comment('Translated message content');
            $table->string('provider', 50)->nullable()->comment('Translation provider used (google, deepl, etc)');
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('note_messages')->cascadeOnDelete();
            
            // One translation per message per language
            $table->unique(['message_id', 'target_language']);
            $table->index('target_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_translations');
    }
};
