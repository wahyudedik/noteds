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
        // Add deduplication tracking columns ke affiliate_fraud_logs
        if (Schema::hasTable('affiliate_fraud_logs')) {
            Schema::table('affiliate_fraud_logs', function (Blueprint $table) {
                // Device fingerprint tracking untuk deduplication
                if (!Schema::hasColumn('affiliate_fraud_logs', 'device_fingerprint')) {
                    $table->string('device_fingerprint')->nullable()->index();
                    $table->comment('SHA-256 hash of IP + User-Agent untuk device identification');
                }

                // Session ID untuk session-based deduplication
                if (!Schema::hasColumn('affiliate_fraud_logs', 'session_id')) {
                    $table->string('session_id')->nullable()->index();
                    $table->comment('Browser session ID untuk prevent session duplicates');
                }

                // Click signature untuk exact duplicate detection
                if (!Schema::hasColumn('affiliate_fraud_logs', 'click_signature')) {
                    $table->string('click_signature')->nullable()->unique();
                    $table->comment('Unique signature for exact duplicate detection');
                }

                // Parent click ID untuk linking duplicate attempts
                if (!Schema::hasColumn('affiliate_fraud_logs', 'parent_click_id')) {
                    $table->uuid('parent_click_id')->nullable();
                    $table->comment('ID of the original click if this is a duplicate');
                }

                // Click source untuk identifying click origin
                if (!Schema::hasColumn('affiliate_fraud_logs', 'click_source')) {
                    $table->enum('click_source', ['landing_page', 'email', 'social', 'direct', 'other'])->default('other');
                    $table->comment('Source of the affiliate click');
                }

                // Deduplication status
                if (!Schema::hasColumn('affiliate_fraud_logs', 'dedup_status')) {
                    $table->enum('dedup_status', ['valid', 'duplicate', 'suspicious', 'unknown'])->default('unknown')->index();
                    $table->comment('Deduplication detection result');
                }

                // Deduplication reason
                if (!Schema::hasColumn('affiliate_fraud_logs', 'dedup_reason')) {
                    $table->string('dedup_reason')->nullable();
                    $table->comment('Reason for deduplication decision');
                }
            });
        }

        // Create new table untuk track click sessions
        // Membantu dengan session-based deduplication
        if (!Schema::hasTable('affiliate_click_sessions')) {
            Schema::create('affiliate_click_sessions', function (Blueprint $table) {
                $table->id();
                $table->uuid('affiliate_id');
                $table->uuid('click_id');
                $table->string('session_id')->index();
                $table->string('device_fingerprint')->index();
                $table->string('ip_address');
                $table->text('user_agent');
                $table->string('referrer')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('clicked_at')->useCurrent();
                $table->timestamps();

                $table->index(['affiliate_id', 'session_id']);
                $table->index(['affiliate_id', 'device_fingerprint']);
                $table->index('created_at');

                // Add foreign key constraint after table creation
                $table->foreign('affiliate_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new table
        if (Schema::hasTable('affiliate_click_sessions')) {
            Schema::dropIfExists('affiliate_click_sessions');
        }

        // Remove columns dari affiliate_fraud_logs
        if (Schema::hasTable('affiliate_fraud_logs')) {
            Schema::table('affiliate_fraud_logs', function (Blueprint $table) {
                if (Schema::hasColumn('affiliate_fraud_logs', 'device_fingerprint')) {
                    $table->dropIndex(['device_fingerprint']);
                    $table->dropColumn('device_fingerprint');
                }
                if (Schema::hasColumn('affiliate_fraud_logs', 'session_id')) {
                    $table->dropIndex(['session_id']);
                    $table->dropColumn('session_id');
                }
                if (Schema::hasColumn('affiliate_fraud_logs', 'click_signature')) {
                    $table->dropUnique(['click_signature']);
                    $table->dropColumn('click_signature');
                }
                if (Schema::hasColumn('affiliate_fraud_logs', 'parent_click_id')) {
                    $table->dropColumn('parent_click_id');
                }
                if (Schema::hasColumn('affiliate_fraud_logs', 'click_source')) {
                    $table->dropColumn('click_source');
                }
                if (Schema::hasColumn('affiliate_fraud_logs', 'dedup_status')) {
                    $table->dropIndex(['dedup_status']);
                    $table->dropColumn('dedup_status');
                }
                if (Schema::hasColumn('affiliate_fraud_logs', 'dedup_reason')) {
                    $table->dropColumn('dedup_reason');
                }
            });
        }
    }
};
