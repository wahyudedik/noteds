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
        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('sector')->nullable();
            $table->string('sub_sector')->nullable();
            $table->date('listing_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('market_cap')->nullable();
            $table->enum('category', ['LQ45', 'IDX30', 'IDX80', 'Kompas100', 'others'])->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('sector');
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
