<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_template_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        Schema::table('newsletter_templates', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('newsletter_template_categories')->nullOnDelete();
            $table->text('description')->nullable();
        });
        Schema::create('newsletter_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->json('branding')->nullable();
            $table->json('variables')->nullable();
            $table->timestamps();
        });
        Schema::create('newsletter_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->boolean('valid')->default(false);
            $table->string('signature')->nullable();
            $table->text('error')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
        Schema::create('newsletter_provider_status', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->timestamp('last_success_at')->nullable();
            $table->unsignedInteger('failures_count')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_provider_status');
        Schema::dropIfExists('newsletter_webhook_logs');
        Schema::dropIfExists('newsletter_clients');
        Schema::table('newsletter_templates', function (Blueprint $table) {
            $table->dropColumn('category_id');
            $table->dropColumn('description');
        });
        Schema::dropIfExists('newsletter_template_categories');
    }
};
