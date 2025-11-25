<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('note_id');
            $table->uuid('transaction_id')->nullable(); // Optional: triggered by purchase
            $table->uuid('checked_by')->nullable(); // Admin who checked
            $table->string('check_type'); // 'pre_publish', 'post_purchase', 'random', 'complaint'
            $table->string('status')->default('pending'); // pending, passed, failed, needs_review
            $table->json('check_results')->nullable(); // Detailed check results
            $table->text('notes')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('notes')) {
                $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            }
            if (Schema::hasTable('transactions')) {
                $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            }
            if (Schema::hasTable('users')) {
                $table->foreign('checked_by')->references('id')->on('users')->onDelete('set null');
            }
            $table->index(['note_id', 'status']);
            $table->index('check_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_checks');
    }
};

