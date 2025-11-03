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
        Schema::create('workspaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['personal', 'team', 'organization'])->default('personal');
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['owner_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
