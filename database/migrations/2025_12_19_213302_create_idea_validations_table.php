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
        Schema::create('idea_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('validation_status', ['layak', 'tidak_layak']);
            $table->decimal('estimated_capital', 15, 2)->nullable();
            $table->decimal('estimated_bep', 15, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->json('risks')->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idea_validations');
    }
};
