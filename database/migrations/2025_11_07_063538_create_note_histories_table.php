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
        Schema::create('note_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // 'created', 'updated', 'sold', etc.
            $table->json('old_data')->nullable(); // Store old values before update
            $table->json('new_data')->nullable(); // Store new values after update
            $table->text('changes')->nullable(); // Human-readable description of changes
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps();
            
            $table->index(['note_id', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_histories');
    }
};
