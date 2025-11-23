<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_ab_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id')->nullable();
            $table->string('name');
            $table->string('test_type'); // 'subject', 'content', 'send_time'
            $table->json('variants'); // Array of variants with subject/content
            $table->integer('split_percentage')->default(50); // 50/50 split
            $table->string('status')->default('draft'); // draft, running, completed, paused
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->uuid('winner_variant_id')->nullable();
            $table->json('results')->nullable(); // Open rates, click rates per variant
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('email_campaigns')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_ab_tests');
    }
};

