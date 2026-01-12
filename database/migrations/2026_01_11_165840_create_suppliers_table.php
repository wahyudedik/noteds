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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // Seller yang terdaftar sebagai supplier
            $table->string('supplier_name');
            $table->string('supplier_category'); // 'spare_part', 'ban', 'sticker'
            $table->text('description');
            $table->string('location')->nullable(); // Kota/kabupaten
            $table->json('contact_info'); // {phone, email, whatsapp, address}
            $table->json('specialties')->nullable(); // ['harga_murah', 'kualitas_premium', 'ready_stock']
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->string('delivery_scope')->nullable(); // 'lokal', 'nasional', 'internasional'
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            $table->integer('order_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('supplier_category');
            $table->index('location');
            $table->index(['is_active', 'is_verified', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
