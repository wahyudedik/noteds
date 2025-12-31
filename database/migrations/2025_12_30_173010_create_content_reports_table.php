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
        Schema::create('content_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // reporter
            $table->string('reportable_type'); // post, comment, user
            $table->uuid('reportable_id');
            $table->enum('reason', ['spam', 'harassment', 'inappropriate', 'copyright', 'fake', 'other']);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'reviewing', 'resolved', 'dismissed'])->default('pending');
            $table->uuid('admin_id')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['reportable_type', 'reportable_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_reports');
    }
};
