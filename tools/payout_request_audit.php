<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\PaymentCallbackController;
use App\Models\Order;
use App\Models\PlatformTransaction;
use App\Models\Tenant;
use App\Models\TenantPayoutLedgerEntry;
use App\Models\TenantPayoutRequest;
use App\Services\Payments\TenantPayoutLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$results = [];

function add_payout_request_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'tenant_id' => $tenantId,
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);

    DB::beginTransaction();
    DB::connection('mysql')->beginTransaction();

    try {
        $user = \App\Models\User::first();

        $order = Order::create([
            'order_number' => 'REQ-' . strtoupper(substr(md5($tenant->id . microtime()), 0, 10)),
            'user_id' => $user?->id,
            'total' => 100,
            'status' => 'pending',
            'notes' => 'Payout request audit order.',
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
            'payment_id' => 'REQ-PAY-' . $order->id,
            'reference' => 'REQ-REF-' . $order->id,
        ];

        $request = Request::create('/payment/webhook/wipay', 'POST', $payload);
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        app(PaymentCallbackController::class)->webhook($request, 'wipay');

        $service = app(TenantPayoutLedgerService::class);
        $balanceAfterOrder = $service->balanceForTenant((string) $tenant->id);

        $requestAmount = round(((float) $balanceAfterOrder['available_balance']) / 2, 2);

        $payoutRequest = $service->createPayoutRequest(
            tenantId: (string) $tenant->id,
            amount: $requestAmount,
            currency: $balanceAfterOrder['currency'] ?? 'TTD',
            notes: 'Audit payout request.'
        );

        $createdPassed = $payoutRequest
            && $payoutRequest->status === 'requested'
            && (float) $payoutRequest->amount === (float) $requestAmount;

        add_payout_request_result($tenant->id, 'tenant can create payout request within balance', $createdPassed ? 'PASS' : 'FAIL', $createdPassed
            ? 'Payout request was created within available balance.'
            : 'Payout request was not created correctly.',
            [
                'available_balance' => $balanceAfterOrder,
                'request_amount' => $requestAmount,
                'payout_request_id' => $payoutRequest?->id,
                'status' => $payoutRequest?->status,
            ]
        );

        $excessBlocked = false;

        try {
            $service->createPayoutRequest(
                tenantId: (string) $tenant->id,
                amount: ((float) $balanceAfterOrder['available_balance']) + 999,
                currency: $balanceAfterOrder['currency'] ?? 'TTD',
                notes: 'Audit excessive payout request.'
            );
        } catch (Throwable $e) {
            $excessBlocked = str_contains($e->getMessage(), 'exceeds available balance');
        }

        add_payout_request_result($tenant->id, 'payout request cannot exceed available balance', $excessBlocked ? 'PASS' : 'FAIL', $excessBlocked
            ? 'Excess payout request was blocked.'
            : 'Excess payout request was not blocked.'
        );

        $beforePaidBalance = $service->balanceForTenant((string) $tenant->id);
        $beforeLedgerCount = TenantPayoutLedgerEntry::where('tenant_id', $tenant->id)->count();

        $service->approvePayoutRequest($payoutRequest, 'Audit approved.');
        $paidRequest = $service->markPayoutRequestPaid($payoutRequest->fresh(), 'Audit paid.');

        $afterPaidBalance = $service->balanceForTenant((string) $tenant->id);
        $afterLedgerCount = TenantPayoutLedgerEntry::where('tenant_id', $tenant->id)->count();

        $paidPassed = $paidRequest->status === 'paid'
            && $paidRequest->tenant_payout_ledger_entry_id
            && $afterLedgerCount === ($beforeLedgerCount + 1)
            && (float) $afterPaidBalance['available_balance'] === round((float) $beforePaidBalance['available_balance'] - $requestAmount, 2);

        add_payout_request_result($tenant->id, 'paid payout creates debit and reduces balance', $paidPassed ? 'PASS' : 'FAIL', $paidPassed
            ? 'Marking payout paid created a debit and reduced available balance.'
            : 'Paid payout did not correctly reduce balance.',
            [
                'before_paid_balance' => $beforePaidBalance,
                'after_paid_balance' => $afterPaidBalance,
                'request_amount' => $requestAmount,
                'before_ledger_count' => $beforeLedgerCount,
                'after_ledger_count' => $afterLedgerCount,
                'payout_request_status' => $paidRequest->status,
                'tenant_payout_ledger_entry_id' => $paidRequest->tenant_payout_ledger_entry_id,
            ]
        );
    } catch (Throwable $e) {
        add_payout_request_result($tenant->id, 'payout request exception', 'FAIL', $e->getMessage(), [
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
