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
        $afterColumn = Schema::hasColumn('users', 'locale') ? 'locale' : 'timezone';

        Schema::table('users', function (Blueprint $table) use ($afterColumn) {
            $table->json('forum_email_preferences')->nullable()->after($afterColumn);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('forum_email_preferences');
        });
    }
};
