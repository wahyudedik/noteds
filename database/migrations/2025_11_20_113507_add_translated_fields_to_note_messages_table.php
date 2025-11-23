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
        Schema::table('note_messages', function (Blueprint $table) {
            $table->string('original_language', 10)->nullable()->after('message')->comment('Original language code of the message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('note_messages', function (Blueprint $table) {
            $table->dropColumn('original_language');
        });
    }
};
