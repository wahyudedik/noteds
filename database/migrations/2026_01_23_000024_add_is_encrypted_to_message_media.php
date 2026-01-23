<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_media', function (Blueprint $table) {
            if (!Schema::hasColumn('message_media', 'is_encrypted')) {
                $table->boolean('is_encrypted')->default(false)->after('order');
                $table->index('is_encrypted');
            }
        });
    }

    public function down(): void
    {
        Schema::table('message_media', function (Blueprint $table) {
            if (Schema::hasColumn('message_media', 'is_encrypted')) {
                $table->dropIndex(['message_media_is_encrypted_index']);
                $table->dropColumn('is_encrypted');
            }
        });
    }
};
