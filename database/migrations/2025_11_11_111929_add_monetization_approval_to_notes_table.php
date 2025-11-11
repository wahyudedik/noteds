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
            $table->boolean('monetization_approved')->default(false)->after('price');
            $table->boolean('monetization_auto_approved')->default(false)->after('monetization_approved');
            $table->foreignUuid('monetization_approved_by')->nullable()->after('monetization_auto_approved');
            $table->timestamp('monetization_approved_at')->nullable()->after('monetization_approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn([
                'monetization_approved',
                'monetization_auto_approved',
                'monetization_approved_by',
                'monetization_approved_at',
            ]);
        });
    }
};
