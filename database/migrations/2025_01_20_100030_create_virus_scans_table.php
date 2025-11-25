<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virus_scans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('file_path'); // Path to scanned file
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('scan_status')->default('pending'); // pending, scanning, clean, infected, error, quarantined
            $table->text('scan_result')->nullable(); // ClamAV scan result
            $table->string('threat_name')->nullable(); // Name of detected threat
            $table->text('threat_details')->nullable(); // Additional threat details
            $table->string('quarantine_path')->nullable(); // Path to quarantined file
            $table->boolean('is_quarantined')->default(false);
            $table->timestamp('quarantined_at')->nullable();
            $table->uuid('scanned_by_user_id')->nullable(); // User who triggered scan
            $table->uuid('note_id')->nullable(); // Related note if applicable
            $table->string('scan_type')->default('realtime'); // realtime, scheduled, manual
            $table->integer('scan_duration_ms')->nullable(); // Scan duration in milliseconds
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamps();

            // Add foreign keys only if tables exist
            if (Schema::hasTable('users')) {
                $table->foreign('scanned_by_user_id')->references('id')->on('users')->onDelete('set null');
            }
            if (Schema::hasTable('notes')) {
                $table->foreign('note_id')->references('id')->on('notes')->onDelete('cascade');
            }
            $table->index('scan_status');
            $table->index('is_quarantined');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virus_scans');
    }
};


