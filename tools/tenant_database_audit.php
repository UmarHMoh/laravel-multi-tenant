<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$report = [];

function add_tenant_result(string $tenantId, string $section, string $name, string $status, string $message, array $data = []): void
{
    global $report;

    $report[] = [
        'tenant_id' => $tenantId,
        'section' => $section,
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

function model_fillable(string $modelClass): array
{
    if (! class_exists($modelClass)) {
        return [];
    }

    try {
        $model = new $modelClass();

        return method_exists($model, 'getFillable') ? $model->getFillable() : [];
    } catch (Throwable $e) {
        return [];
    }
}

function check_table_columns(string $tenantId, string $table, array $requiredColumns): void
{
    if (! Schema::hasTable($table)) {
        add_tenant_result($tenantId, 'tables', $table, 'FAIL', 'Tenant table is missing.');
        return;
    }

    $columns = Schema::getColumnListing($table);
    $missing = array_values(array_diff($requiredColumns, $columns));

    if (count($missing) > 0) {
        add_tenant_result($tenantId, 'tables', $table, 'FAIL', 'Tenant table exists but required columns are missing.', [
            'missing_columns' => $missing,
            'actual_columns' => $columns,
        ]);
    } else {
        add_tenant_result($tenantId, 'tables', $table, 'PASS', 'Tenant table exists with required columns.', [
            'columns' => $columns,
        ]);
    }
}

function check_model_against_table(string $tenantId, string $modelName, string $modelClass, string $table): void
{
    if (! class_exists($modelClass)) {
        add_tenant_result($tenantId, 'models', $modelName, 'FAIL', 'Model class is missing.', [
            'class' => $modelClass,
        ]);
        return;
    }

    if (! Schema::hasTable($table)) {
        add_tenant_result($tenantId, 'models', $modelName, 'FAIL', 'Cannot check model because tenant table is missing.', [
            'table' => $table,
        ]);
        return;
    }

    $fillable = model_fillable($modelClass);
    $columns = Schema::getColumnListing($table);
    $missingColumns = array_values(array_diff($fillable, $columns));

    if (count($missingColumns) > 0) {
        add_tenant_result($tenantId, 'models', $modelName, 'FAIL', 'Model fillable contains fields missing from tenant table.', [
            'table' => $table,
            'fillable_missing_columns' => $missingColumns,
            'fillable' => $fillable,
            'columns' => $columns,
        ]);
    } else {
        add_tenant_result($tenantId, 'models', $modelName, 'PASS', 'Model fillable fields match tenant table columns.', [
            'table' => $table,
            'fillable' => $fillable,
            'columns' => $columns,
        ]);
    }
}

$requiredTenantTables = [
    'users' => ['id', 'name', 'email', 'password', 'created_at', 'updated_at'],
    'categories' => ['id', 'name', 'slug', 'description', 'is_active', 'created_at', 'updated_at'],
    'products' => ['id', 'name', 'slug', 'description', 'price', 'stock', 'is_active', 'sku', 'category_id', 'created_at', 'updated_at'],
    'carts' => ['id', 'user_id', 'session_id', 'created_at', 'updated_at'],
    'cart_items' => ['id', 'cart_id', 'product_id', 'quantity', 'price', 'created_at', 'updated_at'],
    'orders' => [
        'id',
        'order_number',
        'user_id',
        'total',
        'status',
        'notes',
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_zipcode',
        'shipping_name',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_zipcode',
        'payment_method',
        'payment_status',
        'paid_at',
        'created_at',
        'updated_at',
    ],
    'order_items' => ['id', 'order_id', 'product_id', 'product_name', 'quantity', 'price', 'subtotal', 'created_at', 'updated_at'],
];

$modelMap = [
    'User' => [\App\Models\User::class, 'users'],
    'Category' => [\App\Models\Category::class, 'categories'],
    'Product' => [\App\Models\Product::class, 'products'],
    'Cart' => [\App\Models\Cart::class, 'carts'],
    'CartItem' => [\App\Models\CartItem::class, 'cart_items'],
    'Order' => [\App\Models\Order::class, 'orders'],
    'OrderItem' => [\App\Models\OrderItem::class, 'order_items'],
];

$tenants = \App\Models\Tenant::all();

foreach ($tenants as $tenant) {
    try {
        tenancy()->initialize($tenant);

        add_tenant_result($tenant->id, 'tenant', 'initialize', 'PASS', 'Tenant initialized successfully.', [
            'tenant_name' => $tenant->name,
            'tenant_email' => $tenant->email,
            'database' => $tenant->tenancy_db_name ?? null,
        ]);

        foreach ($requiredTenantTables as $table => $columns) {
            check_table_columns($tenant->id, $table, $columns);
        }

        foreach ($modelMap as $modelName => [$class, $table]) {
            check_model_against_table($tenant->id, $modelName, $class, $table);
        }

        $userCount = Schema::hasTable('users') ? \App\Models\User::count() : 0;

        add_tenant_result($tenant->id, 'data', 'login_users', $userCount > 0 ? 'PASS' : 'WARN', $userCount > 0 ? 'Tenant has login users.' : 'Tenant has no login users.', [
            'count' => $userCount,
            'users' => Schema::hasTable('users')
                ? \App\Models\User::query()->get(['id', 'name', 'email', 'created_at'])->map(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at?->toDateTimeString(),
                ])->values()
                : [],
        ]);

        $counts = [];

        foreach (array_keys($requiredTenantTables) as $table) {
            $counts[$table] = Schema::hasTable($table)
                ? \Illuminate\Support\Facades\DB::table($table)->count()
                : null;
        }

        add_tenant_result($tenant->id, 'data', 'table_counts', 'INFO', 'Tenant table counts collected.', $counts);

        tenancy()->end();
    } catch (Throwable $e) {
        try {
            tenancy()->end();
        } catch (Throwable $ignored) {
        }

        add_tenant_result($tenant->id, 'tenant', 'initialize', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
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
    'tenant_count' => $tenants->count(),
    'failures' => array_values(array_filter($report, fn ($item) => $item['status'] === 'FAIL')),
    'warnings' => array_values(array_filter($report, fn ($item) => $item['status'] === 'WARN')),
    'all_findings' => $report,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
