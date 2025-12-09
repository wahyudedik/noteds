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
        Schema::create('user_landing_pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });

        // Create pivot table for affiliate links to landing pages
        Schema::create('affiliate_link_user_landing_page', function (Blueprint $table) {
            $table->uuid('affiliate_link_id');
            $table->uuid('user_landing_page_id');
            $table->timestamps();

            $table->foreign('affiliate_link_id')->references('id')->on('affiliate_links')->onDelete('cascade');
            $table->foreign('user_landing_page_id')->references('id')->on('user_landing_pages')->onDelete('cascade');
            $table->primary(['affiliate_link_id', 'user_landing_page_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_link_user_landing_page');
        Schema::dropIfExists('user_landing_pages');
    }
};
