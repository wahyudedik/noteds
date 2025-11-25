<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('activity_id');
            $table->uuid('user_id');
            $table->uuid('parent_id')->nullable(); // For nested replies
            $table->text('content');
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('activities')) {
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
            }
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
            $table->index('activity_id');
            $table->index('user_id');
            $table->index('parent_id');
        });

        // Add self-referencing foreign key after table is created
        // This is safe because the table is already created above
        try {
            Schema::table('activity_comments', function (Blueprint $table) {
                $table->foreign('parent_id')->references('id')->on('activity_comments')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, ignore
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_comments');
    }
};


