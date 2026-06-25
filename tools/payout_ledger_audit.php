<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\PaymentCallbackController;
use App\Models\Order;
use App\Models\PlatformTransaction;
use App\Models\Tenant;
use App\Models\TenantPayoutLedgerEntry;
use App\Services\Payments\TenantPayoutLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$results = [];

function add_payout_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = compact('tenantId', 'name', 'status', 'message', 'data');
    $results[array_key_last($results)]['tenant_id'] = $tenantId;
    unset($results[array_key_last($results)]['tenantId']);
}

foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);

    DB::beginTransaction();
    DB::connection('mysql')->beginTransaction();

    try {
        $beforeBalance = app(TenantPayoutLedgerService::class)->balanceForTenant((string) $tenant->id);
        $beforeLedgerCount = TenantPayoutLedgerEntry::where('tenant_id', $tenant->id)->count();
        $beforeTransactionCount = PlatformTransaction::where('tenant_id', $tenant->id)->count();

        $user = \App\Models\User::first();

        $order = Order::create([
            'order_number' => 'PAYOUT-' . strtoupper(substr(md5($tenant->id . microtime()), 0, 10)),
            'user_id' => $user?->id,
            'total' => 100,
            'status' => 'pending',
            'notes' => 'Payout ledger audit order.',
            'billing_name' => 'Audit Customer',
            'billing_email' => 'audit@example.com',
            'billing_phone' => '8681234567',
            'billing_address' => '123 Audit Street',
            'billing_city' => 'San Fernando',
            'billing_state' => 'South',
            'billing_country' => 'TT',
            'billing_zipcode' => '00000',
            'shipping_name' => 'Audit Customer',
            'shipping_address' => '123 Audit Street',
            'shipping_city' => 'San Fernando',
            'shipping_state' => 'South',
            'shipping_country' => 'TT',
            'shipping_zipcode' => '00000',
            'payment_method' => 'wipay',
            'payment_provider' => 'wipay',
            'payment_status' => 'pending',
            'payment_metadata' => ['currency' => 'TTD'],
        ]);

        $payload = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => 'paid',
            'payment_id' => 'PAYOUT-PAY-' . $order->id,
            'reference' => 'PAYOUT-REF-' . $order->id,
        ];

        $request = Request::create('/payment/webhook/wipay', 'POST', $payload);
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        app(PaymentCallbackController::class)->webhook($request, 'wipay');

        $afterBalance = app(TenantPayoutLedgerService::class)->balanceForTenant((string) $tenant->id);
        $afterLedgerCount = TenantPayoutLedgerEntry::where('tenant_id', $tenant->id)->count();
        $afterTransactionCount = PlatformTransaction::where('tenant_id', $tenant->id)->count();

        $ledgerEntry = TenantPayoutLedgerEntry::where('tenant_id', $tenant->id)
            ->latest()
            ->first();

        $passed = $afterTransactionCount === ($beforeTransactionCount + 1)
            && $afterLedgerCount === ($beforeLedgerCount + 1)
            && $ledgerEntry
            && (float) $ledgerEntry->tenant_net_amount > 0
            && (float) $afterBalance['available_balance'] > (float) $beforeBalance['available_balance'];

        add_payout_result($tenant->id, 'paid order credits payout ledger', $passed ? 'PASS' : 'FAIL', $passed
            ? 'Paid order created one platform transaction and one payout ledger credit.'
            : 'Paid order did not correctly create payout ledger credit.',
            [
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'before_ledger_count' => $beforeLedgerCount,
                'after_ledger_count' => $afterLedgerCount,
                'before_transaction_count' => $beforeTransactionCount,
                'after_transaction_count' => $afterTransactionCount,
                'ledger_entry_id' => $ledgerEntry?->id,
                'tenant_net_amount' => $ledgerEntry?->tenant_net_amount,
            ]
        );
    } catch (Throwable $e) {
        add_payout_result($tenant->id, 'payout ledger exception', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    } finally {
        try { DB::rollBack(); } catch (Throwable $ignored) {}
        try { DB::connection('mysql')->rollBack(); } catch (Throwable $ignored) {}

        tenancy()->end();
    }
}

$summary = [
    'PASS' => count(array_filter($results, fn ($result) => $result['status'] === 'PASS')),
    'FAIL' => count(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'failures' => array_values(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'warnings' => [],
    'all_findings' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
