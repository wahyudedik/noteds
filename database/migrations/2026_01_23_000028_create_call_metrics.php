<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('call_session_id')->constrained('call_sessions')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->decimal('packet_loss_pct', 5, 2)->nullable();
            $table->unsignedInteger('jitter_ms')->nullable();
            $table->timestamps();
            $table->index(['call_session_id', 'user_id'], 'call_metrics_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_metrics');
    }
};
