<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\TenantPayoutAccount;
use App\Models\TenantPayoutRequest;
use App\Services\Payments\TenantPayoutLedgerService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayoutRequestController extends Controller
{
    public function index()
    {
        $tenant = tenant();

        $balance = app(TenantPayoutLedgerService::class)->balanceForTenant((string) $tenant->id);

        return Inertia::render('tenant/payouts/Index', [
            'balance' => $balance,
            'payoutAccount' => TenantPayoutAccount::where('tenant_id', $tenant->id)->latest()->first(),
            'requests' => TenantPayoutRequest::where('tenant_id', $tenant->id)->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $tenant = tenant();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'tenant_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $account = TenantPayoutAccount::where('tenant_id', $tenant->id)->latest()->first();

            app(TenantPayoutLedgerService::class)->createPayoutRequest(
                tenantId: (string) $tenant->id,
                amount: (float) $validated['amount'],
                currency: 'TTD',
                payoutAccountId: $account?->id,
                notes: $validated['tenant_notes'] ?? null
            );

            return back()->with('success', 'Payout request submitted successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
