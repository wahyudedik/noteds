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
        Schema::table('post_votes', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('vote_type');
            $table->index('reason');
        });

        Schema::table('comment_votes', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('vote_type');
            $table->index('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_votes', function (Blueprint $table) {
            $table->dropIndex(['reason']);
            $table->dropColumn('reason');
        });

        Schema::table('comment_votes', function (Blueprint $table) {
            $table->dropIndex(['reason']);
            $table->dropColumn('reason');
        });
    }
};
