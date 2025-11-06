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
        Schema::create('purchased_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('purchase_price', 12, 2);
            $table->timestamp('purchased_at');
            $table->integer('download_count')->default(0)->comment('Track download count for basic users (max 5)');
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();

            // Unique constraint: one user can only have one record per note
            $table->unique(['user_id', 'note_id']);
            
            // Indexes
            $table->index('user_id');
            $table->index('note_id');
            $table->index('purchased_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchased_notes');
    }
};
