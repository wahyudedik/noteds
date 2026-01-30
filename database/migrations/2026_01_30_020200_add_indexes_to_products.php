<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'category'], 'products_active_category_idx');
            $table->index('created_at', 'products_created_idx');
            $table->index('price', 'products_price_idx');
            $table->index('user_id', 'products_user_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_category_idx');
            $table->dropIndex('products_created_idx');
            $table->dropIndex('products_price_idx');
            $table->dropIndex('products_user_idx');
        });
    }
};
