<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\Manage\CustomerController;
use App\Models\Order;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

$results = [];

function add_customer_management_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
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
        $indexRoute = Route::getRoutes()->getByName('customer.index');
        $showRoute = Route::getRoutes()->getByName('customer.show');

        add_customer_management_result($tenant->id, 'customer index route exists', $indexRoute ? 'PASS' : 'FAIL', $indexRoute
            ? 'Tenant customer index route exists.'
            : 'Tenant customer index route is missing.'
        );

        add_customer_management_result($tenant->id, 'customer show route exists', $showRoute ? 'PASS' : 'FAIL', $showRoute
            ? 'Tenant customer show route exists.'
            : 'Tenant customer show route is missing.'
        );

        $customerEmail = 'audit-customer-' . strtolower(substr(md5($tenant->id . microtime()), 0, 8)) . '@example.com';
        $customerName = 'Audit Customer ' . strtoupper(substr(md5($tenant->id . microtime()), 0, 6));

        $order = Order::create([
            'order_number' => 'S29-' . strtoupper(substr(md5($tenant->id . microtime()), 0, 10)),
            'total' => 125.50,
            'status' => 'completed',
            'notes' => 'Customer management audit.',
            'billing_name' => $customerName,
            'billing_email' => $customerEmail,
            'billing_phone' => '8681234567',
            'billing_address' => '123 Audit Customer Street',
            'billing_city' => 'San Fernando',
            'billing_state' => 'South',
            'billing_country' => 'TT',
            'billing_zipcode' => '00000',
            'shipping_name' => $customerName,
            'shipping_address' => '123 Audit Customer Street',
            'shipping_city' => 'San Fernando',
            'shipping_state' => 'South',
            'shipping_country' => 'TT',
            'shipping_zipcode' => '00000',
            'payment_method' => 'manual',
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        $request = Request::create('/manage/customer', 'GET', [
            'search' => $customerEmail,
        ]);

        $request->headers->set('X-Inertia', 'true');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        app()->instance('request', $request);

        $response = app(CustomerController::class)->index($request);
        $httpResponse = method_exists($response, 'toResponse') ? $response->toResponse($request) : $response;
        $payload = json_decode($httpResponse->getContent(), true);
        $props = $payload['props'] ?? [];

        $indexPassed = isset($props['customers'], $props['stats'], $props['filters'])
            && ($props['filters']['search'] ?? null) === $customerEmail;

        add_customer_management_result($tenant->id, 'customer index returns management props', $indexPassed ? 'PASS' : 'FAIL', $indexPassed
            ? 'Customer index returns customers, stats, and selected filters.'
            : 'Customer index missing expected management props.',
            [
                'props' => array_keys($props),
                'filters' => $props['filters'] ?? null,
            ]
        );

        $customerRows = $props['customers']['data'] ?? [];
        $foundCustomer = collect($customerRows)->contains(fn ($item) => strtolower((string) ($item['email'] ?? '')) === strtolower($customerEmail));

        add_customer_management_result($tenant->id, 'customer search returns expected customer', $foundCustomer ? 'PASS' : 'FAIL', $foundCustomer
            ? 'Customer search returned the expected customer.'
            : 'Customer search did not return the expected customer.',
            [
                'customer_email' => $customerEmail,
                'result_count' => count($customerRows),
            ]
        );

        $customerId = rawurlencode($customerEmail);

        $showRequest = Request::create('/manage/customer/' . $customerId, 'GET');
        $showRequest->headers->set('X-Inertia', 'true');
        $showRequest->headers->set('X-Requested-With', 'XMLHttpRequest');
        app()->instance('request', $showRequest);

        $showResponse = app(CustomerController::class)->show($customerId);
        $showHttpResponse = method_exists($showResponse, 'toResponse') ? $showResponse->toResponse($showRequest) : $showResponse;
        $showPayload = json_decode($showHttpResponse->getContent(), true);
        $showProps = $showPayload['props'] ?? [];

        $showPassed = isset($showProps['customer'], $showProps['orders'], $showProps['stats'])
            && strtolower((string) ($showProps['customer']['email'] ?? '')) === strtolower($customerEmail);

        add_customer_management_result($tenant->id, 'customer show returns profile props', $showPassed ? 'PASS' : 'FAIL', $showPassed
            ? 'Customer show returns customer, orders, and stats.'
            : 'Customer show missing expected profile props.',
            [
                'props' => array_keys($showProps),
                'customer_email' => $showProps['customer']['email'] ?? null,
                'stats' => $showProps['stats'] ?? null,
            ]
        );

        $showOrderRows = $showProps['orders']['data'] ?? [];
        $foundOrder = collect($showOrderRows)->contains(fn ($item) => (int) $item['id'] === (int) $order->id);

        add_customer_management_result($tenant->id, 'customer profile shows order history', $foundOrder ? 'PASS' : 'FAIL', $foundOrder
            ? 'Customer profile includes the expected order history.'
            : 'Customer profile did not include the expected order history.',
            [
                'order_id' => $order->id,
                'result_count' => count($showOrderRows),
            ]
        );
    } catch (Throwable $e) {
        add_customer_management_result($tenant->id, 'customer management exception', 'FAIL', $e->getMessage(), [
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
