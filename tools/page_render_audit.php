<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$report = [];

function add_page_result(string $section, string $name, string $status, string $message, array $data = []): void
{
    global $report;

    $report[] = [
        'section' => $section,
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

function make_request(string $method, string $uri): Request
{
    $request = Request::create($uri, $method);
    $request->headers->set('X-Inertia', 'true');
    $request->headers->set('Accept', 'text/html, application/xhtml+xml');

    app()->instance('request', $request);

    return $request;
}

function run_route_test(string $section, string $name, string $method, string $uri, ?string $tenantId = null): void
{
    try {
        if ($tenantId) {
            $tenant = \App\Models\Tenant::find($tenantId);

            if (! $tenant) {
                add_page_result($section, $name, 'FAIL', 'Tenant not found.', [
                    'tenant_id' => $tenantId,
                    'uri' => $uri,
                ]);
                return;
            }

            tenancy()->initialize($tenant);
        }

        $request = make_request($method, $uri);
        $route = Route::getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);

        $response = $route->run();

        if ($tenantId) {
            tenancy()->end();
        }

        $statusCode = method_exists($response, 'getStatusCode')
            ? $response->getStatusCode()
            : 200;

        $content = method_exists($response, 'getContent')
            ? $response->getContent()
            : '';

        $passed = $statusCode >= 200 && $statusCode < 400;

        add_page_result($section, $name, $passed ? 'PASS' : 'FAIL', $passed ? 'Page route rendered without server error.' : 'Page route returned bad status code.', [
            'method' => $method,
            'uri' => $uri,
            'tenant_id' => $tenantId,
            'status_code' => $statusCode,
            'content_preview' => is_string($content) ? substr($content, 0, 300) : null,
        ]);
    } catch (Throwable $e) {
        try {
            if ($tenantId) {
                tenancy()->end();
            }
        } catch (Throwable $ignored) {
        }

        add_page_result($section, $name, 'FAIL', $e->getMessage(), [
            'method' => $method,
            'uri' => $uri,
            'tenant_id' => $tenantId,
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}

$centralPages = [
    ['Central dashboard', 'GET', '/central'],
    ['Central tenants', 'GET', '/central/tenants'],
    ['Central tenant1 profile', 'GET', '/central/tenants/tenant1'],
    ['Central tenant1 payouts', 'GET', '/central/payouts/tenant1'],
    ['Central payouts index', 'GET', '/central/payouts'],
    ['Central payment settings', 'GET', '/central/payment-settings'],
    ['Central platform settings', 'GET', '/central/settings'],
];

foreach ($centralPages as [$name, $method, $uri]) {
    run_route_test('central_pages', $name, $method, $uri);
}

$tenantPages = [
    ['Tenant dashboard', 'GET', '/dashboard'],
    ['Tenant billing', 'GET', '/manage/billing'],
    ['Tenant payout account', 'GET', '/manage/payout-account'],
    ['Tenant store settings', 'GET', '/manage/store-settings'],
    ['Tenant products', 'GET', '/manage/product'],
    ['Tenant categories', 'GET', '/manage/category'],
    ['Tenant orders', 'GET', '/manage/order'],
    ['Tenant customers', 'GET', '/manage/customer'],
];

foreach (['tenant1', 'tenant2', 'awrah'] as $tenantId) {
    foreach ($tenantPages as [$name, $method, $uri]) {
        run_route_test('tenant_pages', $tenantId . ' ' . $name, $method, $uri, $tenantId);
    }
}

$summary = [
    'PASS' => 0,
    'FAIL' => 0,
    'WARN' => 0,
    'INFO' => 0,
];

foreach ($report as $item) {
    $summary[$item['status']] = ($summary[$item['status']] ?? 0) + 1;
}

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'failures' => array_values(array_filter($report, fn ($item) => $item['status'] === 'FAIL')),
    'warnings' => array_values(array_filter($report, fn ($item) => $item['status'] === 'WARN')),
    'all_findings' => $report,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
