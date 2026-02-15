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
            if (Schema::hasColumn('posts', 'campaign_id')) {
                $table->uuid('original_post_id')->nullable()->after('campaign_id');
            } else {
                $table->uuid('original_post_id')->nullable();
            }
            $table->boolean('is_quote_repost')->default(false)->after('original_post_id');
            
            $table->foreign('original_post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->index('original_post_id');
            $table->index('is_quote_repost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['original_post_id']);
            $table->dropIndex(['original_post_id']);
            $table->dropIndex(['is_quote_repost']);
            $table->dropColumn(['original_post_id', 'is_quote_repost']);
        });
    }
};
