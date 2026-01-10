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
        Schema::table('reposts', function (Blueprint $table) {
            $table->boolean('is_quote_repost')->default(false)->after('comment_updated_at');
            $table->text('quote_content')->nullable()->after('is_quote_repost');
            $table->uuid('quote_post_id')->nullable()->after('quote_content');
            $table->enum('display_mode', ['embedded', 'separate'])->default('embedded')->after('quote_post_id');
            
            $table->foreign('quote_post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->index('is_quote_repost');
            $table->index('quote_post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reposts', function (Blueprint $table) {
            $table->dropForeign(['quote_post_id']);
            $table->dropIndex(['is_quote_repost']);
            $table->dropIndex(['quote_post_id']);
            $table->dropColumn(['is_quote_repost', 'quote_content', 'quote_post_id', 'display_mode']);
        });
    }
};
