<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('agreement_accepted_at')->nullable()->after('remember_token');
            $table->string('agreement_version')->nullable()->after('agreement_accepted_at');
            $table->string('ktp_path')->nullable()->after('agreement_version');
            $table->string('selfie_path')->nullable()->after('ktp_path');
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending')->after('selfie_path');
            $table->timestamp('verification_reviewed_at')->nullable()->after('verification_status');
            $table->foreignUuid('verification_reviewed_by')->nullable()->after('verification_reviewed_at');
            $table->text('verification_notes')->nullable()->after('verification_reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'agreement_accepted_at',
                'agreement_version',
                'ktp_path',
                'selfie_path',
                'verification_status',
                'verification_reviewed_at',
                'verification_reviewed_by',
                'verification_notes',
            ]);
        });
    }
};


