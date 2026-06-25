<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$report = [];

function add_result(string $tenantId, string $section, string $name, string $status, string $message, array $data = []): void
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

function required_tables_exist(array $tables): bool
{
    foreach ($tables as $table) {
        if (! Schema::hasTable($table)) {
            return false;
        }
    }

    return true;
}

$requiredTables = [
    'users',
    'categories',
    'products',
    'carts',
    'cart_items',
    'orders',
    'order_items',
];

$tenants = \App\Models\Tenant::all();

foreach ($tenants as $tenant) {
    try {
        tenancy()->initialize($tenant);

        if (! required_tables_exist($requiredTables)) {
            add_result($tenant->id, 'tenant_actions', 'required_tables', 'FAIL', 'One or more required tenant tables are missing.', [
                'required_tables' => $requiredTables,
                'existing_tables' => collect($requiredTables)->filter(fn ($table) => Schema::hasTable($table))->values(),
                'missing_tables' => collect($requiredTables)->reject(fn ($table) => Schema::hasTable($table))->values(),
            ]);

            tenancy()->end();
            continue;
        }

        DB::beginTransaction();

        try {
            $user = \App\Models\User::query()->first();

            if (! $user) {
                $user = \App\Models\User::create([
                    'name' => 'Audit User',
                    'email' => 'audit-' . $tenant->id . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'admin',
                ]);
            }

            $category = \App\Models\Category::create([
                'name' => 'Audit Category ' . Str::random(5),
                'slug' => 'audit-category-' . Str::random(8),
                'description' => 'Temporary audit category.',
                'is_active' => true,
            ]);

            $product = \App\Models\Product::create([
                'name' => 'Audit Product ' . Str::random(5),
                'slug' => 'audit-product-' . Str::random(8),
                'description' => 'Temporary audit product.',
                'price' => 99.99,
                'stock' => 10,
                'is_active' => true,
                'sku' => 'AUDIT-' . strtoupper(Str::random(6)),
                'category_id' => $category->id,
            ]);

            $cart = \App\Models\Cart::create([
                'user_id' => $user->id,
                'session_id' => 'audit-session-' . Str::random(10),
            ]);

            $cartItem = \App\Models\CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'price' => $product->price,
            ]);

            $order = \App\Models\Order::create([
                'order_number' => 'AUDIT-' . strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'total' => 199.98,
                'status' => 'pending',
                'notes' => 'Temporary audit order.',
                'billing_name' => 'Audit Customer',
                'billing_email' => 'audit-customer@example.com',
                'billing_phone' => '1234567',
                'billing_address' => 'Audit Street',
                'billing_city' => 'Audit City',
                'billing_state' => 'Audit State',
                'billing_country' => 'TT',
                'billing_zipcode' => '00000',
                'shipping_name' => 'Audit Customer',
                'shipping_address' => 'Audit Street',
                'shipping_city' => 'Audit City',
                'shipping_state' => 'Audit State',
                'shipping_country' => 'TT',
                'shipping_zipcode' => '00000',
                'payment_method' => 'cash_on_delivery',
                'payment_status' => 'pending',
                'paid_at' => null,
            ]);

            $orderItem = \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => 2,
                'price' => $product->price,
                'subtotal' => 199.98,
            ]);

            $checks = [
                'user_created_or_found' => (bool) $user->id,
                'category_created' => (bool) $category->id,
                'product_created' => (bool) $product->id,
                'cart_created' => (bool) $cart->id,
                'cart_item_created' => (bool) $cartItem->id,
                'order_created' => (bool) $order->id,
                'order_item_created' => (bool) $orderItem->id,

                'product_category_relation' => $product->category?->id === $category->id,
                'cart_items_relation' => $cart->items()->count() >= 1,
                'cart_item_product_relation' => $cartItem->product?->id === $product->id,
                'order_items_relation' => $order->items()->count() >= 1,
                'order_item_product_relation' => $orderItem->product?->id === $product->id,
            ];

            $failedChecks = collect($checks)
                ->filter(fn ($passed) => $passed !== true)
                ->keys()
                ->values()
                ->all();

            if (count($failedChecks) > 0) {
                add_result($tenant->id, 'tenant_actions', 'create_shop_flow', 'FAIL', 'Tenant shop action flow had failed checks.', [
                    'checks' => $checks,
                    'failed_checks' => $failedChecks,
                ]);
            } else {
                add_result($tenant->id, 'tenant_actions', 'create_shop_flow', 'PASS', 'Tenant shop action flow works inside rollback.', [
                    'checks' => $checks,
                ]);
            }

            DB::rollBack();
            tenancy()->end();
        } catch (Throwable $e) {
            DB::rollBack();

            add_result($tenant->id, 'tenant_actions', 'create_shop_flow', 'FAIL', $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            tenancy()->end();
        }
    } catch (Throwable $e) {
        try {
            tenancy()->end();
        } catch (Throwable $ignored) {
        }

        add_result($tenant->id, 'tenant_actions', 'initialize', 'FAIL', $e->getMessage(), [
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
