<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\Manage\OrderController;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

$results = [];

function add_order_management_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
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

    try {
        $indexRoute = Route::getRoutes()->getByName('order.index');
        $showRoute = Route::getRoutes()->getByName('order.show');

        add_order_management_result($tenant->id, 'order index route exists', $indexRoute ? 'PASS' : 'FAIL', $indexRoute
            ? 'Tenant order index route exists.'
            : 'Tenant order index route is missing.'
        );

        add_order_management_result($tenant->id, 'order show route exists', $showRoute ? 'PASS' : 'FAIL', $showRoute
            ? 'Tenant order show route exists.'
            : 'Tenant order show route is missing.'
        );

        $order = Order::create([
            'order_number' => 'S28-' . strtoupper(substr(md5($tenant->id . microtime()), 0, 10)),
            'total' => 99.99,
            'status' => 'pending',
            'notes' => 'Order management audit.',
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
            'payment_method' => 'manual',
            'payment_status' => 'pending',
        ]);

        $request = Request::create('/manage/order', 'GET', [
            'search' => 'Audit Customer',
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        $request->headers->set('X-Inertia', 'true');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        app()->instance('request', $request);

        $response = app(OrderController::class)->index($request);
        $httpResponse = method_exists($response, 'toResponse') ? $response->toResponse($request) : $response;
        $payload = json_decode($httpResponse->getContent(), true);
        $props = $payload['props'] ?? [];

        $indexPassed = isset($props['orders'], $props['stats'], $props['filters'], $props['statusOptions'], $props['paymentStatusOptions'])
            && ($props['filters']['search'] ?? null) === 'Audit Customer';

        add_order_management_result($tenant->id, 'order index returns management props', $indexPassed ? 'PASS' : 'FAIL', $indexPassed
            ? 'Order index returns orders, stats, filters, and status options.'
            : 'Order index missing required management props.',
            [
                'props' => array_keys($props),
                'filters' => $props['filters'] ?? null,
            ]
        );

        $showRequest = Request::create('/manage/order/' . $order->id, 'GET');
        $showRequest->headers->set('X-Inertia', 'true');
        $showRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
        app()->instance('request', $showRequest);

        $showResponse = app(OrderController::class)->show($order);
        $showHttpResponse = method_exists($showResponse, 'toResponse') ? $showResponse->toResponse($showRequest) : $showResponse;
        $showPayload = json_decode($showHttpResponse->getContent(), true);
        $showProps = $showPayload['props'] ?? [];

        $showPassed = isset($showProps['order'], $showProps['statusOptions'], $showProps['paymentStatusOptions'])
            && (int) ($showProps['order']['id'] ?? 0) === (int) $order->id;

        add_order_management_result($tenant->id, 'order show returns detail props', $showPassed ? 'PASS' : 'FAIL', $showPassed
            ? 'Order show returns detail, status options, and payment status options.'
            : 'Order show missing expected detail props.',
            [
                'props' => array_keys($showProps),
                'order_id' => $showProps['order']['id'] ?? null,
            ]
        );

        $orderId = $order->id;

        $statusRequest = Request::create('/manage/order/' . $orderId . '/status', 'POST', [
            'status' => 'processing',
        ]);

        app()->instance('request', $statusRequest);

        $controller = app(OrderController::class);

        try {
            $controller->updateStatus($statusRequest, $order);
        } catch (TypeError|Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $controller->updateStatus($statusRequest, $orderId);
        }

        $paymentRequest = Request::create('/manage/order/' . $orderId . '/payment-status', 'POST', [
            'payment_status' => 'failed',
        ]);

        app()->instance('request', $paymentRequest);

        try {
            $controller->updatePaymentStatus($paymentRequest, $order->fresh());
        } catch (TypeError|Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $controller->updatePaymentStatus($paymentRequest, $orderId);
        }

        tenancy()->initialize($tenant);
        $order = Order::find($orderId);

        $updatePassed = $order && $order->status === 'processing' && $order->payment_status === 'failed';

        add_order_management_result($tenant->id, 'order status updates work', $updatePassed ? 'PASS' : 'FAIL', $updatePassed
            ? 'Order status and payment status update actions work.'
            : 'Order status and payment status update actions failed.',
            [
                'status' => $order?->status,
                'payment_status' => $order?->payment_status,
            ]
        );
    } catch (Throwable $e) {
        add_order_management_result($tenant->id, 'order management exception', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    } finally {
        try { DB::rollBack(); } catch (Throwable $ignored) {}
        try { tenancy()->end(); } catch (Throwable $ignored) {}
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
