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
            $table->text('comment')->nullable()->after('post_id');
            $table->timestamp('comment_updated_at')->nullable()->after('comment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reposts', function (Blueprint $table) {
            $table->dropColumn(['comment', 'comment_updated_at']);
        });
    }
};
