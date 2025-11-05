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
        Schema::table('workspaces', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->after('is_active');
            $table->boolean('is_for_sale')->default(false)->after('price');
            $table->timestamp('sold_at')->nullable()->after('is_for_sale');
            $table->foreignUuid('sold_to_user_id')->nullable()->after('sold_at')->constrained('users')->onDelete('set null');
            $table->text('marketplace_description')->nullable()->after('sold_to_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropForeign(['sold_to_user_id']);
            $table->dropColumn([
                'price',
                'is_for_sale',
                'sold_at',
                'sold_to_user_id',
                'marketplace_description',
            ]);
        });
    }
};
