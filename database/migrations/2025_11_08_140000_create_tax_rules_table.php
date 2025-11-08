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
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('country_code', 3)->index();
            $table->string('country_name');
            $table->string('note_category')->nullable()->index();
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->boolean('is_inclusive')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['country_code', 'note_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rules');
    }
};

