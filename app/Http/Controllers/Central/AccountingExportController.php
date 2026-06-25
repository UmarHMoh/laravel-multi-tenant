<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\PlatformTransaction;
use App\Models\TenantPayoutBatch;
use App\Models\TenantPayoutLedgerEntry;
use App\Models\TenantPayoutRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccountingExportController extends Controller
{
    public function payoutRequests(Request $request): StreamedResponse
    {
        $query = TenantPayoutRequest::query()->with('tenant');

        $this->applyCommonFilters($query, $request);

        return $this->csv('payout-requests.csv', [
            'ID',
            'Tenant ID',
            'Tenant Name',
            'Reference',
            'Amount',
            'Currency',
            'Status',
            'Requested At',
            'Approved At',
            'Rejected At',
            'Paid At',
            'Tenant Notes',
            'Admin Notes',
            'Rejection Reason',
        ], $query->latest()->cursor(), function ($row) {
            return [
                $row->id,
                $row->tenant_id,
                $row->tenant?->name,
                $row->reference,
                $row->amount,
                $row->currency,
                $row->status,
                optional($row->requested_at)->toDateTimeString(),
                optional($row->approved_at)->toDateTimeString(),
                optional($row->rejected_at)->toDateTimeString(),
                optional($row->paid_at)->toDateTimeString(),
                $row->tenant_notes,
                $row->admin_notes,
                $row->rejection_reason,
            ];
        });
    }

    public function payoutBatches(Request $request): StreamedResponse
    {
        $query = TenantPayoutBatch::query();

        $this->applyDateFilters($query, $request);

        return $this->csv('payout-batches.csv', [
            'ID',
            'Batch Number',
            'Status',
            'Request Count',
            'Total Amount',
            'Currency',
            'Approved At',
            'Paid At',
            'Notes',
            'Created At',
        ], $query->latest()->cursor(), function ($row) {
            return [
                $row->id,
                $row->batch_number,
                $row->status,
                $row->request_count,
                $row->total_amount,
                $row->currency,
                optional($row->approved_at)->toDateTimeString(),
                optional($row->paid_at)->toDateTimeString(),
                $row->notes,
                optional($row->created_at)->toDateTimeString(),
            ];
        });
    }

    public function payoutLedger(Request $request): StreamedResponse
    {
        $query = TenantPayoutLedgerEntry::query();

        $this->applyCommonFilters($query, $request);

        return $this->csv('payout-ledger.csv', [
            'ID',
            'Tenant ID',
            'Platform Transaction ID',
            'Entry Type',
            'Source',
            'Status',
            'Gross Amount',
            'Processor Fee',
            'Platform Fee',
            'Tenant Net Amount',
            'Currency',
            'Reference',
            'Available At',
            'Paid At',
            'Created At',
        ], $query->latest()->cursor(), function ($row) {
            return [
                $row->id,
                $row->tenant_id,
                $row->platform_transaction_id,
                $row->entry_type,
                $row->source,
                $row->status,
                $row->gross_amount,
                $row->processor_fee_amount,
                $row->platform_fee_amount,
                $row->tenant_net_amount,
                $row->currency,
                $row->reference,
                optional($row->available_at)->toDateTimeString(),
                optional($row->paid_at)->toDateTimeString(),
                optional($row->created_at)->toDateTimeString(),
            ];
        });
    }

    public function platformTransactions(Request $request): StreamedResponse
    {
        $query = PlatformTransaction::query();

        $this->applyCommonFilters($query, $request);

        return $this->csv('platform-transactions.csv', [
            'ID',
            'Tenant ID',
            'Gross Amount',
            'Processor Fee Amount',
            'Platform Fee Amount',
            'Tenant Payout Amount',
            'Commission Amount',
            'Currency',
            'Provider',
            'Provider Transaction ID',
            'Idempotency Key',
            'Source Event',
            'Recorded At',
            'Created At',
        ], $query->latest()->cursor(), function ($row) {
            return [
                $row->id,
                $row->tenant_id,
                $row->gross_amount,
                $row->processor_fee_amount,
                $row->platform_fee_amount,
                $row->tenant_payout_amount,
                $row->commission_amount,
                $row->currency,
                $row->provider,
                $row->provider_transaction_id,
                $row->idempotency_key,
                $row->source_event,
                optional($row->recorded_at)->toDateTimeString(),
                optional($row->created_at)->toDateTimeString(),
            ];
        });
    }

    private function applyCommonFilters(Builder $query, Request $request): void
    {
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->string('tenant_id')->toString());
        }

        $this->applyDateFilters($query, $request);
    }

    private function applyDateFilters(Builder $query, Request $request): void
    {
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from')->toDateString());
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to')->toDateString());
        }
    }

    private function csv(string $filename, array $headers, iterable $rows, callable $mapRow): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows, $mapRow) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $mapRow($row));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
