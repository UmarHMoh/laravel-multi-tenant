<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\PaymentCallbackController;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\TenantPayoutBatch;
use App\Models\TenantPayoutLedgerEntry;
use App\Models\TenantPayoutRequest;
use App\Services\Payments\TenantPayoutLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$results = [];

function add_payout_batch_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
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
        $service = app(TenantPayoutLedgerService::class);
        $user = \App\Models\User::first();

        $createdRequests = [];

        for ($i = 1; $i <= 2; $i++) {
            $order = Order::create([
                'order_number' => 'BATCH-' . $i . '-' . strtoupper(substr(md5($tenant->id . microtime()), 0, 10)),
                'user_id' => $user?->id,
                'total' => 100,
                'status' => 'pending',
                'notes' => 'Payout batch audit order.',
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
                'payment_id' => 'BATCH-PAY-' . $order->id,
                'reference' => 'BATCH-REF-' . $order->id,
            ];

            $webhook = Request::create('/payment/webhook/wipay', 'POST', $payload);
            $webhook->setLaravelSession(app('session.store'));
            app()->instance('request', $webhook);

            app(PaymentCallbackController::class)->webhook($webhook, 'wipay');

            $balance = $service->balanceForTenant((string) $tenant->id);
            $amount = round(((float) $balance['available_balance']) / 4, 2);

            $payoutRequest = $service->createPayoutRequest(
                tenantId: (string) $tenant->id,
                amount: $amount,
                currency: $balance['currency'] ?? 'TTD',
                notes: 'Batch audit request.'
            );

            $createdRequests[] = $service->approvePayoutRequest($payoutRequest, 'Batch audit approved.');
        }

        $beforeBalance = $service->balanceForTenant((string) $tenant->id);
        $beforeDebitCount = TenantPayoutLedgerEntry::where('tenant_id', $tenant->id)
            ->where('entry_type', 'debit')
            ->count();

        $batch = $service->createPayoutBatch(
            payoutRequestIds: collect($createdRequests)->pluck('id')->all(),
            notes: 'Audit payout batch.'
        );

        $batchPassed = $batch
            && $batch->status === 'approved'
            && $batch->request_count === 2
            && (float) $batch->total_amount > 0
            && TenantPayoutRequest::where('tenant_payout_batch_id', $batch->id)->count() === 2;

        add_payout_batch_result($tenant->id, 'central can create payout batch', $batchPassed ? 'PASS' : 'FAIL', $batchPassed
            ? 'Approved payout requests were grouped into a payout batch.'
            : 'Payout batch was not created correctly.',
            [
                'batch_id' => $batch?->id,
                'batch_number' => $batch?->batch_number,
                'request_count' => $batch?->request_count,
                'total_amount' => $batch?->total_amount,
            ]
        );

        $paidBatch = $service->markPayoutBatchPaid($batch, 'Batch audit paid.');
        $afterBalance = $service->balanceForTenant((string) $tenant->id);
        $afterDebitCount = TenantPayoutLedgerEntry::where('tenant_id', $tenant->id)
            ->where('entry_type', 'debit')
            ->count();

        $requestsPaid = TenantPayoutRequest::where('tenant_payout_batch_id', $batch->id)
            ->where('status', 'paid')
            ->count();

        $paidPassed = $paidBatch->status === 'paid'
            && $requestsPaid === 2
            && $afterDebitCount === ($beforeDebitCount + 2)
            && (float) $afterBalance['available_balance'] === round((float) $beforeBalance['available_balance'] - (float) $batch->total_amount, 2);

        add_payout_batch_result($tenant->id, 'paid batch pays requests and reduces balance', $paidPassed ? 'PASS' : 'FAIL', $paidPassed
            ? 'Marking payout batch paid paid all requests and reduced tenant balance.'
            : 'Payout batch payment did not correctly update requests, debits, or balance.',
            [
                'before_balance' => $beforeBalance,
                'after_balance' => $afterBalance,
                'batch_total' => $batch->total_amount,
                'before_debit_count' => $beforeDebitCount,
                'after_debit_count' => $afterDebitCount,
                'requests_paid' => $requestsPaid,
                'batch_status' => $paidBatch->status,
            ]
        );
    } catch (Throwable $e) {
        add_payout_batch_result($tenant->id, 'payout batch exception', 'FAIL', $e->getMessage(), [
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
