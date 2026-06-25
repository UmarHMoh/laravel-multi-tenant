<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\Manage\ProductController;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

$results = [];

function add_product_inventory_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
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
        $indexRoute = Route::getRoutes()->getByName('product.index');
        $createRoute = Route::getRoutes()->getByName('product.create');

        add_product_inventory_result($tenant->id, 'product index route exists', $indexRoute ? 'PASS' : 'FAIL', $indexRoute
            ? 'Tenant product index route exists.'
            : 'Tenant product index route is missing.'
        );

        add_product_inventory_result($tenant->id, 'product create route exists', $createRoute ? 'PASS' : 'FAIL', $createRoute
            ? 'Tenant product create route exists.'
            : 'Tenant product create route is missing.'
        );

        $category = Category::firstOrCreate(
            ['name' => 'Inventory Audit Category'],
            [
                'slug' => 'inventory-audit-category',
                'description' => 'Temporary inventory audit category.',
                'is_active' => true,
            ]
        );

        $lowStockProduct = Product::create([
            'name' => 'Inventory Low Stock ' . strtoupper(substr(md5($tenant->id . microtime()), 0, 6)),
            'slug' => 'inventory-low-stock-' . strtolower(substr(md5($tenant->id . microtime()), 0, 8)),
            'description' => 'Temporary low stock audit product.',
            'price' => 25.00,
            'stock' => 3,
            'is_active' => true,
            'sku' => 'LOW-' . strtoupper(substr(md5(microtime()), 0, 6)),
            'category_id' => $category->id,
        ]);

        $outStockProduct = Product::create([
            'name' => 'Inventory Out Stock ' . strtoupper(substr(md5($tenant->id . microtime()), 0, 6)),
            'slug' => 'inventory-out-stock-' . strtolower(substr(md5($tenant->id . microtime()), 0, 8)),
            'description' => 'Temporary out of stock audit product.',
            'price' => 35.00,
            'stock' => 0,
            'is_active' => true,
            'sku' => 'OUT-' . strtoupper(substr(md5(microtime()), 0, 6)),
            'category_id' => $category->id,
        ]);

        $request = Request::create('/manage/product', 'GET', [
            'search' => 'Inventory',
            'stock_status' => 'low',
        ]);

        $request->headers->set('X-Inertia', 'true');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        app()->instance('request', $request);

        $response = app(ProductController::class)->index($request);
        $httpResponse = method_exists($response, 'toResponse') ? $response->toResponse($request) : $response;
        $payload = json_decode($httpResponse->getContent(), true);
        $props = $payload['props'] ?? [];

        $hasProps = isset($props['products'], $props['stats'], $props['filters'], $props['stockStatusOptions'])
            && ($props['filters']['stock_status'] ?? null) === 'low';

        add_product_inventory_result($tenant->id, 'product index returns inventory props', $hasProps ? 'PASS' : 'FAIL', $hasProps
            ? 'Product index returns products, stats, filters, and stock status options.'
            : 'Product index missing expected inventory props.',
            [
                'props' => array_keys($props),
                'filters' => $props['filters'] ?? null,
            ]
        );

        $productRows = $props['products']['data'] ?? [];
        $foundLowStock = collect($productRows)->contains(fn ($item) => (int) $item['id'] === (int) $lowStockProduct->id);
        $foundOutStock = collect($productRows)->contains(fn ($item) => (int) $item['id'] === (int) $outStockProduct->id);

        add_product_inventory_result($tenant->id, 'low stock filter returns low-stock product', $foundLowStock ? 'PASS' : 'FAIL', $foundLowStock
            ? 'Low stock filter returned the expected product.'
            : 'Low stock filter did not return the expected product.',
            [
                'low_stock_product_id' => $lowStockProduct->id,
                'result_count' => count($productRows),
            ]
        );

        add_product_inventory_result($tenant->id, 'low stock filter excludes out-of-stock product', ! $foundOutStock ? 'PASS' : 'FAIL', ! $foundOutStock
            ? 'Low stock filter excluded the out-of-stock product.'
            : 'Low stock filter incorrectly included the out-of-stock product.',
            [
                'out_stock_product_id' => $outStockProduct->id,
                'result_count' => count($productRows),
            ]
        );
    } catch (Throwable $e) {
        add_product_inventory_result($tenant->id, 'product inventory exception', 'FAIL', $e->getMessage(), [
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
