<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignUuid('assigned_user_id')->nullable()->after('user_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn('assigned_user_id');
        });
    }
};


