<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$httpKernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$tests = [
    [
        'name' => 'Unauthenticated central dashboard',
        'method' => 'GET',
        'uri' => '/central',
        'expected' => 'redirect_to_login',
    ],
    [
        'name' => 'Unauthenticated central tenants',
        'method' => 'GET',
        'uri' => '/central/tenants',
        'expected' => 'redirect_to_login',
    ],
    [
        'name' => 'Unauthenticated central payment settings',
        'method' => 'GET',
        'uri' => '/central/payment-settings',
        'expected' => 'redirect_to_login',
    ],
    [
        'name' => 'Unauthenticated central payout account approve',
        'method' => 'POST',
        'uri' => '/central/payout-accounts/1/approve',
        'expected' => 'blocked',
    ],
    [
        'name' => 'Central login page public',
        'method' => 'GET',
        'uri' => '/central/login',
        'expected' => 'ok',
    ],
];

$results = [];

foreach ($tests as $test) {
    try {
        $request = Request::create($test['uri'], $test['method']);
        $request->headers->set('Accept', 'text/html');

        $response = $httpKernel->handle($request);

        $status = $response->getStatusCode();
        $location = $response->headers->get('Location');

        $passed = false;

        if ($test['expected'] === 'redirect_to_login') {
            $passed = in_array($status, [302, 303], true)
                && is_string($location)
                && str_contains($location, '/central/login');
        }

        if ($test['expected'] === 'blocked') {
            $passed = in_array($status, [302, 303, 401, 403, 419], true);
        }

        if ($test['expected'] === 'ok') {
            $passed = $status >= 200 && $status < 400;
        }

        $results[] = [
            'name' => $test['name'],
            'method' => $test['method'],
            'uri' => $test['uri'],
            'status' => $passed ? 'PASS' : 'FAIL',
            'http_status' => $status,
            'location' => $location,
            'expected' => $test['expected'],
        ];

        $httpKernel->terminate($request, $response);
    } catch (Throwable $e) {
        $results[] = [
            'name' => $test['name'],
            'method' => $test['method'],
            'uri' => $test['uri'],
            'status' => 'FAIL',
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ];
    }
}

$summary = [
    'PASS' => 0,
    'FAIL' => 0,
];

foreach ($results as $result) {
    $summary[$result['status']]++;
}

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'failures' => array_values(array_filter($results, fn ($item) => $item['status'] === 'FAIL')),
    'all_findings' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
