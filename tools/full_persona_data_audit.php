<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$report = [];

function add_persona_result(string $persona, string $tenantId, string $action, string $status, string $message, array $data = []): void
{
    global $report;

    $report[] = [
        'persona' => $persona,
        'tenant_id' => $tenantId,
        'action' => $action,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

function required_tenant_tables_exist(array $tables): array
{
    $missing = [];

    foreach ($tables as $table) {
        if (! Schema::hasTable($table)) {
            $missing[] = $table;
        }
    }

    return $missing;
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

        $missingTables = required_tenant_tables_exist($requiredTables);

        if (count($missingTables) > 0) {
            add_persona_result('system', $tenant->id, 'tenant database readiness', 'FAIL', 'Tenant is missing required shop tables.', [
                'missing_tables' => $missingTables,
            ]);

            tenancy()->end();
            continue;
        }

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Persona 1: Store owner creates user/category/product
            |--------------------------------------------------------------------------
            */

            $owner = \App\Models\User::query()->first();

            if (! $owner) {
                $owner = \App\Models\User::create([
                    'name' => 'Persona Store Owner',
                    'email' => 'persona-owner-' . $tenant->id . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'admin',
                ]);
            }

            add_persona_result('store_owner', $tenant->id, 'find or create store owner', $owner->id ? 'PASS' : 'FAIL', 'Store owner account exists.', [
                'user_id' => $owner->id,
                'email' => $owner->email,
            ]);

            $category = \App\Models\Category::create([
                'name' => 'Persona Category ' . Str::random(5),
                'slug' => 'persona-category-' . Str::random(8),
                'description' => 'Persona test category.',
                'is_active' => true,
            ]);

            add_persona_result('store_owner', $tenant->id, 'create category', $category->id ? 'PASS' : 'FAIL', 'Store owner created category.', [
                'category_id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]);

            $product = \App\Models\Product::create([
                'name' => 'Persona Product ' . Str::random(5),
                'slug' => 'persona-product-' . Str::random(8),
                'description' => 'Persona test product.',
                'price' => 150.00,
                'stock' => 20,
                'is_active' => true,
                'sku' => 'PERSONA-' . strtoupper(Str::random(6)),
                'category_id' => $category->id,
            ]);

            add_persona_result('store_owner', $tenant->id, 'create product', $product->id ? 'PASS' : 'FAIL', 'Store owner created product.', [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'stock' => $product->stock,
                'category_id' => $product->category_id,
            ]);

            $productCategoryWorks = $product->category && $product->category->id === $category->id;

            add_persona_result('store_owner', $tenant->id, 'product category relation', $productCategoryWorks ? 'PASS' : 'FAIL', 'Product loads its category correctly.', [
                'product_id' => $product->id,
                'expected_category_id' => $category->id,
                'actual_category_id' => $product->category?->id,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Persona 2: Store owner edits product/category
            |--------------------------------------------------------------------------
            */

            $category->update([
                'name' => 'Updated Persona Category',
                'is_active' => true,
            ]);

            $product->update([
                'name' => 'Updated Persona Product',
                'price' => 175.50,
                'stock' => 15,
                'is_active' => true,
            ]);

            $freshProduct = $product->fresh();

            $editPassed = $freshProduct->name === 'Updated Persona Product'
                && (float) $freshProduct->price === 175.50
                && (int) $freshProduct->stock === 15;

            add_persona_result('store_owner', $tenant->id, 'edit product and category', $editPassed ? 'PASS' : 'FAIL', 'Store owner can update product/category data.', [
                'product_name' => $freshProduct->name,
                'product_price' => $freshProduct->price,
                'product_stock' => $freshProduct->stock,
                'category_name' => $category->fresh()?->name,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Persona 3: Customer adds product to cart
            |--------------------------------------------------------------------------
            */

            $customer = \App\Models\User::create([
                'name' => 'Persona Customer',
                'email' => 'persona-customer-' . Str::random(8) . '@example.com',
                'password' => bcrypt('password123'),
                'role' => 'customer',
            ]);

            $cart = \App\Models\Cart::create([
                'user_id' => $customer->id,
                'session_id' => 'persona-session-' . Str::random(12),
            ]);

            $cartItem = \App\Models\CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $freshProduct->id,
                'quantity' => 2,
                'price' => $freshProduct->price,
            ]);

            $cartPassed = $cart->items()->count() === 1
                && $cartItem->product
                && $cartItem->product->id === $freshProduct->id;

            add_persona_result('customer', $tenant->id, 'add product to cart', $cartPassed ? 'PASS' : 'FAIL', 'Customer can add product to cart and relation loads.', [
                'customer_id' => $customer->id,
                'cart_id' => $cart->id,
                'cart_item_id' => $cartItem->id,
                'product_id' => $freshProduct->id,
                'quantity' => $cartItem->quantity,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Persona 4: Customer changes cart quantity
            |--------------------------------------------------------------------------
            */

            $cartItem->update([
                'quantity' => 3,
            ]);

            $quantityPassed = (int) $cartItem->fresh()->quantity === 3;

            add_persona_result('customer', $tenant->id, 'update cart quantity', $quantityPassed ? 'PASS' : 'FAIL', 'Customer cart quantity updates correctly.', [
                'cart_item_id' => $cartItem->id,
                'quantity' => $cartItem->fresh()->quantity,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Persona 5: Customer places order
            |--------------------------------------------------------------------------
            */

            $subtotal = 3 * (float) $freshProduct->price;

            $order = \App\Models\Order::create([
                'order_number' => 'PERSONA-' . strtoupper(Str::random(10)),
                'user_id' => $customer->id,
                'total' => $subtotal,
                'status' => 'pending',
                'notes' => 'Persona checkout test order.',
                'billing_name' => 'Persona Customer',
                'billing_email' => $customer->email,
                'billing_phone' => '8681234567',
                'billing_address' => '123 Persona Street',
                'billing_city' => 'San Fernando',
                'billing_state' => 'South',
                'billing_country' => 'TT',
                'billing_zipcode' => '00000',
                'shipping_name' => 'Persona Customer',
                'shipping_address' => '123 Persona Street',
                'shipping_city' => 'San Fernando',
                'shipping_state' => 'South',
                'shipping_country' => 'TT',
                'shipping_zipcode' => '00000',
                'payment_method' => 'Cash on Delivery',
                'payment_status' => 'pending',
                'paid_at' => null,
            ]);

            $orderItem = \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $freshProduct->id,
                'product_name' => $freshProduct->name,
                'quantity' => 3,
                'price' => $freshProduct->price,
                'subtotal' => $subtotal,
            ]);

            $orderPassed = $order->items()->count() === 1
                && $orderItem->product
                && $orderItem->product->id === $freshProduct->id
                && (float) $order->total === $subtotal;

            add_persona_result('customer', $tenant->id, 'place order', $orderPassed ? 'PASS' : 'FAIL', 'Customer order and order item save correctly.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'order_item_id' => $orderItem->id,
                'payment_method' => $order->payment_method,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Persona 6: Store owner manages order
            |--------------------------------------------------------------------------
            */

            $order->update([
                'status' => 'processing',
            ]);

            $processingPassed = $order->fresh()->status === 'processing';

            add_persona_result('store_owner', $tenant->id, 'update order status', $processingPassed ? 'PASS' : 'FAIL', 'Store owner can update order status.', [
                'order_id' => $order->id,
                'status' => $order->fresh()->status,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Persona 7: Stock deduction logic check
            |--------------------------------------------------------------------------
            | This does not change stock unless your app currently has automatic stock
            | deduction in the order controller. It simply flags whether stock remained
            | unchanged at the model-only level.
            |--------------------------------------------------------------------------
            */

            add_persona_result('system', $tenant->id, 'stock behavior observation', 'INFO', 'Model-only order creation does not automatically deduct stock. Controller checkout may handle this separately if implemented.', [
                'product_id' => $freshProduct->id,
                'stock_after_model_order' => $freshProduct->fresh()->stock,
            ]);

            DB::rollBack();

            add_persona_result('system', $tenant->id, 'rollback', 'PASS', 'Persona sample data rolled back successfully.');
        } catch (Throwable $e) {
            DB::rollBack();

            add_persona_result('system', $tenant->id, 'persona flow exception', 'FAIL', $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        tenancy()->end();
    } catch (Throwable $e) {
        try {
            tenancy()->end();
        } catch (Throwable $ignored) {
        }

        add_persona_result('system', $tenant->id ?? 'unknown', 'tenant initialize exception', 'FAIL', $e->getMessage(), [
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
