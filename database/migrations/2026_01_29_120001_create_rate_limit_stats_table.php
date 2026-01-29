<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_limit_stats', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->dateTime('minute_bucket');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();
            $table->unique(['endpoint', 'minute_bucket']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_limit_stats');
    }
};
