<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\OrderController;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

$results = [];


function inertia_component_name($response): ?string
{
    if (is_object($response) && method_exists($response, 'getComponent')) {
        return $response->getComponent();
    }

    if (is_object($response)) {
        try {
            $reflection = new ReflectionClass($response);

            if ($reflection->hasProperty('component')) {
                $property = $reflection->getProperty('component');
                $property->setAccessible(true);

                return $property->getValue($response);
            }
        } catch (Throwable $ignored) {
            return null;
        }
    }

    return null;
}

function add_customer_order_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
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

    $token = strtolower(substr(md5($tenant->id . microtime(true)), 0, 10));
    $user = null;
    $otherUser = null;
    $product = null;
    $order = null;

    try {
        add_customer_order_result($tenant->id, 'customer order history route exists', Route::getRoutes()->getByName('orders.customer.index') ? 'PASS' : 'FAIL',
            Route::getRoutes()->getByName('orders.customer.index') ? 'Customer order history route exists.' : 'Customer order history route missing.'
        );

        add_customer_order_result($tenant->id, 'order confirmation route exists', Route::getRoutes()->getByName('orders.confirmation') ? 'PASS' : 'FAIL',
            Route::getRoutes()->getByName('orders.confirmation') ? 'Order confirmation route exists.' : 'Order confirmation route missing.'
        );

        $user = User::create([
            'name' => 'Customer Order Audit',
            'email' => 'customer-order-audit-' . $token . '@example.com',
            'password' => Hash::make('password123'),
        ]);

        $otherUser = User::create([
            'name' => 'Other Customer Audit',
            'email' => 'other-customer-order-audit-' . $token . '@example.com',
            'password' => Hash::make('password123'),
        ]);

        $category = Category::firstOrCreate(
            ['name' => 'Customer Order Audit Category'],
            [
                'slug' => 'customer-order-audit-category',
                'description' => 'Temporary customer order audit category.',
                'is_active' => true,
            ]
        );

        $product = Product::create([
            'name' => 'Customer Order Audit Product ' . strtoupper($token),
            'slug' => 'customer-order-audit-product-' . $token,
            'description' => 'Temporary customer order audit product.',
            'price' => 25,
            'stock' => 20,
            'is_active' => true,
            'sku' => 'CUST-ORD-' . strtoupper($token),
            'category_id' => $category->id,
        ]);

        $order = Order::create([
            'order_number' => 'COA-' . strtoupper($token),
            'user_id' => $user->id,
            'status' => 'processing',
            'subtotal' => 50,
            'tax_amount' => 0,
            'shipping_cost' => 0,
            'total' => 50,
            'billing_name' => 'Customer Order Audit',
            'billing_email' => $user->email,
            'billing_phone' => '8681234567',
            'billing_address' => '123 Audit Street',
            'billing_city' => 'San Fernando',
            'billing_state' => 'South',
            'billing_country' => 'TT',
            'billing_zipcode' => '00000',
            'shipping_name' => 'Customer Order Audit',
            'shipping_address' => '123 Audit Street',
            'shipping_city' => 'San Fernando',
            'shipping_state' => 'South',
            'shipping_country' => 'TT',
            'shipping_zipcode' => '00000',
            'payment_method' => 'Bank Transfer',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'price' => 25,
            'subtotal' => 50,
        ]);

        Auth::login($user);

        $historyRequest = Request::create('/orders', 'GET');
        $historyRequest->setUserResolver(fn () => $user);
        app()->instance('request', $historyRequest);

        $historyResponse = app(OrderController::class)->index($historyRequest);

        $historyComponent = inertia_component_name($historyResponse);

        add_customer_order_result($tenant->id, 'customer order history loads', $historyComponent === 'tenant/orders/CustomerIndex' ? 'PASS' : 'FAIL',
            $historyComponent === 'tenant/orders/CustomerIndex' ? 'Customer order history rendered.' : 'Customer order history did not render expected component.',
            [
                'component' => $historyComponent,
                'response_class' => is_object($historyResponse) ? get_class($historyResponse) : gettype($historyResponse),
            ]
        );

        $confirmationResponse = app(OrderController::class)->confirmation($order);

        $confirmationComponent = inertia_component_name($confirmationResponse);

        add_customer_order_result($tenant->id, 'order confirmation loads', $confirmationComponent === 'tenant/OrderConfirmation' ? 'PASS' : 'FAIL',
            $confirmationComponent === 'tenant/OrderConfirmation' ? 'Order confirmation rendered.' : 'Order confirmation did not render expected component.',
            [
                'component' => $confirmationComponent,
                'response_class' => is_object($confirmationResponse) ? get_class($confirmationResponse) : gettype($confirmationResponse),
            ]
        );

        Auth::logout();
        Auth::login($otherUser);

        $blocked = false;

        try {
            app(OrderController::class)->confirmation($order);
        } catch (Throwable $e) {
            $blocked = method_exists($e, 'getStatusCode') && $e->getStatusCode() === 403;
        }

        add_customer_order_result($tenant->id, 'other customer cannot view order', $blocked ? 'PASS' : 'FAIL',
            $blocked ? 'Other customer was blocked from viewing this order.' : 'Other customer was not blocked from viewing this order.'
        );
    } catch (Throwable $e) {
        add_customer_order_result($tenant->id, 'customer order experience exception', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    } finally {
        try { Auth::logout(); } catch (Throwable $ignored) {}
        try {
            if ($order) {
                $order->items()->delete();
                $order->delete();
            }
            if ($product) {
                $product->delete();
            }
            if ($user) {
                $user->delete();
            }
            if ($otherUser) {
                $otherUser->delete();
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
