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
        Schema::create('note_view_revenue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 10, 2)->default(0.01); // Revenue per view (0.01 rupiah)
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('fingerprint')->nullable(); // Browser fingerprint untuk deteksi bot
            $table->boolean('is_valid')->default(true); // Flag untuk validasi view
            $table->string('validation_status')->default('pending'); // pending, approved, rejected
            $table->text('rejection_reason')->nullable();
            $table->json('bot_detection_data')->nullable(); // Data untuk analisis bot
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            // Indexes
            $table->index(['note_id', 'viewed_at']);
            $table->index(['user_id', 'viewed_at']);
            $table->index('ip_address');
            $table->index('fingerprint');
            $table->index('is_valid');
            $table->index('validation_status');
            
            // Prevent duplicate views from same IP/fingerprint within short time
            $table->unique(['note_id', 'ip_address', 'fingerprint', 'viewed_at'], 'unique_view_per_ip_fingerprint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_view_revenue');
    }
};
