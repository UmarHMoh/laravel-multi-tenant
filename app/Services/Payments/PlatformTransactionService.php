<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\PlatformTransaction;
use App\Services\Payments\TenantPayoutLedgerService;
use App\Models\Tenant;

class PlatformTransactionService
{
    public function recordPaidOrder(Tenant $tenant, Order $order, string $provider, ?string $providerTransactionId = null): ?PlatformTransaction
    {
        $alreadyExists = PlatformTransaction::where('tenant_id', $tenant->id)
            ->where('tenant_order_id', $order->id)
            ->first();

        if ($alreadyExists) {
            return $alreadyExists;
        }

        $tenant->loadMissing('currentSubscription.plan');

        $paymentSetting = PaymentSetting::active() ?? PaymentSetting::latest()->first();

        $processorFeePercent = (float) ($paymentSetting?->processor_fee_percent ?? 3.5);
        $processorFeeFixed = (float) ($paymentSetting?->processor_fee_fixed ?? 0.30);

        $plan = $tenant->currentSubscription?->plan;

        // Plan transaction fees are your platform add-on fees.
        // They are added on top of the payment provider's processor fees.
        $platformFeePercent = (float) (
            $plan?->transaction_fee_percent
            ?? $plan?->commission_rate
            ?? 0
        );

        $platformFeeFixed = (float) (
            $plan?->transaction_fee_fixed
            ?? 0
        );

        $tenantTransactionFeePercent = round($processorFeePercent + $platformFeePercent, 4);
        $tenantTransactionFeeFixed = round($processorFeeFixed + $platformFeeFixed, 2);

        $grossAmount = round((float) $order->total, 2);

        $processorFee = round(($grossAmount * ($processorFeePercent / 100)) + $processorFeeFixed, 2);
        $netAmount = round($grossAmount - $processorFee, 2);

        $platformFeeAmount = round(
            ($grossAmount * ($platformFeePercent / 100)) + $platformFeeFixed,
            2
        );

        $totalTenantFeeAmount = round($processorFee + $platformFeeAmount, 2);

        $tenantPayoutAmount = round($grossAmount - $totalTenantFeeAmount, 2);

        $tenantData = $tenant->data ?? [];
        $currency = strtoupper(
            $paymentSetting?->currency
            ?? $tenantData['store_currency']
            ?? 'USD'
        );

        $idempotencyKey = $this->buildIdempotencyKey($tenant->id, $order->id, $provider);

        $existing = PlatformTransaction::where('idempotency_key', $idempotencyKey)->first();

        if ($existing) {
            app(TenantPayoutLedgerService::class)->recordPaidOrder($existing);

            return $existing;
        }

        $transaction = PlatformTransaction::create([
            'tenant_id' => $tenant->id,
            'tenant_order_id' => $order->id,
            'provider' => $provider,
            'provider_transaction_id' => $providerTransactionId ?: strtoupper($provider) . '-' . $order->order_number,
            'currency' => $currency,

            'gross_amount' => $grossAmount,

            'processor_fee' => $processorFee,
            'processor_fee_percent' => $processorFeePercent,
            'processor_fee_fixed' => $processorFeeFixed,

            'net_amount' => $netAmount,

            // Backwards-compatible fields.
            // commission_rate and commission_amount now represent your platform profit,
            // not the full tenant-facing plan fee.
            'commission_rate' => $platformFeePercent,
            'commission_amount' => $platformFeeAmount,

            'tenant_transaction_fee_percent' => $tenantTransactionFeePercent,
            'tenant_transaction_fee_fixed' => $tenantTransactionFeeFixed,
            'total_tenant_fee_amount' => $totalTenantFeeAmount,

            'platform_fee_percent' => $platformFeePercent,
            'platform_fee_fixed' => $platformFeeFixed,
            'platform_fee_amount' => $platformFeeAmount,

            'tenant_payout_amount' => $tenantPayoutAmount,

            'payment_status' => 'paid',
            'payout_status' => 'pending',
            'paid_at' => now(),

            'metadata' => [
                'calculation_version' => 'marketplace_fee_v3_provider_plus_platform',
                'source' => $provider,
                'order_number' => $order->order_number,
                'billing_email' => $order->billing_email,
                'payment_setting_id' => $paymentSetting?->id,
                'plan_id' => $plan?->id,
                'warning' => $platformFeeAmount < 0
                    ? 'Plan tenant fee is lower than processor fee. Platform is losing money on this transaction.'
                    : null,
            ],
        ]);

        app(TenantPayoutLedgerService::class)->recordPaidOrder($transaction);

        return $transaction;
    }


    private function buildIdempotencyKey(int|string $tenantId, int|string $orderId, string $provider): string
    {
        return hash('sha256', implode('|', [
            'platform_transaction',
            $tenantId,
            $orderId,
            $provider,
        ]));
    }

}
