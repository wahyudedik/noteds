<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->after('type');
            $table->string('demo_url')->nullable()->after('description');
            $table->string('thumbnail_url')->nullable()->after('demo_url');
            $table->boolean('is_paid')->default(false)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('plugins', function (Blueprint $table) {
            $table->dropColumn(['price', 'demo_url', 'thumbnail_url', 'is_paid']);
        });
    }
};
