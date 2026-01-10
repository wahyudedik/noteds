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
        Schema::table('posts', function (Blueprint $table) {
            $table->decimal('weighted_upvotes_score', 10, 2)->default(0)->after('downvotes_count');
            $table->decimal('weighted_downvotes_score', 10, 2)->default(0)->after('weighted_upvotes_score');
            $table->index('weighted_upvotes_score');
            $table->index('weighted_downvotes_score');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->decimal('weighted_upvotes_score', 10, 2)->default(0)->after('downvotes_count');
            $table->decimal('weighted_downvotes_score', 10, 2)->default(0)->after('weighted_upvotes_score');
            $table->index('weighted_upvotes_score');
            $table->index('weighted_downvotes_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['weighted_upvotes_score']);
            $table->dropIndex(['weighted_downvotes_score']);
            $table->dropColumn(['weighted_upvotes_score', 'weighted_downvotes_score']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['weighted_upvotes_score']);
            $table->dropIndex(['weighted_downvotes_score']);
            $table->dropColumn(['weighted_upvotes_score', 'weighted_downvotes_score']);
        });
    }
};
