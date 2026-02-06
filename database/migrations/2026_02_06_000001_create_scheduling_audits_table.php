<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('scheduling_audits')) {
            return;
        }

        Schema::create('scheduling_audits', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('scheduleable');
            $table->uuid('user_id')->nullable();
            $table->string('action');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['scheduleable_type', 'scheduleable_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduling_audits');
    }
};
