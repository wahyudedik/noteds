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
        Schema::create('featured_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade'); // Seller
            $table->enum('location', [
                'landing_hero',
                'landing_carousel',
                'marketplace_banner',
                'marketplace_grid',
                'popup_welcome',
                'popup_exit',
                'popup_interstitial'
            ])->default('marketplace_grid');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration_days'); // Berapa hari iklan ditampilkan
            $table->decimal('price', 12, 2); // Total harga iklan
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->integer('clicks')->default(0); // Tracking clicks
            $table->integer('impressions')->default(0); // Tracking views
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'location', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_notes');
    }
};
