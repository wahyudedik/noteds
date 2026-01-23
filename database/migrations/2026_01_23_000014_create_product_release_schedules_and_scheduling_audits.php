<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_release_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->timestamp('scheduled_at')->index();
            $table->string('timezone')->default('UTC');
            $table->string('status')->default('scheduled'); // scheduled|published|cancelled
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('scheduling_audits', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('scheduleable'); // posts, campaigns, product_release_schedules
            $table->uuid('user_id')->nullable();
            $table->string('action'); // create|update|delete|publish|notify
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['scheduleable_type', 'scheduleable_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduling_audits');
        Schema::dropIfExists('product_release_schedules');
    }
};
