<?php

namespace App\Services\Payments;

use App\Models\PlatformTransaction;
use App\Models\TenantPayoutLedgerEntry;
use App\Models\TenantPayoutRequest;
use App\Models\TenantPayoutBatch;

class TenantPayoutLedgerService
{
    public function recordPaidOrder(PlatformTransaction $transaction): TenantPayoutLedgerEntry
    {
        $existing = TenantPayoutLedgerEntry::where('platform_transaction_id', $transaction->id)
            ->where('source', 'paid_order')
            ->first();

        if ($existing) {
            return $existing;
        }

        return TenantPayoutLedgerEntry::create([
            'tenant_id' => $transaction->tenant_id,
            'platform_transaction_id' => $transaction->id,
            'entry_type' => 'credit',
            'source' => 'paid_order',
            'status' => 'available',
            'gross_amount' => $transaction->gross_amount,
            'processor_fee_amount' => $transaction->processor_fee_amount ?? 0,
            'platform_fee_amount' => $transaction->platform_fee_amount ?? $transaction->commission_amount ?? 0,
            'tenant_net_amount' => $transaction->tenant_payout_amount,
            'currency' => $transaction->currency ?? 'TTD',
            'reference' => $transaction->provider_transaction_id,
            'available_at' => now(),
            'metadata' => [
                'platform_transaction_id' => $transaction->id,
                'provider' => $transaction->provider,
                'idempotency_key' => $transaction->idempotency_key ?? null,
            ],
        ]);
    }

    public function balanceForTenant(string $tenantId): array
    {
        $availableCredits = TenantPayoutLedgerEntry::where('tenant_id', $tenantId)
            ->where('entry_type', 'credit')
            ->where('status', 'available')
            ->sum('tenant_net_amount');

        $paidDebits = TenantPayoutLedgerEntry::where('tenant_id', $tenantId)
            ->where('entry_type', 'debit')
            ->whereIn('status', ['paid', 'available'])
            ->sum('tenant_net_amount');

        $pendingCredits = TenantPayoutLedgerEntry::where('tenant_id', $tenantId)
            ->where('entry_type', 'credit')
            ->where('status', 'pending')
            ->sum('tenant_net_amount');

        return [
            'available_balance' => round((float) $availableCredits - (float) $paidDebits, 2),
            'pending_balance' => round((float) $pendingCredits, 2),
            'lifetime_earned' => round((float) TenantPayoutLedgerEntry::where('tenant_id', $tenantId)
                ->where('entry_type', 'credit')
                ->sum('tenant_net_amount'), 2),
            'currency' => TenantPayoutLedgerEntry::where('tenant_id', $tenantId)->latest()->value('currency') ?? 'TTD',
        ];
    }


    public function createPayoutRequest(string $tenantId, float $amount, string $currency = 'TTD', ?int $payoutAccountId = null, ?string $notes = null): TenantPayoutRequest
    {
        $balance = $this->balanceForTenant($tenantId);

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payout amount must be greater than zero.');
        }

        if ($amount > (float) $balance['available_balance']) {
            throw new \InvalidArgumentException('Payout request exceeds available balance.');
        }

        return TenantPayoutRequest::create([
            'tenant_id' => $tenantId,
            'tenant_payout_account_id' => $payoutAccountId,
            'amount' => $amount,
            'currency' => strtoupper($currency ?: ($balance['currency'] ?? 'TTD')),
            'status' => 'requested',
            'tenant_notes' => $notes,
            'requested_at' => now(),
            'reference' => 'PO-' . strtoupper(substr(hash('sha256', $tenantId . microtime(true)), 0, 10)),
            'metadata' => [
                'balance_at_request' => $balance,
            ],
        ]);
    }

    public function approvePayoutRequest(TenantPayoutRequest $request, ?string $adminNotes = null): TenantPayoutRequest
    {
        if (! in_array($request->status, ['requested'], true)) {
            throw new \InvalidArgumentException('Only requested payouts can be approved.');
        }

        $request->update([
            'status' => 'approved',
            'approved_at' => now(),
            'admin_notes' => $adminNotes,
        ]);

        return $request->fresh();
    }

    public function rejectPayoutRequest(TenantPayoutRequest $request, ?string $reason = null): TenantPayoutRequest
    {
        if (! in_array($request->status, ['requested', 'approved'], true)) {
            throw new \InvalidArgumentException('Only requested or approved payouts can be rejected.');
        }

        $request->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $request->fresh();
    }

    public function markPayoutRequestPaid(TenantPayoutRequest $request, ?string $adminNotes = null): TenantPayoutRequest
    {
        if (! in_array($request->status, ['requested', 'approved'], true)) {
            throw new \InvalidArgumentException('Only requested or approved payouts can be marked paid.');
        }

        $balance = $this->balanceForTenant((string) $request->tenant_id);

        if ((float) $request->amount > (float) $balance['available_balance']) {
            throw new \InvalidArgumentException('Payout amount exceeds current available balance.');
        }

        $existingDebit = TenantPayoutLedgerEntry::where('tenant_id', $request->tenant_id)
            ->where('source', 'payout')
            ->where('reference', $request->reference)
            ->first();

        if (! $existingDebit) {
            $existingDebit = TenantPayoutLedgerEntry::create([
                'tenant_id' => $request->tenant_id,
                'platform_transaction_id' => null,
                'entry_type' => 'debit',
                'source' => 'payout',
                'status' => 'paid',
                'gross_amount' => 0,
                'processor_fee_amount' => 0,
                'platform_fee_amount' => 0,
                'tenant_net_amount' => $request->amount,
                'currency' => $request->currency,
                'reference' => $request->reference,
                'paid_at' => now(),
                'metadata' => [
                    'payout_request_id' => $request->id,
                    'balance_before_payout' => $balance,
                ],
            ]);
        }

        $request->update([
            'tenant_payout_ledger_entry_id' => $existingDebit->id,
            'status' => 'paid',
            'paid_at' => now(),
            'admin_notes' => $adminNotes,
        ]);

        return $request->fresh();
    }



    public function createPayoutBatch(array $payoutRequestIds, ?string $notes = null): TenantPayoutBatch
    {
        $requests = TenantPayoutRequest::whereIn('id', $payoutRequestIds)
            ->where('status', 'approved')
            ->whereNull('tenant_payout_batch_id')
            ->get();

        if ($requests->isEmpty()) {
            throw new \InvalidArgumentException('No approved payout requests are available for batching.');
        }

        $currency = $requests->first()->currency ?? 'TTD';

        $mixedCurrency = $requests->contains(fn ($request) => $request->currency !== $currency);

        if ($mixedCurrency) {
            throw new \InvalidArgumentException('Cannot batch payout requests with mixed currencies.');
        }

        $batch = TenantPayoutBatch::create([
            'batch_number' => 'PB-' . now()->format('Ymd') . '-' . strtoupper(substr(hash('sha256', microtime(true) . random_int(1, 999999)), 0, 8)),
            'status' => 'approved',
            'request_count' => $requests->count(),
            'total_amount' => $requests->sum('amount'),
            'currency' => $currency,
            'notes' => $notes,
            'approved_at' => now(),
            'metadata' => [
                'payout_request_ids' => $requests->pluck('id')->values()->all(),
            ],
        ]);

        TenantPayoutRequest::whereIn('id', $requests->pluck('id'))
            ->update([
                'tenant_payout_batch_id' => $batch->id,
            ]);

        return $batch->fresh(['requests']);
    }

    public function markPayoutBatchPaid(TenantPayoutBatch $batch, ?string $adminNotes = null): TenantPayoutBatch
    {
        if (! in_array($batch->status, ['approved', 'draft'], true)) {
            throw new \InvalidArgumentException('Only approved or draft payout batches can be marked paid.');
        }

        $requests = $batch->requests()->whereIn('status', ['requested', 'approved'])->get();

        if ($requests->isEmpty()) {
            throw new \InvalidArgumentException('This batch has no payable payout requests.');
        }

        foreach ($requests as $request) {
            $this->markPayoutRequestPaid($request, $adminNotes ?: 'Paid through payout batch ' . $batch->batch_number);
        }

        $batch->update([
            'status' => 'paid',
            'paid_at' => now(),
            'request_count' => $batch->requests()->count(),
            'total_amount' => $batch->requests()->sum('amount'),
            'metadata' => array_merge($batch->metadata ?? [], [
                'paid_request_ids' => $batch->requests()->pluck('id')->values()->all(),
            ]),
        ]);

        return $batch->fresh(['requests']);
    }

    public function cancelPayoutBatch(TenantPayoutBatch $batch, ?string $notes = null): TenantPayoutBatch
    {
        if ($batch->status === 'paid') {
            throw new \InvalidArgumentException('Paid payout batches cannot be cancelled.');
        }

        TenantPayoutRequest::where('tenant_payout_batch_id', $batch->id)
            ->whereIn('status', ['requested', 'approved'])
            ->update([
                'tenant_payout_batch_id' => null,
            ]);

        $batch->update([
            'status' => 'cancelled',
            'notes' => $notes ?: $batch->notes,
        ]);

        return $batch->fresh(['requests']);
    }

}
