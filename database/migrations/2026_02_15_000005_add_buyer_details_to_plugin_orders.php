<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plugin_orders', function (Blueprint $table) {
            $table->string('buyer_name')->nullable()->after('user_id');
            $table->string('buyer_whatsapp')->nullable()->after('buyer_name');
            $table->uuid('bank_id')->nullable()->after('plugin_id');
            
            // Assuming bank_accounts uses UUID as primary key based on previous migrations
            // If bank_accounts hasn't been created yet or uses ID, this foreign key might fail if strict checks are on immediately
            // But usually we define the column first.
            // Let's add foreign key constraint if possible, or just index.
            // Since we just created bank_accounts migration, it should be fine.
        });
    }

    public function down(): void
    {
        Schema::table('plugin_orders', function (Blueprint $table) {
            $table->dropColumn(['buyer_name', 'buyer_whatsapp', 'bank_id']);
        });
    }
};
