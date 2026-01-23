<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_analytics', function (Blueprint $table) {
            $table->id();
            $table->morphs('shareable'); // shareable_type, shareable_id
            $table->string('platform'); // facebook|twitter|linkedin|whatsapp|telegram|copy_link|web_share
            $table->unsignedBigInteger('count')->default(0);
            $table->timestamp('last_shared_at')->nullable();
            $table->timestamps();
            $table->unique(['shareable_type','shareable_id','platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_analytics');
    }
};
