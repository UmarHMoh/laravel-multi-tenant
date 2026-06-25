<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\OrderController;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\PlatformTransaction;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\TenantPayoutLedgerEntry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

$results = [];

function add_test_payment_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
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

    $auditToken = strtolower(substr(md5($tenant->id . microtime(true)), 0, 10));
    $auditEmail = 'test-payment-audit-' . $auditToken . '@example.com';
    $productId = null;
    $userId = null;

    try {
        add_test_payment_result(
            $tenant->id,
            'order store route exists',
            Route::getRoutes()->getByName('orders.store') ? 'PASS' : 'FAIL',
            Route::getRoutes()->getByName('orders.store') ? 'Order store route exists.' : 'Order store route missing.'
        );

        $user = User::create([
            'name' => 'Test Payment Audit User',
            'email' => $auditEmail,
            'password' => Hash::make('password123'),
        ]);

        $userId = $user->id;

        Auth::login($user);

        Cart::where('user_id', $user->id)->each(function ($cart) {
            $cart->items()->delete();
            $cart->delete();
        });

        $category = Category::firstOrCreate(
            ['name' => 'Test Payment Audit Category'],
            [
                'slug' => 'test-payment-audit-category',
                'description' => 'Temporary test payment audit category.',
                'is_active' => true,
            ]
        );

        $product = Product::create([
            'name' => 'Test Payment Audit ' . strtoupper($auditToken),
            'slug' => 'test-payment-audit-' . $auditToken,
            'description' => 'Temporary test payment audit product.',
            'price' => 40.00,
            'stock' => 5,
            'is_active' => true,
            'sku' => 'TEST-PAY-' . strtoupper($auditToken),
            'category_id' => $category->id,
        ]);

        $productId = $product->id;

        $addRequest = Request::create('/cart/add', 'POST', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $addRequest->setUserResolver(fn () => $user);
        $addRequest->setLaravelSession(app('session')->driver());
        app()->instance('request', $addRequest);

        app(CartController::class)->addItem($addRequest);

        $cart = Cart::where('user_id', $user->id)->latest('id')->first();
        $cartQuantity = CartItem::where('cart_id', $cart?->id)->where('product_id', $product->id)->sum('quantity');

        add_test_payment_result(
            $tenant->id,
            'test payment cart created',
            (int) $cartQuantity === 2 ? 'PASS' : 'FAIL',
            (int) $cartQuantity === 2 ? 'Cart item was created for test payment checkout.' : 'Cart item was not created.',
            [
                'cart_id' => $cart?->id,
                'cart_quantity' => $cartQuantity,
            ]
        );

        tenancy()->central(function () use ($tenant, &$platformBefore, &$ledgerBefore) {
            $platformBefore = PlatformTransaction::where('tenant_id', $tenant->id)->count();
            $ledgerBefore = TenantPayoutLedgerEntry::where('tenant_id', $tenant->id)->count();
        });

        tenancy()->initialize($tenant);

        $checkoutRequest = Request::create('/orders', 'POST', [
            'billing_name' => 'Test Payment Audit Customer',
            'billing_email' => $auditEmail,
            'billing_phone' => '8681234567',
            'billing_address' => '123 Test Payment Street',
            'billing_city' => 'San Fernando',
            'billing_state' => 'South',
            'billing_country' => 'TT',
            'billing_zipcode' => '00000',
            'shipping_name' => 'Test Payment Audit Customer',
            'shipping_address' => '123 Test Payment Street',
            'shipping_city' => 'San Fernando',
            'shipping_state' => 'South',
            'shipping_country' => 'TT',
            'shipping_zipcode' => '00000',
            'payment_method' => 'Test Payment',
            'notes' => 'Test payment transaction audit.',
        ]);

        $checkoutRequest->setUserResolver(fn () => $user);
        $checkoutRequest->setLaravelSession(app('session')->driver());
        app()->instance('request', $checkoutRequest);

        $response = app(OrderController::class)->store($checkoutRequest);

        $controllerError = $checkoutRequest->session()->get('error');

        if ($controllerError) {
            add_test_payment_result($tenant->id, 'test payment controller returned error', 'FAIL', $controllerError, [
                'response_class' => is_object($response) ? get_class($response) : gettype($response),
            ]);

            continue;
        }

        tenancy()->initialize($tenant);

        $remainingStock = Product::where('id', $productId)->value('stock');
        $latestOrder = Order::where('billing_email', $auditEmail)->latest('id')->first();

        add_test_payment_result(
            $tenant->id,
            'test payment creates paid order',
            $latestOrder && $latestOrder->payment_status === 'paid' ? 'PASS' : 'FAIL',
            $latestOrder && $latestOrder->payment_status === 'paid' ? 'Test payment checkout created a paid order.' : 'Test payment checkout did not create a paid order.',
            [
                'order_id' => $latestOrder?->id,
                'payment_status' => $latestOrder?->payment_status,
            ]
        );

        add_test_payment_result(
            $tenant->id,
            'test payment deducts stock',
            (int) $remainingStock === 3 ? 'PASS' : 'FAIL',
            (int) $remainingStock === 3 ? 'Test payment deducted stock.' : 'Test payment did not deduct stock correctly.',
            [
                'remaining_stock' => $remainingStock,
            ]
        );

        tenancy()->central(function () use ($tenant, $platformBefore, $ledgerBefore, &$platformAfter, &$ledgerAfter) {
            $platformAfter = PlatformTransaction::where('tenant_id', $tenant->id)->count();
            $ledgerAfter = TenantPayoutLedgerEntry::where('tenant_id', $tenant->id)->count();
        });

        add_test_payment_result(
            $tenant->id,
            'test payment creates platform transaction',
            $platformAfter === $platformBefore + 1 ? 'PASS' : 'FAIL',
            $platformAfter === $platformBefore + 1 ? 'Test payment created one platform transaction.' : 'Test payment did not create exactly one platform transaction.',
            [
                'before' => $platformBefore,
                'after' => $platformAfter,
            ]
        );

        add_test_payment_result(
            $tenant->id,
            'test payment creates payout ledger credit',
            $ledgerAfter === $ledgerBefore + 1 ? 'PASS' : 'FAIL',
            $ledgerAfter === $ledgerBefore + 1 ? 'Test payment created one payout ledger credit.' : 'Test payment did not create exactly one payout ledger credit.',
            [
                'before' => $ledgerBefore,
                'after' => $ledgerAfter,
            ]
        );
    } catch (Throwable $e) {
        add_test_payment_result($tenant->id, 'test payment transaction exception', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    } finally {
        try { Auth::logout(); } catch (Throwable $ignored) {}

        try {
            tenancy()->initialize($tenant);

            if ($userId) {
                Cart::where('user_id', $userId)->each(function ($cart) {
                    $cart->items()->delete();
                    $cart->delete();
                });

                User::where('id', $userId)->delete();
            }

            if ($productId) {
                Product::where('id', $productId)->delete();
            }
        } catch (Throwable $ignored) {}

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
