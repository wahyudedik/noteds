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
            if (!Schema::hasColumn('users', 'email_digest_frequency')) {
                $table->enum('email_digest_frequency', ['none', 'daily', 'weekly'])->default('none')->after('email_verified_at');
            }
            if (!Schema::hasColumn('users', 'email_digest_time')) {
                $table->time('email_digest_time')->nullable()->after('email_digest_frequency');
            }
            if (!Schema::hasColumn('users', 'email_digest_timezone')) {
                $table->string('email_digest_timezone')->nullable()->after('email_digest_time');
            }
            if (!Schema::hasColumn('users', 'last_digest_sent_at')) {
                $table->timestamp('last_digest_sent_at')->nullable()->after('email_digest_timezone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_digest_frequency', 'email_digest_time', 'email_digest_timezone', 'last_digest_sent_at']);
        });
    }
};
