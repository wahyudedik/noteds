<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->string('language', 10)->nullable()->after('summary')->index();
            $table->timestamp('scheduled_publish_at')->nullable()->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['language', 'scheduled_publish_at']);
        });
    }
};


