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
        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('support_ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_admin')->default(false);
            $table->timestamps();

            $table->index('support_ticket_id');
            $table->index('user_id');
            $table->index(['support_ticket_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');
    }
};
