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
        Schema::create('supplier_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('supplier_id');
            $table->uuid('user_id');
            $table->uuid('post_id')->nullable(); // Link ke post yang request supplier
            $table->integer('rating'); // 1-5
            $table->text('review');
            $table->json('tags')->nullable(); // ['harga_murah', 'kualitas_bagus', 'pelayanan_cepat']
            $table->boolean('is_verified_purchase')->default(false);
            $table->timestamps();
            
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('set null');
            $table->unique(['supplier_id', 'user_id', 'post_id']); // One review per user per supplier per post
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_reviews');
    }
};
