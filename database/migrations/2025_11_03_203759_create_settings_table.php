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
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique(); // e.g., 's3_enabled', 's3_bucket', etc.
            $table->text('value')->nullable(); // JSON or text value
            $table->string('type')->default('string'); // 'string', 'boolean', 'json', 'number'
            $table->string('group')->default('general'); // 'general', 's3', 'email', etc.
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
