<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->string('status')->default('pending');
            $table->json('preferences')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->string('confirm_token')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->longText('html');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('template_id')->constrained('newsletter_templates')->cascadeOnDelete();
            $table->json('segment')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('total_target')->default(0);
            $table->timestamps();
        });

        Schema::create('newsletter_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('newsletter_campaigns')->cascadeOnDelete();
            $table->foreignId('subscriber_id')->constrained('newsletter_subscribers')->cascadeOnDelete();
            $table->string('email');
            $table->timestamp('sent_at')->nullable();
            $table->string('delivery_status')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamps();
        });

        Schema::create('newsletter_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('send_id')->constrained('newsletter_sends')->cascadeOnDelete();
            $table->string('type');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('newsletter_suppression_list', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_suppression_list');
        Schema::dropIfExists('newsletter_events');
        Schema::dropIfExists('newsletter_sends');
        Schema::dropIfExists('newsletter_campaigns');
        Schema::dropIfExists('newsletter_templates');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
