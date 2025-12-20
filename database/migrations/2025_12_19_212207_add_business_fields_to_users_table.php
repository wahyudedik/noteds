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
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_name')->nullable()->after('name');
            $table->string('business_field')->nullable()->after('business_name');
            $table->json('skills')->nullable()->after('business_field');
            $table->json('goals')->nullable()->after('skills');
            $table->string('portfolio_url')->nullable()->after('goals');
            $table->string('website_url')->nullable()->after('portfolio_url');
            $table->boolean('is_verified_mentor')->default(false)->after('website_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'business_name',
                'business_field',
                'skills',
                'goals',
                'portfolio_url',
                'website_url',
                'is_verified_mentor',
            ]);
        });
    }
};
