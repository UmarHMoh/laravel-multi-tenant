<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\PlatformTransaction;
use App\Models\Tenant;
use App\Models\TenantPayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

class PayoutController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['domains', 'payoutAccounts' => function ($query) {
                $query->latest();
            }])
            ->withSum(['platformTransactions as pending_payout_total' => function ($query) {
                $query->where('payout_status', 'pending');
            }], 'tenant_payout_amount')
            ->withSum(['platformTransactions as pending_gross_total' => function ($query) {
                $query->where('payout_status', 'pending');
            }], 'gross_amount')
            ->withSum(['platformTransactions as pending_commission_total' => function ($query) {
                $query->where('payout_status', 'pending');
            }], 'commission_amount')
            ->withCount(['platformTransactions as pending_transaction_count' => function ($query) {
                $query->where('payout_status', 'pending');
            }])
            ->orderBy('name')
            ->get();

        return Inertia::render('central/payouts/Index', [
            'tenants' => $tenants,
            'summary' => [
                'pendingPayoutTotal' => PlatformTransaction::where('payout_status', 'pending')->sum('tenant_payout_amount'),
                'pendingCommissionTotal' => PlatformTransaction::where('payout_status', 'pending')->sum('commission_amount'),
                'pendingGrossTotal' => PlatformTransaction::where('payout_status', 'pending')->sum('gross_amount'),
                'pendingTransactionCount' => PlatformTransaction::where('payout_status', 'pending')->count(),
            ],
        ]);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['domains', 'payoutAccounts' => function ($query) {
            $query->latest();
        }]);

        $pendingTransactions = PlatformTransaction::where('tenant_id', $tenant->id)
            ->where('payout_status', 'pending')
            ->orderBy('paid_at')
            ->get();

        $payouts = TenantPayout::with('payoutAccount')
            ->where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $payoutAccounts = $tenant->payoutAccounts->map(function ($account) {
            return [
                'id' => $account->id,
                'tenant_id' => $account->tenant_id,
                'type' => $account->type,
                'account_holder_name' => $account->account_holder_name,
                'bank_name' => $account->bank_name,
                'bank_account_number' => $this->decryptValue($account->bank_account_number),
                'branch_transit_number' => $this->decryptValue($account->branch_transit_number),
                'bank_account_type' => $account->bank_account_type,
                'wipay_account_email' => $this->decryptValue($account->wipay_account_email),
                'status' => $account->status,
                'notes' => $account->notes,
                'review_notes' => $account->review_notes,
                'reviewed_at' => $account->reviewed_at,
                'reviewed_by' => $account->reviewed_by,
                'created_at' => $account->created_at,
                'updated_at' => $account->updated_at,
            ];
        })->values();

        return Inertia::render('central/payouts/Show', [
            'tenant' => $tenant,
            'payoutAccounts' => $payoutAccounts,
            'latestPayoutAccount' => $payoutAccounts->first(),
            'pendingTransactions' => $pendingTransactions,
            'payouts' => $payouts,
            'summary' => [
                'gross' => $pendingTransactions->sum('gross_amount'),
                'processorFees' => $pendingTransactions->sum('processor_fee'),
                'net' => $pendingTransactions->sum('net_amount'),
                'commission' => $pendingTransactions->sum('commission_amount'),
                'payout' => $pendingTransactions->sum('tenant_payout_amount'),
                'count' => $pendingTransactions->count(),
            ],
        ]);
    }

    public function batchShow(TenantPayout $payout)
    {
        $payout->load(['tenant', 'payoutAccount', 'transactions']);

        return Inertia::render('central/payouts/BatchShow', [
            'payout' => $payout,
        ]);
    }

    public function markPaid(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $pendingTransactions = PlatformTransaction::where('tenant_id', $tenant->id)
            ->where('payout_status', 'pending')
            ->get();

        if ($pendingTransactions->isEmpty()) {
            return back()->withErrors([
                'payout' => 'There are no pending transactions to pay out for this tenant.',
            ]);
        }

        $payoutAccount = $tenant->payoutAccounts()->latest()->first();

        if (! $payoutAccount) {
            return back()->withErrors([
                'payout' => 'This tenant has not submitted a payout account yet.',
            ]);
        }

        if ($payoutAccount->status !== 'verified') {
            return back()->withErrors([
                'payout' => 'This tenant payout account must be approved before marking payouts as paid.',
            ]);
        }

        DB::transaction(function () use ($tenant, $pendingTransactions, $validated, $payoutAccount) {
            $payout = TenantPayout::create([
                'tenant_id' => $tenant->id,
                'tenant_payout_account_id' => $payoutAccount->id,
                'currency' => $pendingTransactions->first()->currency ?? 'USD',
                'total_gross' => $pendingTransactions->sum('gross_amount'),
                'total_processor_fees' => $pendingTransactions->sum('processor_fee'),
                'total_net' => $pendingTransactions->sum('net_amount'),
                'total_commission' => $pendingTransactions->sum('commission_amount'),
                'total_payout' => $pendingTransactions->sum('tenant_payout_amount'),
                'status' => 'paid',
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'paid_at' => now(),
            ]);

            $payout->transactions()->attach($pendingTransactions->pluck('id')->all());

            PlatformTransaction::whereIn('id', $pendingTransactions->pluck('id'))
                ->update([
                    'payout_status' => 'paid',
                    'payout_marked_at' => now(),
                ]);
        });

        return redirect()
            ->route('central.payouts.show', $tenant->id)
            ->with('success', 'Payout marked as paid successfully.');
    }
    private function decryptValue(?string $encrypted): ?string
    {
        if (! $encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable $e) {
            return 'Hidden';
        }
    }
}
