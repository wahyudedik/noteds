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
        Schema::create('support_ticket_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade'); // User or admin who responded
            $table->text('message');
            $table->json('attachments')->nullable(); // Array of file paths
            $table->boolean('is_admin_response')->default(false);
            $table->boolean('is_internal_note')->default(false); // Internal notes only visible to admins
            $table->timestamps();
            
            $table->index(['ticket_id', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_ticket_responses');
    }
};
