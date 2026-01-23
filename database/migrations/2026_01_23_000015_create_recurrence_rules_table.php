<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recurrence_rules')) {
            Schema::create('recurrence_rules', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuidMorphs('scheduleable'); // Post, Campaign, ProductReleaseSchedule
                $table->string('timezone')->default('UTC');
                $table->text('rrule')->nullable(); // RFC 5545 string
                $table->enum('freq', ['DAILY','WEEKLY','MONTHLY','YEARLY'])->nullable();
                $table->integer('interval')->default(1);
                $table->json('byday')->nullable(); // e.g., ["MO","WE","FR"]
                $table->json('bymonthday')->nullable(); // e.g., [1,15,30]
                $table->timestamp('dtstart')->nullable();
                $table->timestamp('until')->nullable();
                $table->integer('count')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recurrence_rules');
    }
};
