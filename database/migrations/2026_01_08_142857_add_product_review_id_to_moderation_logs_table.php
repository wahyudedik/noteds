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
        Schema::table('moderation_logs', function (Blueprint $table) {
            $table->uuid('product_review_id')->nullable()->after('comment_id');
            $table->foreign('product_review_id')->references('id')->on('product_reviews')->onDelete('cascade');
            $table->index('product_review_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('moderation_logs', function (Blueprint $table) {
            $table->dropForeign(['product_review_id']);
            $table->dropIndex(['product_review_id']);
            $table->dropColumn('product_review_id');
        });
    }
};
