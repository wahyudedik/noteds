<?php

namespace Database\Seeders;

use App\Models\PointsRule;
use App\Models\PointsSystemConfig;
use Illuminate\Database\Seeder;

class PointsRulesSeeder extends Seeder
{
    /**
     * Seed the points rules
     */
    public function run(): void
    {
        // ===== EARNING RULES =====

        // Rule 1: Purchase points earning
        PointsRule::create([
            'category' => 'earning',
            'name' => 'Peraturan Penambahan Poin dari Pembelian',
            'description' => 'User mendapat poin setiap kali melakukan pembelian di marketplace. Tidak ada poin jika pesanan dibatalkan atau refund.',
            'conditions' => [
                [
                    'field' => 'source_type',
                    'operator' => '=',
                    'value' => 'purchase',
                ],
            ],
            'priority' => 100,
            'is_active' => true,
            'max_attempts' => null,
            'cooldown_minutes' => null,
            'penalty_points' => null,
            'notify_admin' => false,
            'notify_user' => true,
            'notification_type' => 'points_earned',
            'notes' => '1% dari nilai pembelian = poin (Rp 100.000 = 1000 poin)',
        ]);

        // Rule 2: Referral points earning
        PointsRule::create([
            'category' => 'earning',
            'name' => 'Peraturan Bonus Referral',
            'description' => 'User mendapat bonus poin jika merekomendasikan teman yang baru daftar dan melakukan pembelian pertama.',
            'conditions' => [
                [
                    'field' => 'source_type',
                    'operator' => '=',
                    'value' => 'referral',
                ],
            ],
            'priority' => 90,
            'is_active' => true,
            'max_attempts' => 100, // Max 100 referral per bulan
            'cooldown_minutes' => 1440, // 1 hari
            'penalty_points' => 500, // Jika melanggar, potong 500 poin
            'notify_admin' => false,
            'notify_user' => true,
            'notification_type' => 'points_earned',
            'notes' => 'Bonus referral: 5000 poin per teman yang sukses membeli',
        ]);

        // Rule 3: Sign up bonus
        PointsRule::create([
            'category' => 'earning',
            'name' => 'Bonus Pendaftaran Member Baru',
            'description' => 'User baru mendapat poin bonus saat pendaftaran akun.',
            'conditions' => [
                [
                    'field' => 'source_type',
                    'operator' => '=',
                    'value' => 'signup_bonus',
                ],
            ],
            'priority' => 50,
            'is_active' => true,
            'max_attempts' => 1, // Hanya 1x
            'cooldown_minutes' => null,
            'penalty_points' => null,
            'notify_admin' => false,
            'notify_user' => true,
            'notification_type' => 'points_earned',
            'notes' => 'Bonus signup: 1000 poin untuk member baru',
        ]);

        // ===== REDEMPTION RULES =====

        // Rule 4: Daily redemption limit
        PointsRule::create([
            'category' => 'redemption',
            'name' => 'Batas Penukaran Per Hari',
            'description' => 'User hanya bisa menukar poin maksimal 5x per hari untuk mencegah abuse.',
            'conditions' => [
                [
                    'field' => 'daily_count',
                    'operator' => '>',
                    'value' => 5,
                ],
            ],
            'priority' => 100,
            'is_active' => true,
            'max_attempts' => null,
            'cooldown_minutes' => 1440, // Reset setiap hari
            'penalty_points' => 0,
            'notify_admin' => true,
            'notify_user' => true,
            'notification_type' => 'limit_reached',
            'notes' => 'Max 5 redemptions per hari per user',
        ]);

        // Rule 5: Minimum points requirement
        PointsRule::create([
            'category' => 'redemption',
            'name' => 'Minimal Poin untuk Penukaran',
            'description' => 'User harus memiliki poin minimal sesuai konfigurasi untuk setiap paket penukaran.',
            'conditions' => [
                [
                    'field' => 'user_points',
                    'operator' => '<',
                    'value' => 'required_points',
                ],
            ],
            'priority' => 100,
            'is_active' => true,
            'max_attempts' => null,
            'cooldown_minutes' => null,
            'penalty_points' => 0,
            'notify_admin' => false,
            'notify_user' => true,
            'notification_type' => 'insufficient_points',
            'notes' => 'User harus memiliki poin cukup untuk menukar',
        ]);

        // Rule 6: Prevent rapid redemptions
        PointsRule::create([
            'category' => 'redemption',
            'name' => 'Deteksi Penukaran Cepat Beruntun',
            'description' => 'Jika user menukar poin lebih dari 3x dalam 1 jam, flag sebagai suspicious.',
            'conditions' => [
                [
                    'field' => 'last_hour_count',
                    'operator' => '>',
                    'value' => 3,
                ],
            ],
            'priority' => 95,
            'is_active' => true,
            'max_attempts' => null,
            'cooldown_minutes' => 60,
            'penalty_points' => 1000, // Potong 1000 poin
            'notify_admin' => true,
            'notify_user' => false,
            'notification_type' => 'suspicious_activity',
            'notes' => 'Deteksi bot atau automated tools',
        ]);

        // ===== MARKETPLACE INTEGRATION RULES =====

        // Rule 7: Discount limit per transaction
        PointsRule::create([
            'category' => 'marketplace',
            'name' => 'Batas Diskon dari Poin Per Transaksi',
            'description' => 'Diskon dari poin tidak boleh lebih dari 50% dari total harga transaksi.',
            'conditions' => [
                [
                    'field' => 'discount_percent',
                    'operator' => '>',
                    'value' => 50,
                ],
            ],
            'priority' => 100,
            'is_active' => true,
            'max_attempts' => null,
            'cooldown_minutes' => null,
            'penalty_points' => 0,
            'notify_admin' => true,
            'notify_user' => true,
            'notification_type' => 'discount_limit_exceeded',
            'notes' => 'Diskon poin max 50% dari harga transaksi',
        ]);

        // Rule 8: Prevent multiple discounts on same transaction
        PointsRule::create([
            'category' => 'marketplace',
            'name' => 'Cegah Multiple Diskon Poin Satu Transaksi',
            'description' => 'User tidak bisa menggunakan 2 diskon poin untuk transaksi yang sama.',
            'conditions' => [
                [
                    'field' => 'duplicate_discount',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
            'priority' => 100,
            'is_active' => true,
            'max_attempts' => null,
            'cooldown_minutes' => null,
            'penalty_points' => 2000, // Potong 2000 poin penalty
            'notify_admin' => true,
            'notify_user' => true,
            'notification_type' => 'rule_violation',
            'notes' => 'Hanya 1 diskon poin per transaksi',
        ]);

        // ===== FRAUD PREVENTION RULES =====

        // Rule 9: Rapid IP changes
        PointsRule::create([
            'category' => 'fraud_prevention',
            'name' => 'Deteksi Perubahan IP Cepat',
            'description' => 'IP address berubah dalam waktu kurang dari 1 menit = suspicious (tidak natural).',
            'conditions' => [
                [
                    'field' => 'ip_change_time',
                    'operator' => '<',
                    'value' => 60,
                ],
            ],
            'priority' => 100,
            'is_active' => true,
            'max_attempts' => 1,
            'cooldown_minutes' => null,
            'penalty_points' => 5000,
            'notify_admin' => true,
            'notify_user' => false,
            'notification_type' => 'suspicious_activity',
            'notes' => 'Indikasi VPN atau proxy untuk bypass rules',
        ]);

        // Rule 10: Account takeover detection
        PointsRule::create([
            'category' => 'fraud_prevention',
            'name' => 'Deteksi Akun Diambil Alih (Account Takeover)',
            'description' => 'Login dari lokasi baru + redemption di jam yang tidak biasa = kemungkinan account takeover.',
            'conditions' => [
                [
                    'field' => 'new_location',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
            'priority' => 90,
            'is_active' => true,
            'max_attempts' => null,
            'cooldown_minutes' => 240, // 4 jam
            'penalty_points' => 0,
            'notify_admin' => true,
            'notify_user' => true,
            'notification_type' => 'security_alert',
            'notes' => 'Require 2FA verification untuk redemption',
        ]);

        // ===== SYSTEM CONFIGURATION =====

        PointsSystemConfig::setValue('earning_rate', '0.01', 'decimal', 'earning');
        PointsSystemConfig::setValue('earning_description', 'User mendapat 1 poin setiap Rp 100 dari pembelian', 'string', 'earning');

        PointsSystemConfig::setValue('referral_bonus', '5000', 'integer', 'earning');
        PointsSystemConfig::setValue('signup_bonus', '1000', 'integer', 'earning');

        PointsSystemConfig::setValue('daily_redemption_limit', '5', 'integer', 'redemption');
        PointsSystemConfig::setValue('hourly_redemption_limit', '3', 'integer', 'redemption');

        PointsSystemConfig::setValue('max_discount_percent', '50', 'integer', 'marketplace');
        PointsSystemConfig::setValue('max_discount_amount', '500000', 'integer', 'marketplace');

        PointsSystemConfig::setValue('fraud_ip_threshold', '60', 'integer', 'fraud_prevention');
        PointsSystemConfig::setValue('fraud_confidence_high', '80', 'integer', 'fraud_prevention');
        PointsSystemConfig::setValue('auto_suspend_on_high_fraud', 'true', 'boolean', 'fraud_prevention');
        PointsSystemConfig::setValue('suspension_days', '7', 'integer', 'fraud_prevention');
    }
}
