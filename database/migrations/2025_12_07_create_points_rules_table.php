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
        // Tabel untuk rules/peraturan poin
        Schema::create('points_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Kategori aturan
            $table->enum('category', ['earning', 'redemption', 'usage', 'marketplace', 'fraud_prevention'])
                ->default('earning');

            // Informasi aturan
            $table->string('name', 255)->index();
            $table->text('description');
            $table->text('conditions'); // JSON: syarat-syarat aturan

            // Pengaturan
            $table->integer('priority')->default(0); // Priority untuk urutan enforcement
            $table->boolean('is_active')->default(true)->index();
            $table->integer('max_attempts')->nullable(); // Max attempts sebelum di-flag
            $table->integer('cooldown_minutes')->nullable(); // Cooldown period
            $table->decimal('penalty_points', 10, 2)->nullable(); // Poin yang dikurangi jika melanggar

            // Notifikasi
            $table->boolean('notify_admin')->default(true);
            $table->boolean('notify_user')->default(false);
            $table->string('notification_type', 50)->default('rule_violation'); // rule_violation, suspicious_activity, etc

            // Logs dan tracking
            $table->integer('violation_count')->default(0);
            $table->timestamp('last_violation_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });

        // Tabel untuk track setiap aktivitas poin (untuk audit dan deteksi fraud)
        Schema::create('points_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Tipe aktivitas
            $table->enum('activity_type', [
                'earned',
                'redeemed',
                'used',
                'expired',
                'refunded',
                'transferred',
                'deducted',
                'adjusted'
            ])->index();

            // Detail aktivitas
            $table->integer('points_amount')->nullable();
            $table->decimal('monetary_value', 12, 2)->nullable();
            $table->string('source_type', 100)->nullable(); // pembelian, referral, kuis, dll
            $table->uuid('related_id')->nullable(); // reference ke order, redemption, dll

            // Informasi transaksi
            $table->string('transaction_reference', 255)->nullable()->index();
            $table->json('metadata')->nullable(); // Extra data

            // Status dan aturan
            $table->enum('status', ['pending', 'approved', 'flagged', 'rejected'])->default('pending')->index();
            $table->uuid('rule_id')->nullable();
            $table->foreign('rule_id')->references('id')->on('points_rules')->onDelete('set null');

            // Deteksi fraud
            $table->boolean('is_suspicious')->default(false)->index();
            $table->string('fraud_flag_reason', 255)->nullable();
            $table->integer('risk_score')->default(0); // 0-100

            // IP dan device tracking
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent', 255)->nullable();

            // Approval
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'activity_type', 'created_at']);
        });

        // Tabel untuk notifikasi admin
        Schema::create('points_admin_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('admin_id')->index();
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');

            // Notifikasi content
            $table->enum('notification_type', [
                'discount_used',
                'redemption_completed',
                'suspicious_activity',
                'rule_violation',
                'daily_limit_reached',
                'user_limit_warning',
                'high_value_redemption'
            ])->index();

            $table->text('message');
            $table->uuid('related_user_id')->nullable();
            $table->uuid('related_activity_id')->nullable();
            $table->json('data')->nullable();

            // Status notifikasi
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_actioned')->default(false);
            $table->timestamp('actioned_at')->nullable();

            $table->integer('severity')->default(1); // 1 (low), 2 (medium), 3 (high)
            $table->string('action_url', 255)->nullable();

            $table->timestamps();

            $table->index(['admin_id', 'is_read', 'created_at']);
        });

        // Tabel untuk fraud detection logs
        Schema::create('points_fraud_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Flag details
            $table->enum('flag_type', [
                'rapid_redemptions',
                'high_value_pattern',
                'impossible_timing',
                'ip_change',
                'device_change',
                'multiple_accounts',
                'rule_violation_pattern',
            ])->index();

            $table->text('description');
            $table->integer('severity')->default(1); // 1-3
            $table->integer('confidence_score')->default(0); // 0-100

            // Evidence
            $table->json('evidence')->nullable();
            $table->uuid('triggered_by_activity_id')->nullable();

            // Action
            $table->enum('status', ['pending', 'investigating', 'resolved', 'false_positive'])
                ->default('pending')->index();
            $table->uuid('investigated_by')->nullable();
            $table->timestamp('investigated_at')->nullable();
            $table->text('investigation_notes')->nullable();

            // Automatic action
            $table->boolean('auto_flagged')->default(true);
            $table->boolean('points_suspended')->default(false);
            $table->timestamp('suspension_until')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at']);
        });

        // Tabel untuk track peraturan yang dilanggar
        Schema::create('points_rule_violations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('rule_id')->index();
            $table->foreign('rule_id')->references('id')->on('points_rules')->onDelete('cascade');

            $table->uuid('activity_id')->nullable();
            $table->foreign('activity_id')->references('id')->on('points_activities')->onDelete('set null');

            // Detail pelanggaran
            $table->text('violation_details');
            $table->integer('severity')->default(1); // 1 (warning), 2 (penalty), 3 (suspension)
            $table->integer('points_penalty')->default(0);

            // Status
            $table->enum('status', ['reported', 'acknowledged', 'warned', 'penalized', 'appealed'])
                ->default('reported')->index();

            // Admin action
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_decision')->nullable();

            // Appeal
            $table->text('user_appeal')->nullable();
            $table->boolean('appeal_approved')->default(false)->nullable();

            $table->timestamps();

            $table->index(['user_id', 'rule_id', 'status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_rule_violations');
        Schema::dropIfExists('points_fraud_flags');
        Schema::dropIfExists('points_admin_notifications');
        Schema::dropIfExists('points_activities');
        Schema::dropIfExists('points_rules');
    }
};
