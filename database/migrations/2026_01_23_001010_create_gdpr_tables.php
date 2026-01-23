<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privacy_consents', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->index();
            $table->string('policy_version');
            $table->json('cookie_categories')->nullable();
            $table->timestamp('consented_at');
            $table->timestamps();
        });

        Schema::create('gdpr_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id')->index();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gdpr_requests');
        Schema::dropIfExists('privacy_consents');
    }
};
