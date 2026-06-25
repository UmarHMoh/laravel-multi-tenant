<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\PlatformTransaction;
use App\Models\Tenant;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\Payments\PlatformTransactionService;

class PlatformTransactionController extends Controller
{
    public function index()
    {
        $transactions = PlatformTransaction::with('tenant')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('central/transactions/Index', [
            'transactions' => $transactions,
            'summary' => [
                'gross' => PlatformTransaction::sum('gross_amount'),
                'processorFees' => PlatformTransaction::sum('processor_fee'),
                'net' => PlatformTransaction::sum('net_amount'),
                'commission' => PlatformTransaction::sum('commission_amount'),
                'pendingPayouts' => PlatformTransaction::where('payout_status', 'pending')->sum('tenant_payout_amount'),
            ],
        ]);
    }


    public function show(PlatformTransaction $transaction)

    {

        $transaction->load('tenant');

        return Inertia::render('central/transactions/Show', [

            'transaction' => $transaction,

        ]);

    }


    public function create()
    {
        return Inertia::render('central/transactions/Create', [
            'tenants' => Tenant::with('currentSubscription.plan')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'provider' => ['nullable', 'string', 'max:255'],
            'provider_transaction_id' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        $tenant = Tenant::with('currentSubscription.plan')->findOrFail($validated['tenant_id']);

        $order = new Order([
            'order_number' => 'MANUAL-' . now()->format('YmdHis'),
            'total' => $validated['gross_amount'],
            'billing_email' => 'manual-entry@example.com',
        ]);

        $order->id = null;
        $order->exists = false;

        app(PlatformTransactionService::class)->recordPaidOrder(
            tenant: $tenant,
            order: $order,
            provider: $validated['provider'] ?: 'manual',
            providerTransactionId: $validated['provider_transaction_id'] ?: null
        );

        return redirect()
            ->route('central.transactions.index')
            ->with('success', 'Transaction recorded successfully.');
    }
}
