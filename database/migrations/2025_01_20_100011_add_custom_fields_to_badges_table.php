<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->boolean('is_custom')->default(false)->after('is_active');
            $table->uuid('created_by')->nullable()->after('is_custom'); // Admin yang membuat custom badge
            $table->json('custom_criteria')->nullable()->after('created_by'); // Custom criteria untuk custom badges
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropColumn(['is_custom', 'created_by', 'custom_criteria']);
        });
    }
};

