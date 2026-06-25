<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$report = [];

function add_controller_persona_result(string $tenantId, string $persona, string $action, string $status, string $message, array $data = []): void
{
    global $report;

    $report[] = [
        'tenant_id' => $tenantId,
        'persona' => $persona,
        'action' => $action,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

function make_persona_request(string $method, string $uri, array $data = []): Request
{
    $request = Request::create($uri, $method, $data);
    $request->setLaravelSession(app('session.store'));
    app()->instance('request', $request);

    return $request;
}

function required_tables_exist(array $tables): array
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

        $missing = required_tables_exist($requiredTables);

        if (count($missing) > 0) {
            add_controller_persona_result($tenant->id, 'system', 'table readiness', 'FAIL', 'Tenant is missing required tables.', [
                'missing_tables' => $missing,
            ]);

            tenancy()->end();
            continue;
        }

        DB::beginTransaction();
        DB::connection('mysql')->beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Admin/store owner identity
            |--------------------------------------------------------------------------
            */

            $admin = \App\Models\User::query()->where('role', 'admin')->first()
                ?: \App\Models\User::query()->first();

            if (! $admin) {
                $admin = \App\Models\User::create([
                    'name' => 'Controller Persona Admin',
                    'email' => 'controller-admin-' . $tenant->id . '@example.com',
                    'password' => bcrypt('password123'),
                    'role' => 'admin',
                ]);
            }

            Auth::login($admin);

            add_controller_persona_result($tenant->id, 'store_owner', 'login simulation', 'PASS', 'Admin user authenticated in terminal persona audit.', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Manage category create
            |--------------------------------------------------------------------------
            */

            $categoryName = 'Controller Category ' . Str::random(8);
            $expectedCategorySlug = Str::slug($categoryName);

            $categoryCreateRequest = make_persona_request('POST', '/manage/category', [
                'name' => $categoryName,
                'description' => 'Created by controller persona audit.',
                'is_active' => true,
            ]);

            app(\App\Http\Controllers\Tenant\Manage\CategoryController::class)->store($categoryCreateRequest);

            $category = \App\Models\Category::where('name', $categoryName)
                ->where('slug', $expectedCategorySlug)
                ->first();

            add_controller_persona_result($tenant->id, 'store_owner', 'controller create category', $category ? 'PASS' : 'FAIL', $category
                ? 'Category controller created category.'
                : 'Category controller did not create category.',
                [
                    'category_id' => $category?->id,
                    'expected_slug' => $expectedCategorySlug,
                ]
            );

            if (! $category) {
                throw new RuntimeException('Category controller create failed; stopping tenant flow.');
            }

            /*
            |--------------------------------------------------------------------------
            | Manage category update
            |--------------------------------------------------------------------------
            */

            $categoryUpdateRequest = make_persona_request('POST', '/manage/category/' . $category->id . '/edit', [
                'name' => 'Updated Controller Category ' . Str::random(6),
                'description' => 'Updated by controller persona audit.',
                'is_active' => true,
            ]);

            app(\App\Http\Controllers\Tenant\Manage\CategoryController::class)->update($categoryUpdateRequest, $category);

            $freshCategory = $category->fresh();

            $categoryPassed = $freshCategory
                && str_starts_with($freshCategory->name, 'Updated Controller Category');

            add_controller_persona_result($tenant->id, 'store_owner', 'controller update category', $categoryPassed ? 'PASS' : 'FAIL', $categoryPassed
                ? 'Category controller updated category.'
                : 'Category controller did not update category.',
                [
                    'category_id' => $category->id,
                    'name' => $freshCategory?->name,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Manage product create
            |--------------------------------------------------------------------------
            */

            $productName = 'Controller Product ' . Str::random(8);
            $expectedProductSlug = Str::slug($productName);

            $productCreateRequest = make_persona_request('POST', '/manage/product', [
                'name' => $productName,
                'description' => 'Created by controller persona audit.',
                'price' => 200.00,
                'stock' => 12,
                'is_active' => true,
                'category_id' => $category->id,
            ]);

            app(\App\Http\Controllers\Tenant\Manage\ProductController::class)->store($productCreateRequest);

            $product = \App\Models\Product::where('name', $productName)
                ->where('slug', $expectedProductSlug)
                ->first();

            add_controller_persona_result($tenant->id, 'store_owner', 'controller create product', $product ? 'PASS' : 'FAIL', $product
                ? 'Product controller created product.'
                : 'Product controller did not create product.',
                [
                    'product_id' => $product?->id,
                    'expected_slug' => $expectedProductSlug,
                    'sku' => $product?->sku,
                ]
            );

            if (! $product) {
                throw new RuntimeException('Product controller create failed; stopping tenant flow.');
            }

            /*
            |--------------------------------------------------------------------------
            | Manage product update
            |--------------------------------------------------------------------------
            */

            $productUpdateRequest = make_persona_request('POST', '/manage/product/' . $product->id . '/edit', [
                'name' => 'Updated Controller Product ' . Str::random(6),
                'description' => 'Updated by controller persona audit.',
                'price' => 225.50,
                'stock' => 10,
                'is_active' => true,
                'category_id' => $category->id,
            ]);

            app(\App\Http\Controllers\Tenant\Manage\ProductController::class)->update($productUpdateRequest, $product);

            $product = $product->fresh();

            $productUpdatePassed = str_starts_with($product->name, 'Updated Controller Product')
                && (float) $product->price === 225.50
                && (int) $product->stock === 10
                && (int) $product->category_id === (int) $category->id;

            add_controller_persona_result($tenant->id, 'store_owner', 'controller update product', $productUpdatePassed ? 'PASS' : 'FAIL', $productUpdatePassed
                ? 'Product controller updated product.'
                : 'Product controller did not update expected fields.',
                [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'category_id' => $product->category_id,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Customer cart through real CartController
            |--------------------------------------------------------------------------
            */

            $customer = \App\Models\User::create([
                'name' => 'Controller Persona Customer',
                'email' => 'controller-customer-' . Str::random(8) . '@example.com',
                'password' => bcrypt('password123'),
                'role' => 'customer',
            ]);

            Auth::login($customer);

            $addCartRequest = make_persona_request('POST', '/cart/add', [
                'product_id' => $product->id,
                'quantity' => 2,
            ]);

            app(\App\Http\Controllers\Tenant\CartController::class)->addItem($addCartRequest);

            $cart = \App\Models\Cart::with('items.product')
                ->where('user_id', $customer->id)
                ->first();

            $cartItem = $cart?->items?->first();

            $cartAddPassed = $cart
                && $cartItem
                && (int) $cartItem->product_id === (int) $product->id
                && (int) $cartItem->quantity === 2;

            add_controller_persona_result($tenant->id, 'customer', 'controller add cart item', $cartAddPassed ? 'PASS' : 'FAIL', $cartAddPassed
                ? 'Cart controller added product to cart.'
                : 'Cart controller failed to add product to cart.',
                [
                    'customer_id' => $customer->id,
                    'cart_id' => $cart?->id,
                    'cart_item_id' => $cartItem?->id,
                    'product_id' => $product->id,
                    'quantity' => $cartItem?->quantity,
                ]
            );

            if (! $cartItem) {
                throw new RuntimeException('Cart controller add failed; stopping tenant flow.');
            }

            $updateCartRequest = make_persona_request('PATCH', '/cart/' . $cartItem->id, [
                'quantity' => 3,
            ]);

            app(\App\Http\Controllers\Tenant\CartController::class)->updateItem($updateCartRequest, $cartItem);

            $cartUpdatePassed = (int) $cartItem->fresh()->quantity === 3;

            add_controller_persona_result($tenant->id, 'customer', 'controller update cart item', $cartUpdatePassed ? 'PASS' : 'FAIL', $cartUpdatePassed
                ? 'Cart controller updated item quantity.'
                : 'Cart controller failed to update item quantity.',
                [
                    'cart_item_id' => $cartItem->id,
                    'quantity' => $cartItem->fresh()->quantity,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Customer checkout/order through real OrderController
            |--------------------------------------------------------------------------
            */

            $stockBeforeOrder = (int) $product->fresh()->stock;

            Auth::login($customer);

            $orderRequest = make_persona_request('POST', '/orders', [
                'billing_name' => 'Controller Customer',
                'billing_email' => $customer->email,
                'billing_phone' => '8681234567',
                'billing_address' => '123 Controller Street',
                'billing_city' => 'San Fernando',
                'billing_state' => 'South',
                'billing_country' => 'TT',
                'billing_zipcode' => '00000',
                'shipping_name' => 'Controller Customer',
                'shipping_address' => '123 Controller Street',
                'shipping_city' => 'San Fernando',
                'shipping_state' => 'South',
                'shipping_country' => 'TT',
                'shipping_zipcode' => '00000',
                'payment_method' => 'Bank Transfer',
                'notes' => 'Controller persona checkout test.',
            ]);

            $orderRequest->setUserResolver(fn () => $customer);
            app()->instance('request', $orderRequest);

            $orderResponse = app(\App\Http\Controllers\Tenant\OrderController::class)->store($orderRequest);

            $orderResponseClass = is_object($orderResponse) ? get_class($orderResponse) : gettype($orderResponse);
            $orderResponseTarget = method_exists($orderResponse, 'getTargetUrl') ? $orderResponse->getTargetUrl() : null;
            $orderSessionError = session('error');
            $orderSessionSuccess = session('success');

            $order = \App\Models\Order::with('items.product')
                ->where('user_id', $customer->id)
                ->latest()
                ->first();

            $productAfterOrder = \App\Models\Product::find($product->id);
            $cartAfterOrder = $cart->fresh();

            $orderPassed = $order
                && $productAfterOrder
                && $cartAfterOrder
                && $order->items->count() === 1
                && (int) $order->items->first()->product_id === (int) $product->id
                && (int) $productAfterOrder->stock === ($stockBeforeOrder - 3)
                && $cartAfterOrder->items()->count() === 0;

            add_controller_persona_result($tenant->id, 'customer', 'controller place order', $orderPassed ? 'PASS' : 'FAIL', $orderPassed
                ? 'Order controller placed order, deducted stock, and cleared cart.'
                : 'Order controller did not produce expected checkout result.',
                [
                    'order_id' => $order?->id,
                    'order_number' => $order?->order_number,
                    'order_total' => $order?->total,
                    'order_items_count' => $order?->items?->count(),
                    'stock_before' => $stockBeforeOrder,
                    'stock_after' => $productAfterOrder?->stock,
                    'cart_items_after' => $cartAfterOrder?->items()->count(),
                    'payment_method' => $order?->payment_method,
                    'payment_status' => $order?->payment_status,
                    'response_class' => $orderResponseClass ?? null,
                    'response_target' => $orderResponseTarget ?? null,
                    'session_error' => $orderSessionError ?? null,
                    'session_success' => $orderSessionSuccess ?? null,
                    'auth_id' => \Illuminate\Support\Facades\Auth::id(),
                    'request_user_id' => $orderRequest->user()?->id,
                    'cart_before_id' => $cart?->id,
                    'cart_before_user_id' => $cart?->user_id,
                    'cart_before_items' => $cart?->items()->count(),
                ]
            );

            if (! $order) {
                throw new RuntimeException('Order controller store failed; stopping tenant flow.');
            }

            /*
            |--------------------------------------------------------------------------
            | Store owner order status management through real Manage\OrderController
            |--------------------------------------------------------------------------
            */

            Auth::login($admin);

            $statusRequest = make_persona_request('POST', '/manage/order/' . $order->id . '/status', [
                'status' => 'processing',
            ]);

            app(\App\Http\Controllers\Tenant\Manage\OrderController::class)->updateStatus($statusRequest, $order);

            $statusPassed = $order->fresh()->status === 'processing';

            add_controller_persona_result($tenant->id, 'store_owner', 'controller update order status', $statusPassed ? 'PASS' : 'FAIL', $statusPassed
                ? 'Manage order controller updated order status.'
                : 'Manage order controller did not update order status.',
                [
                    'order_id' => $order->id,
                    'status' => $order->fresh()->status,
                ]
            );

            $paymentStatusRequest = make_persona_request('POST', '/manage/order/' . $order->id . '/payment-status', [
                'payment_status' => 'paid',
            ]);

            app(\App\Http\Controllers\Tenant\Manage\OrderController::class)->updatePaymentStatus($paymentStatusRequest, $order);

            // The controller updates the passed Order model directly before creating
            // a central platform transaction. Check the same model instance.
            $paymentStatusPassed = $order->payment_status === 'paid';

            add_controller_persona_result($tenant->id, 'store_owner', 'controller update payment status', $paymentStatusPassed ? 'PASS' : 'FAIL', $paymentStatusPassed
                ? 'Manage order controller updated payment status.'
                : 'Manage order controller did not update payment status.',
                [
                    'order_id' => $order->id,
                    'payment_status_on_model' => $order->payment_status,
                    'paid_at_on_model' => $order->paid_at?->toDateTimeString(),
                ]
            );

            DB::rollBack();
            DB::connection('mysql')->rollBack();
            Auth::logout();

            add_controller_persona_result($tenant->id, 'system', 'rollback', 'PASS', 'Controller persona audit data rolled back successfully.');
        } catch (Throwable $e) {
            try {
                DB::rollBack();
            } catch (Throwable $ignored) {
            }

            try {
                DB::connection('mysql')->rollBack();
            } catch (Throwable $ignored) {
            }

            Auth::logout();

            add_controller_persona_result($tenant->id, 'system', 'controller persona exception', 'FAIL', $e->getMessage(), [
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

        add_controller_persona_result($tenant->id ?? 'unknown', 'system', 'tenant initialize exception', 'FAIL', $e->getMessage(), [
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
