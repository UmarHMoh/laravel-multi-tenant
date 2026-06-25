<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\PaymentCallbackController;
use App\Models\Order;
use App\Models\PlatformTransaction;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$results = [];

function add_payment_verification_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
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
            'order_number' => 'VERIFY-' . strtoupper(substr(md5($tenant->id . microtime()), 0, 10)),
            'user_id' => $user?->id,
            'total' => 100,
            'status' => 'pending',
            'notes' => 'Payment verification audit order.',
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
            'payment_metadata' => [
                'currency' => 'TTD',
            ],
        ]);

        $payload = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => 'paid',
            'payment_id' => 'PAY-' . $order->id,
            'reference' => 'REF-' . $order->id,
        ];

        $request = Request::create('/payment/webhook/wipay', 'POST', $payload);
        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        $platformCountBeforeWebhook = PlatformTransaction::where('tenant_id', $tenant->id)->count();

        app(PaymentCallbackController::class)->webhook($request, 'wipay');

        $fresh = $order->fresh();

        $passed = $fresh
            && $fresh->payment_status === 'paid'
            && $fresh->status === 'processing'
            && $fresh->paid_at
            && $fresh->provider_payment_id === 'PAY-' . $order->id
            && $fresh->provider_reference === 'REF-' . $order->id;

        add_payment_verification_result($tenant->id, 'webhook marks order paid', $passed ? 'PASS' : 'FAIL', $passed
            ? 'Webhook payload marked order as paid and stored provider references.'
            : 'Webhook payload did not correctly mark order paid.',
            [
                'order_id' => $fresh?->id,
                'payment_status' => $fresh?->payment_status,
                'status' => $fresh?->status,
                'paid_at' => $fresh?->paid_at?->toDateTimeString(),
                'provider_payment_id' => $fresh?->provider_payment_id,
                'provider_reference' => $fresh?->provider_reference,
            ]
        );

        $platformCountAfterFirstWebhook = PlatformTransaction::where('tenant_id', $tenant->id)->count();

        $duplicateRequest = Request::create('/payment/webhook/wipay', 'POST', $payload);
        $duplicateRequest->setLaravelSession(app('session.store'));
        app()->instance('request', $duplicateRequest);

        app(PaymentCallbackController::class)->webhook($duplicateRequest, 'wipay');

        $platformCountAfterSecondWebhook = PlatformTransaction::where('tenant_id', $tenant->id)->count();

        $duplicatePassed = $platformCountAfterFirstWebhook === ($platformCountBeforeWebhook + 1)
            && $platformCountAfterSecondWebhook === $platformCountAfterFirstWebhook;

        add_payment_verification_result($tenant->id, 'duplicate webhook is idempotent', $duplicatePassed ? 'PASS' : 'FAIL', $duplicatePassed
            ? 'Duplicate paid webhook did not create an extra platform transaction.'
            : 'Duplicate paid webhook created duplicate platform transaction records.',
            [
                'platform_count_before_webhook' => $platformCountBeforeWebhook,
                'platform_count_after_first_webhook' => $platformCountAfterFirstWebhook,
                'platform_count_after_second_webhook' => $platformCountAfterSecondWebhook,
            ]
        );
    } catch (Throwable $e) {
        add_payment_verification_result($tenant->id, 'payment verification exception', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    } finally {
        try {
            DB::rollBack();
        } catch (Throwable $ignored) {}

        try {
            DB::connection('mysql')->rollBack();
        } catch (Throwable $ignored) {}

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
