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
        Schema::create('business_supplier_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_type'); // FK ke business_supplier_categories
            $table->string('supplier_category'); // 'spare_part', 'ban', 'sticker', 'beras', 'susu'
            $table->string('category_label'); // 'Spare Part Motor', 'Beras Premium'
            $table->integer('priority_order')->default(0);
            $table->text('recommendation_note')->nullable(); // "Tempat jualan sparepart terbaik"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('business_type');
            $table->unique(['business_type', 'supplier_category'], 'bsm_business_supplier_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_supplier_mappings');
    }
};
