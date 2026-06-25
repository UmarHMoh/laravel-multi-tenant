<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\OrderController;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$results = [];

function add_cart_stock_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
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
        $cartAddRoute = Route::getRoutes()->getByName('cart.add');
        $orderStoreRoute = Route::getRoutes()->getByName('orders.store');

        add_cart_stock_result($tenant->id, 'cart add route exists', $cartAddRoute ? 'PASS' : 'FAIL', $cartAddRoute
            ? 'Cart add route exists.'
            : 'Cart add route is missing.'
        );

        add_cart_stock_result($tenant->id, 'order store route exists', $orderStoreRoute ? 'PASS' : 'FAIL', $orderStoreRoute
            ? 'Order store route exists.'
            : 'Order store route is missing.'
        );

        $user = User::create([
            'name' => 'Cart Stock Audit User',
            'email' => 'cart-stock-audit-' . strtolower(substr(md5($tenant->id . microtime()), 0, 8)) . '@example.com',
            'password' => Hash::make('password123'),
        ]);

        Auth::login($user);

        $category = Category::firstOrCreate(
            ['name' => 'Cart Stock Audit Category'],
            [
                'slug' => 'cart-stock-audit-category',
                'description' => 'Temporary cart stock audit category.',
                'is_active' => true,
            ]
        );

        $product = Product::create([
            'name' => 'Cart Stock Audit ' . strtoupper(substr(md5($tenant->id . microtime()), 0, 6)),
            'slug' => 'cart-stock-audit-' . strtolower(substr(md5($tenant->id . microtime()), 0, 8)),
            'description' => 'Temporary cart stock audit product.',
            'price' => 50.00,
            'stock' => 2,
            'is_active' => true,
            'sku' => 'CART-STOCK-' . strtoupper(substr(md5(microtime()), 0, 6)),
            'category_id' => $category->id,
        ]);

        $addRequest = Request::create('/cart/add', 'POST', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $addRequest->setUserResolver(fn () => $user);
        $addRequest->setLaravelSession(app('session')->driver());
        app()->instance('request', $addRequest);

        app(CartController::class)->addItem($addRequest);

        $cartQuantity = CartItem::where('product_id', $product->id)->sum('quantity');

        add_cart_stock_result($tenant->id, 'cart blocks quantity above stock', (int) $cartQuantity === 0 ? 'PASS' : 'FAIL', (int) $cartQuantity === 0
            ? 'Cart did not add quantity above available stock.'
            : 'Cart allowed quantity above available stock.',
            [
                'stock' => $product->stock,
                'cart_quantity' => $cartQuantity,
            ]
        );

        $validAddRequest = Request::create('/cart/add', 'POST', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $validAddRequest->setUserResolver(fn () => $user);
        $validAddRequest->setLaravelSession(app('session')->driver());
        app()->instance('request', $validAddRequest);

        app(CartController::class)->addItem($validAddRequest);

        $cart = Cart::where('user_id', $user->id)->first();
        $cartQuantity = CartItem::where('cart_id', $cart?->id)
            ->where('product_id', $product->id)
            ->sum('quantity');

        add_cart_stock_result($tenant->id, 'cart allows quantity within stock', (int) $cartQuantity === 2 ? 'PASS' : 'FAIL', (int) $cartQuantity === 2
            ? 'Cart allowed quantity within stock.'
            : 'Cart did not add valid stock quantity.',
            [
                'stock' => $product->stock,
                'cart_quantity' => $cartQuantity,
                'cart_id' => $cart?->id,
                'user_id' => $user->id,
            ]
        );

        $checkoutRequest = Request::create('/orders', 'POST', [
            'billing_name' => 'Stock Audit Customer',
            'billing_email' => 'stock-audit@example.com',
            'billing_phone' => '8681234567',
            'billing_address' => '123 Stock Street',
            'billing_city' => 'San Fernando',
            'billing_state' => 'South',
            'billing_country' => 'TT',
            'billing_zipcode' => '00000',
            'shipping_name' => 'Stock Audit Customer',
            'shipping_address' => '123 Stock Street',
            'shipping_city' => 'San Fernando',
            'shipping_state' => 'South',
            'shipping_country' => 'TT',
            'shipping_zipcode' => '00000',
            'payment_method' => 'Bank Transfer',
            'notes' => 'Cart checkout stock audit.',
        ]);

        $checkoutRequest->setUserResolver(fn () => $user);
        $checkoutRequest->setLaravelSession(app('session')->driver());
        app()->instance('request', $checkoutRequest);

        app(OrderController::class)->store($checkoutRequest);

        $product->refresh();

        add_cart_stock_result($tenant->id, 'checkout deducts stock after order', (int) $product->stock === 0 ? 'PASS' : 'FAIL', (int) $product->stock === 0
            ? 'Checkout deducted ordered quantity from stock.'
            : 'Checkout did not deduct stock correctly.',
            [
                'remaining_stock' => $product->stock,
            ]
        );

        $cartRemaining = CartItem::where('product_id', $product->id)->sum('quantity');

        add_cart_stock_result($tenant->id, 'checkout clears ordered cart item', (int) $cartRemaining === 0 ? 'PASS' : 'FAIL', (int) $cartRemaining === 0
            ? 'Checkout cleared ordered cart item.'
            : 'Checkout did not clear ordered cart item.',
            [
                'cart_remaining' => $cartRemaining,
            ]
        );
    } catch (Throwable $e) {
        add_cart_stock_result($tenant->id, 'cart checkout stock exception', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    } finally {
        try { Auth::logout(); } catch (Throwable $ignored) {}
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
