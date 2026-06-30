<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$report = [];

$intentionalPublicWriteRoutes = [
    'contact.store',
];

function add_security_result(string $section, string $name, string $status, string $message, array $data = []): void
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

function normalize_uri(string $uri): string
{
    $uri = '/' . trim($uri, '/');

    return $uri === '/' ? '/' : rtrim($uri, '/');
}

$bootstrapApp = file_exists(base_path('bootstrap/app.php'))
    ? file_get_contents(base_path('bootstrap/app.php'))
    : '';

$hasGlobalCentralProtection = str_contains($bootstrapApp, 'EnsureCentralAdminAuthenticated');

$routes = [];

foreach (Route::getRoutes() as $route) {
    $methods = array_values(array_filter($route->methods(), fn ($method) => $method !== 'HEAD'));

    $routes[] = [
        'uri' => normalize_uri($route->uri()),
        'name' => $route->getName(),
        'methods' => $methods,
        'middleware' => $route->gatherMiddleware(),
        'action' => $route->getActionName(),
    ];
}

$publicAllowedPrefixes = [
    '/',
    '/login',
    '/register',
    '/forgot-password',
    '/reset-password',
    '/verify-email',
    '/confirm-password',
    '/home',
    '/product',
    '/cart',
    '/checkout',
    '/order-confirmation',
    '/orders',
    '/central/login',
];

$dangerousMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

foreach ($routes as $route) {
    $uri = $route['uri'];
    $name = $route['name'] ?? '';
    $methods = $route['methods'];
    $middleware = $route['middleware'];

    $isCentral = str_starts_with($uri, '/central');
    $isTenantManage = str_starts_with($uri, '/manage');
    $isTenantDashboard = $uri === '/dashboard';
    $isSettings = str_starts_with($uri, '/settings');
    $isDangerous = count(array_intersect($methods, $dangerousMethods)) > 0;

    $hasAuth = in_array('auth', $middleware, true);
    $hasCentralAuth = in_array('central.auth', $middleware, true) || in_array('central', $middleware, true);
    $hasThrottle = collect($middleware)->contains(fn ($m) => str_starts_with((string) $m, 'throttle:'));
    $hasTenantMiddleware = collect($middleware)->contains(fn ($m) =>
        str_contains((string) $m, 'tenant')
        || str_contains((string) $m, 'InitializeTenancy')
        || str_contains((string) $m, 'PreventAccessFromCentralDomains')
    );

    /*
    |--------------------------------------------------------------------------
    | Central route checks
    |--------------------------------------------------------------------------
    */

    if ($isCentral && $uri !== '/central/login' && $uri !== '/central/logout') {
        if (! $hasCentralAuth && ! $hasAuth && ! $hasGlobalCentralProtection) {
            add_security_result('central_routes', $uri, 'FAIL', 'Central route may not be protected by auth middleware.', $route);
        } else {
            add_security_result('central_routes', $uri, 'PASS', $hasGlobalCentralProtection
                ? 'Central route protected by global central admin middleware.'
                : 'Central route appears protected.',
                $route
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Tenant management route checks
    |--------------------------------------------------------------------------
    */

    if ($isTenantManage || $isTenantDashboard || $isSettings) {
        if (! $hasAuth) {
            add_security_result('tenant_routes', $uri, 'FAIL', 'Tenant management/settings route may not be protected by auth middleware.', $route);
        } else {
            add_security_result('tenant_routes', $uri, 'PASS', 'Tenant management/settings route appears protected.', $route);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Dangerous method checks
    |--------------------------------------------------------------------------
    */

    if ($isDangerous) {
        $isPublicAllowed = false;

        foreach ($publicAllowedPrefixes as $prefix) {
            if ($uri === $prefix || str_starts_with($uri, $prefix . '/')) {
                $isPublicAllowed = true;
                break;
            }
        }

        $isIntentionalPublicWrite = in_array($name, $intentionalPublicWriteRoutes, true)
            && $hasTenantMiddleware
            && $hasThrottle;

        $protected = $hasAuth
            || $hasCentralAuth
            || ($isCentral && $hasGlobalCentralProtection)
            || $isIntentionalPublicWrite;

        if (! $protected && ! $isPublicAllowed) {
            add_security_result('dangerous_routes', $uri, 'FAIL', 'POST/PUT/PATCH/DELETE route may be unprotected.', $route);
        } else {
            add_security_result('dangerous_routes', $uri, 'PASS', 'Dangerous method route appears acceptable/protected.', $route);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Central login should be public
    |--------------------------------------------------------------------------
    */

    if ($uri === '/central/login') {
        add_security_result('central_auth', $uri, 'PASS', 'Central login route is present.', $route);
    }
}

/*
|--------------------------------------------------------------------------
| Required route protection contracts
|--------------------------------------------------------------------------
*/

$requiredProtected = [
    '/central',
    '/central/tenants',
    '/central/payouts',
    '/central/payment-settings',
    '/central/settings',
    '/dashboard',
    '/manage/billing',
    '/manage/payout-account',
    '/manage/store-settings',
    '/manage/product',
    '/manage/category',
    '/manage/order',
    '/manage/customer',
];

foreach ($requiredProtected as $requiredUri) {
    $matches = array_values(array_filter($routes, fn ($route) => $route['uri'] === $requiredUri));

    if (count($matches) === 0) {
        add_security_result('required_contracts', $requiredUri, 'FAIL', 'Required protected route does not exist.');
        continue;
    }

    foreach ($matches as $match) {
        $middleware = $match['middleware'];

        $protected = in_array('auth', $middleware, true)
            || in_array('central.auth', $middleware, true)
            || in_array('central', $middleware, true)
            || (str_starts_with($requiredUri, '/central') && $hasGlobalCentralProtection);

        add_security_result('required_contracts', $requiredUri, $protected ? 'PASS' : 'FAIL', $protected
            ? 'Required route has auth protection.'
            : 'Required route does not appear to have auth protection.',
            $match
        );
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
