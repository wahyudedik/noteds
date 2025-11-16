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
        Schema::table('notes', function (Blueprint $table) {
            $table->boolean('is_draft')->default(false)->after('status');
            $table->timestamp('scheduled_at')->nullable()->after('is_draft');
            $table->timestamp('published_at')->nullable()->after('scheduled_at');
            
            $table->index('is_draft');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn(['is_draft', 'scheduled_at', 'published_at']);
        });
    }
};
