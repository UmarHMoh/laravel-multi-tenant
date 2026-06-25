<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\TenantPayoutRequest;
use App\Services\Payments\TenantPayoutLedgerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayoutRequestController extends Controller
{
    public function index()
    {
        return Inertia::render('central/payout-requests/Index', [
            'requests' => TenantPayoutRequest::with(['tenant', 'payoutAccount', 'ledgerEntry'])->latest()->get(),
        ]);
    }

    public function approve(Request $request, TenantPayoutRequest $payoutRequest)
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            app(TenantPayoutLedgerService::class)->approvePayoutRequest($payoutRequest, $validated['admin_notes'] ?? null);

            return back()->with('success', 'Payout request approved.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, TenantPayoutRequest $payoutRequest)
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            app(TenantPayoutLedgerService::class)->rejectPayoutRequest($payoutRequest, $validated['rejection_reason'] ?? null);

            return back()->with('success', 'Payout request rejected.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function markPaid(Request $request, TenantPayoutRequest $payoutRequest)
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            app(TenantPayoutLedgerService::class)->markPayoutRequestPaid($payoutRequest, $validated['admin_notes'] ?? null);

            return back()->with('success', 'Payout request marked as paid.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
