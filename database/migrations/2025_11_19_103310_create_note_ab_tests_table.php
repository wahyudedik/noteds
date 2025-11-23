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
        Schema::create('note_ab_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('note_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            
            // A/B Test Configuration
            $table->string('test_type')->default('title'); // title, description, thumbnail, price
            $table->string('variant_a')->nullable(); // Original value
            $table->string('variant_b')->nullable(); // Test value
            $table->text('variant_a_description')->nullable(); // For description tests
            $table->text('variant_b_description')->nullable(); // For description tests
            
            // Test Status
            $table->enum('status', ['active', 'paused', 'completed', 'archived'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            
            // Results
            $table->integer('variant_a_views')->default(0);
            $table->integer('variant_b_views')->default(0);
            $table->integer('variant_a_purchases')->default(0);
            $table->integer('variant_b_purchases')->default(0);
            $table->decimal('variant_a_revenue', 12, 2)->default(0);
            $table->decimal('variant_b_revenue', 12, 2)->default(0);
            
            // Statistics
            $table->decimal('variant_a_conversion_rate', 5, 2)->nullable();
            $table->decimal('variant_b_conversion_rate', 5, 2)->nullable();
            $table->string('winning_variant')->nullable(); // a, b, or null (inconclusive)
            $table->decimal('confidence_level', 5, 2)->nullable(); // Statistical confidence (0-100)
            
            $table->timestamps();
            
            // Indexes
            $table->index(['note_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
        
        // Create table untuk tracking user assignment ke variant
        Schema::create('note_ab_test_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ab_test_id')->constrained('note_ab_tests')->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('session_id')->nullable();
            $table->string('variant')->default('a'); // a or b
            $table->boolean('viewed')->default(false);
            $table->boolean('purchased')->default(false);
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['ab_test_id', 'variant']);
            $table->index(['user_id', 'ab_test_id']);
            $table->index('session_id');
            
            // Prevent duplicate assignments per user/session
            $table->unique(['ab_test_id', 'user_id'], 'unique_user_assignment');
            $table->unique(['ab_test_id', 'session_id'], 'unique_session_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('note_ab_test_assignments');
        Schema::dropIfExists('note_ab_tests');
    }
};
