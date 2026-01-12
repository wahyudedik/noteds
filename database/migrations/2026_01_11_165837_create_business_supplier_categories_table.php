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
        Schema::create('business_supplier_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_type')->unique(); // 'motor_listrik', 'toko_kelontong'
            $table->string('business_name'); // 'Produksi Motor Listrik'
            $table->text('description')->nullable();
            $table->json('keywords')->nullable(); // Keywords untuk auto-detect
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_supplier_categories');
    }
};
