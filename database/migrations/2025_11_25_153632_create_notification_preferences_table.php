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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->string('notification_type'); // purchase, sale, review, etc.
            $table->boolean('in_app')->default(true); // In-app notifications
            $table->boolean('email')->default(true); // Email notifications
            $table->boolean('push')->default(false); // Push notifications (mobile)
            $table->timestamps();

            // Unique constraint: one preference per user per notification type
            $table->unique(['user_id', 'notification_type']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
