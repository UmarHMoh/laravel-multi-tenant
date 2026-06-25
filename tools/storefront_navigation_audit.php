<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

$results = [];

function add_nav_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = compact('tenantId', 'name', 'status', 'message', 'data');
    $results[array_key_last($results)]['tenant_id'] = $tenantId;
    unset($results[array_key_last($results)]['tenantId']);
}

$requiredRoutes = [
    'home' => 'Storefront home route exists.',
    'cart.index' => 'Cart route exists.',
    'orders.customer.index' => 'Customer order history route exists.',
    'orders.confirmation' => 'Order confirmation route exists.',
    'login' => 'Login route exists.',
    'logout' => 'Logout route exists.',
];

$requiredFiles = [
    'resources/js/components/tenant/StorefrontHeader.vue' => 'Shared storefront header exists.',
    'resources/js/pages/tenant/Homepage.vue' => 'Homepage exists.',
    'resources/js/pages/tenant/ProductDetail.vue' => 'Product detail page exists.',
    'resources/js/pages/tenant/ShoppingCartList.vue' => 'Cart page exists.',
    'resources/js/pages/tenant/Payment.vue' => 'Checkout/payment page exists.',
    'resources/js/pages/tenant/OrderConfirmation.vue' => 'Order confirmation page exists.',
    'resources/js/pages/tenant/orders/CustomerIndex.vue' => 'Customer order history page exists.',
];

foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);

    foreach ($requiredRoutes as $route => $message) {
        add_nav_result(
            $tenant->id,
            "route {$route}",
            Route::getRoutes()->getByName($route) ? 'PASS' : 'FAIL',
            Route::getRoutes()->getByName($route) ? $message : "Missing route {$route}."
        );
    }

    foreach ($requiredFiles as $file => $message) {
        add_nav_result(
            $tenant->id,
            "file {$file}",
            file_exists(base_path($file)) ? 'PASS' : 'FAIL',
            file_exists(base_path($file)) ? $message : "Missing file {$file}."
        );
    }

    $header = file_get_contents(base_path('resources/js/components/tenant/StorefrontHeader.vue'));

    foreach (['Cart', 'My Orders', 'Login', 'Logout'] as $needle) {
        add_nav_result(
            $tenant->id,
            "header contains {$needle}",
            str_contains($header, $needle) ? 'PASS' : 'FAIL',
            str_contains($header, $needle) ? "Header contains {$needle}." : "Header missing {$needle}."
        );
    }

    tenancy()->end();
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
