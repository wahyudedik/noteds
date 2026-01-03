<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PlatformSetting;
use App\Models\PlatformWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketplaceCommissionService
{
    /**
     * Calculate commission breakdown for an order total.
     *
     * @param float $orderTotal
     * @return array
     */
    public function calculateCommission(float $orderTotal): array
    {
        $settings = $this->getCommissionSettings();

        // If commission is disabled, seller receives 100%
        if (!$settings['enabled']) {
            return [
                'order_total' => $orderTotal,
                'commission_enabled' => false,
                'commission_percentage' => 0,
                'commission_percentage_amount' => 0,
                'commission_flat' => 0,
                'commission_total' => 0,
                'seller_amount' => $orderTotal,
            ];
        }

        $percentage = (float) $settings['percentage'];
        $flatFee = (float) $settings['flat_fee'];

        // Calculate percentage commission
        $percentageAmount = round($orderTotal * ($percentage / 100), 2);

        // Calculate total commission
        $totalCommission = $percentageAmount + $flatFee;

        // Ensure seller receives at least 0 (commission cannot exceed order total)
        $sellerAmount = max(0, $orderTotal - $totalCommission);

        // If commission exceeds order total, adjust
        if ($totalCommission > $orderTotal) {
            $totalCommission = $orderTotal;
            $percentageAmount = $orderTotal - $flatFee;
            $sellerAmount = 0;
        }

        return [
            'order_total' => $orderTotal,
            'commission_enabled' => true,
            'commission_percentage' => $percentage,
            'commission_percentage_amount' => round($percentageAmount, 2),
            'commission_flat' => $flatFee,
            'commission_total' => round($totalCommission, 2),
            'seller_amount' => round($sellerAmount, 2),
        ];
    }

    /**
     * Get current commission settings.
     *
     * @return array
     */
    public function getCommissionSettings(): array
    {
        $enabled = PlatformSetting::get(
            'marketplace_commission_enabled',
            config('marketplace.commission_enabled', true)
        );

        $percentage = PlatformSetting::get(
            'marketplace_commission_percentage',
            config('marketplace.commission_percentage', 5)
        );

        $flatFee = PlatformSetting::get(
            'marketplace_commission_flat_fee',
            config('marketplace.commission_flat_fee', 0)
        );

        return [
            'enabled' => (bool) $enabled,
            'percentage' => (float) $percentage,
            'flat_fee' => (float) $flatFee,
        ];
    }

    /**
     * Update commission settings.
     *
     * @param array $settings
     * @return bool
     * @throws \Exception
     */
    public function updateCommissionSettings(array $settings): bool
    {
        // Validate settings
        $this->validateSettings($settings);

        try {
            DB::beginTransaction();

            // Update enabled status
            if (isset($settings['enabled'])) {
                PlatformSetting::set(
                    'marketplace_commission_enabled',
                    $settings['enabled'],
                    'boolean',
                    'Enable or disable marketplace commission system'
                );
            }

            // Update percentage
            if (isset($settings['percentage'])) {
                PlatformSetting::set(
                    'marketplace_commission_percentage',
                    $settings['percentage'],
                    'number',
                    'Commission percentage (0-100)'
                );
            }

            // Update flat fee
            if (isset($settings['flat_fee'])) {
                PlatformSetting::set(
                    'marketplace_commission_flat_fee',
                    $settings['flat_fee'],
                    'number',
                    'Flat fee commission amount'
                );
            }

            DB::commit();

            // Clear cache
            PlatformSetting::clearAllCache();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update marketplace commission settings: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Apply commission to an order and update balances.
     *
     * @param Order $order
     * @return array
     */
    public function applyCommission(Order $order): array
    {
        // Check if commission has already been applied to prevent double application
        if ($order->platform_commission_total !== null && $order->seller_amount !== null) {
            // Commission already applied, return existing data
            return [
                'order_total' => (float) $order->total,
                'commission_enabled' => $order->platform_commission_total > 0,
                'commission_percentage' => (float) ($order->platform_commission_percentage ?? 0),
                'commission_percentage_amount' => (float) ($order->platform_commission_total - ($order->platform_commission_flat ?? 0)),
                'commission_flat' => (float) ($order->platform_commission_flat ?? 0),
                'commission_total' => (float) $order->platform_commission_total,
                'seller_amount' => (float) $order->seller_amount,
            ];
        }

        $commissionData = $this->calculateCommission((float) $order->total);

        try {
            DB::beginTransaction();

            // Update order with commission details
            $order->update([
                'platform_commission_percentage' => $commissionData['commission_percentage'],
                'platform_commission_flat' => $commissionData['commission_flat'],
                'platform_commission_total' => $commissionData['commission_total'],
                'seller_amount' => $commissionData['seller_amount'],
            ]);

            // Add commission to platform wallet if commission is enabled and > 0
            if ($commissionData['commission_enabled'] && $commissionData['commission_total'] > 0) {
                $platformWallet = PlatformWallet::getInstance();
                $platformWallet->addFee($commissionData['commission_total']);

                // Create ledger entry for tracking
                $this->createLedgerEntry($order, $commissionData);
            }

            DB::commit();

            return $commissionData;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to apply commission to order: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
            throw $e;
        }
    }

    /**
     * Validate commission settings.
     *
     * @param array $settings
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateSettings(array $settings): void
    {
        if (isset($settings['percentage'])) {
            $percentage = (float) $settings['percentage'];
            if ($percentage < 0 || $percentage > 100) {
                throw new \InvalidArgumentException('Commission percentage must be between 0 and 100');
            }
        }

        if (isset($settings['flat_fee'])) {
            $flatFee = (float) $settings['flat_fee'];
            if ($flatFee < 0) {
                throw new \InvalidArgumentException('Flat fee cannot be negative');
            }
        }
    }

    /**
     * Create ledger entry for commission tracking.
     *
     * @param Order $order
     * @param array $commissionData
     * @return void
     */
    protected function createLedgerEntry(Order $order, array $commissionData): void
    {
        if (!class_exists(\App\Models\LedgerEntry::class)) {
            return;
        }

        try {
            $platformWallet = PlatformWallet::getInstance();
            
            // Marketplace commission: money flows from marketplace transaction to platform wallet
            // Since marketplace orders don't have a wallet type, we use 'platform' as from_wallet_type
            // to indicate it's a platform revenue (commission)
            \App\Models\LedgerEntry::createEntry([
                'from_wallet_type' => 'platform', // Marketplace transaction (no specific wallet)
                'from_wallet_id' => null,
                'to_wallet_type' => 'platform',
                'to_wallet_id' => $platformWallet->id,
                'amount' => $commissionData['commission_total'],
                'reason' => 'fee', // Platform fee from marketplace
                'reference_type' => 'App\\Models\\Order',
                'reference_id' => $order->id,
                'metadata' => [
                    'order_number' => $order->order_number,
                    'order_total' => $order->total,
                    'commission_percentage' => $commissionData['commission_percentage'],
                    'commission_flat' => $commissionData['commission_flat'],
                    'commission_type' => 'marketplace',
                    'description' => "Marketplace commission for Order #{$order->order_number}",
                ],
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the transaction - commission is already added to wallet
            Log::warning('Failed to create ledger entry for marketplace commission: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);
        }
    }
}
