<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\HomepageController;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

$results = [];

function add_storefront_filter_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
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
        $homeRoute = Route::getRoutes()->getByName('home');
        $productRoute = Route::getRoutes()->getByName('products.show');

        add_storefront_filter_result($tenant->id, 'home route exists', $homeRoute ? 'PASS' : 'FAIL', $homeRoute
            ? 'Storefront home route exists.'
            : 'Storefront home route could not be found.'
        );

        add_storefront_filter_result($tenant->id, 'product detail route exists', $productRoute ? 'PASS' : 'FAIL', $productRoute
            ? 'Product detail route exists.'
            : 'Product detail route could not be found.'
        );

        $category = Category::firstOrCreate(
            ['slug' => 'audit-filter-category'],
            [
                'name' => 'Audit Filter Category',
                'description' => 'Temporary audit category.',
                'is_active' => true,
            ]
        );

        $product = Product::create([
            'name' => 'Audit Search Product ' . strtoupper(substr(md5($tenant->id . microtime()), 0, 6)),
            'slug' => 'audit-search-product-' . strtolower(substr(md5($tenant->id . microtime()), 0, 8)),
            'description' => 'Temporary searchable audit product.',
            'price' => 12.34,
            'stock' => 5,
            'is_active' => true,
            'sku' => 'AUDIT-SKU-' . strtoupper(substr(md5(microtime()), 0, 6)),
            'category_id' => $category->id,
        ]);

        $request = Request::create('/home', 'GET', [
            'search' => 'Audit Search Product',
            'category' => $category->slug,
            'sort' => 'price_low',
        ]);

        $request->headers->set('X-Inertia', 'true');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        app()->instance('request', $request);

        $response = app()->call([app(HomepageController::class), 'index'], [
            'request' => $request,
        ]);

        $httpResponse = method_exists($response, 'toResponse')
            ? $response->toResponse($request)
            : $response;

        $payload = json_decode($httpResponse->getContent(), true);
        $props = $payload['props'] ?? [];

        $hasProducts = isset($props['products']);
        $hasCategories = isset($props['categories']);
        $hasFilters = isset($props['filters'])
            && ($props['filters']['search'] ?? null) === 'Audit Search Product'
            && ($props['filters']['category'] ?? null) === $category->slug
            && ($props['filters']['sort'] ?? null) === 'price_low';

        add_storefront_filter_result($tenant->id, 'home returns storefront props', ($hasProducts && $hasCategories && $hasFilters) ? 'PASS' : 'FAIL', ($hasProducts && $hasCategories && $hasFilters)
            ? 'Storefront home returns products, categories, and selected filters.'
            : 'Storefront home did not return expected search/filter props.',
            [
                'has_products' => $hasProducts,
                'has_categories' => $hasCategories,
                'filters' => $props['filters'] ?? null,
            ]
        );

        $productsData = $props['products'] ?? [];
        $productRows = $productsData['data'] ?? [];

        $found = collect($productRows)->contains(fn ($item) => (int) $item['id'] === (int) $product->id);

        add_storefront_filter_result($tenant->id, 'search and category filter returns product', $found ? 'PASS' : 'FAIL', $found
            ? 'Search and category filtering returned the expected product.'
            : 'Search and category filtering did not return the expected product.',
            [
                'product_id' => $product->id,
                'result_count' => count($productRows),
            ]
        );
    } catch (Throwable $e) {
        add_storefront_filter_result($tenant->id, 'storefront filter exception', 'FAIL', $e->getMessage(), [
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
