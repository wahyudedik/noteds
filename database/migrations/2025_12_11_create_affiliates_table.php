<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');

            // Affiliate info
            $table->string('code')->unique()->index(); // Unique affiliate code
            $table->decimal('commission_rate', 5, 2)->default(10.00); // %

            // Statistics
            $table->integer('total_clicks')->default(0);
            $table->integer('total_conversions')->default(0);
            $table->decimal('total_earned', 15, 2)->default(0);

            // Status
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
