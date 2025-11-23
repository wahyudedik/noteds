<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // 'sequence', 'abandoned_cart', 'new_note', 'weekly_digest'
            $table->text('subject');
            $table->longText('content');
            $table->json('settings')->nullable(); // Additional settings per campaign type
            $table->boolean('is_active')->default(true);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};

