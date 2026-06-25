<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\TenantPayoutBatch;
use App\Models\TenantPayoutRequest;
use App\Services\Payments\TenantPayoutLedgerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayoutBatchController extends Controller
{
    public function index()
    {
        return Inertia::render('central/payout-batches/Index', [
            'batches' => TenantPayoutBatch::with('requests.tenant')->latest()->get(),
            'approvedRequests' => TenantPayoutRequest::with(['tenant', 'payoutAccount'])
                ->where('status', 'approved')
                ->whereNull('tenant_payout_batch_id')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payout_request_ids' => ['required', 'array', 'min:1'],
            'payout_request_ids.*' => ['integer', 'exists:tenant_payout_requests,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            app(TenantPayoutLedgerService::class)->createPayoutBatch(
                payoutRequestIds: $validated['payout_request_ids'],
                notes: $validated['notes'] ?? null
            );

            return back()->with('success', 'Payout batch created successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markPaid(Request $request, TenantPayoutBatch $payoutBatch)
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            app(TenantPayoutLedgerService::class)->markPayoutBatchPaid(
                batch: $payoutBatch,
                adminNotes: $validated['admin_notes'] ?? null
            );

            return back()->with('success', 'Payout batch marked as paid.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, TenantPayoutBatch $payoutBatch)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            app(TenantPayoutLedgerService::class)->cancelPayoutBatch(
                batch: $payoutBatch,
                notes: $validated['notes'] ?? null
            );

            return back()->with('success', 'Payout batch cancelled.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
