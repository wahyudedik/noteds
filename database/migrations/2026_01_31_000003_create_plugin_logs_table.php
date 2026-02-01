<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plugin_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('plugin_id');
            $table->string('level')->default('info');
            $table->string('message');
            $table->json('context')->nullable();
            $table->decimal('duration_ms', 10, 3)->nullable();
            $table->timestamps();

            $table->foreign('plugin_id')->references('id')->on('plugins')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plugin_logs');
    }
};

