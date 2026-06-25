<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;

use App\Models\Cart;
use App\Models\CartItem;

use App\Models\Order;
use App\Models\Product;

use App\Models\OrderItem;

use App\Models\PlatformTransaction;
use App\Models\PaymentSetting;

use App\Models\Tenant;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Inertia\Inertia;
use App\Services\Payments\PlatformTransactionService;
use App\Services\Payments\UniversalPaymentGateway;
use App\Services\Plans\PlanFeatureGate;

class OrderController extends Controller

{

    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please log in to view your orders.');
        }

        $orders = Order::query()
            ->withCount('items')
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $tenant = tenant();
        $tenantData = $tenant->data ?? [];

        return Inertia::render('tenant/orders/CustomerIndex', [
            'orders' => $orders,
            'store' => [
                'name' => $tenantData['store_name'] ?? $tenant->name ?? 'Online Shop',
                'email' => $tenantData['store_email'] ?? $tenant->email ?? null,
                'currency' => strtoupper($tenantData['store_currency'] ?? 'USD'),
            ],
        ]);
    }


    public function checkout(Request $request)

    {

        $cartItems = $this->getCart($request);

        $cartItems->load(['product.images']);

        if ($cartItems->isEmpty()) {

            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Add items before checkout.');

        }

        $tenant = tenant();

        $tenantData = $tenant->data ?? [];

        $setting = PaymentSetting::active() ?? PaymentSetting::latest()->first();

        $activePaymentProcessor = $setting ? [
            'id' => $setting->id,
            'name' => $setting->name ?: strtoupper((string) $setting->provider),
            'provider' => $setting->provider,
            'config_type' => $setting->config_type,
            'environment' => $setting->environment,
            'currency' => $setting->currency,
            'fee_structure' => $setting->fee_structure,
            'processor_fee_percent' => (float) $setting->processor_fee_percent,
            'processor_fee_fixed' => (float) $setting->processor_fee_fixed,
            'is_active' => (bool) $setting->is_active,
        ] : null;

        return Inertia::render('tenant/Payment', [

            'cart' => [
                'items' => $cartItems,
            ],

            'cartItems' => $cartItems,

            'user' => Auth::user(),

            'store' => [

                'name' => $tenantData['store_name'] ?? $tenant->name ?? 'Online Shop',

                'email' => $tenantData['store_email'] ?? $tenant->email ?? null,

                'currency' => strtoupper($tenantData['store_currency'] ?? $activePaymentProcessor['currency'] ?? 'USD'),

                'delivery_fee' => (float) ($tenantData['delivery_fee'] ?? 0),

                'cash_on_delivery_enabled' => (bool) ($tenantData['cash_on_delivery_enabled'] ?? false),

            ],

            'activePaymentProcessor' => $activePaymentProcessor,

            'allowTestPayment' => app()->environment(['local', 'testing']),
            'planFeatures' => app(PlanFeatureGate::class)->featurePayload(),

        ]);

    }

    public function store(Request $request)

    {

        $request->validate([

            'billing_name' => 'required|string|max:255',

            'billing_email' => 'required|email|max:255',

            'billing_phone' => 'required|string|max:30',

            'billing_address' => 'required|string|max:255',

            'billing_city' => 'required|string|max:100',

            'billing_state' => 'required|string|max:100',

            'billing_country' => 'required|string|max:100',

            'billing_zipcode' => 'required|string|max:20',

            'shipping_name' => 'required|string|max:255',

            'shipping_address' => 'required|string|max:255',

            'shipping_city' => 'required|string|max:100',

            'shipping_state' => 'required|string|max:100',

            'shipping_country' => 'required|string|max:100',

            'shipping_zipcode' => 'required|string|max:20',

            'payment_method' => 'required|string|max:100',

            'notes' => 'nullable|string|max:1000',

        ]);

        $cartItems = $this->getCart($request);

        $cartItems->load('product');

        if ($cartItems->isEmpty()) {

            return redirect()->route('cart.index')->with('error', 'Your cart is empty. Add items before checkout.');

        }

        foreach ($cartItems as $item) {

            if ($item->quantity > $item->product->stock) {

                return redirect()

                    ->route('cart.index')

                    ->with('error', "Not enough stock for {$item->product->name}. Available: {$item->product->stock}");

            }

        }

        $activePaymentProcessor = PaymentSetting::active() ?? PaymentSetting::latest()->first();

        $activeProvider = $activePaymentProcessor?->provider ?: 'manual';

        try {

            DB::beginTransaction();

            $subtotal = 0;

            foreach ($cartItems as $item) {

                $subtotal += $item->price * $item->quantity;

            }

            $tenantData = tenant()->data ?? [];

            $shippingCost = (float) ($tenantData['delivery_fee'] ?? 0);

            $taxAmount = 0;

            $total = $subtotal + $shippingCost;

            $isTestPayment = $request->payment_method === 'Test Payment';

            $isExternalProcessor = $request->payment_method === $activeProvider;

        $planGate = app(PlanFeatureGate::class);

        if ($isExternalProcessor && ! $planGate->allowsApiPayments()) {
            return back()
                ->with('error', $planGate->apiPaymentMessage())
                ->withInput();
        }

            $isCashOnDelivery = $request->payment_method === 'Cash on Delivery';

            $isBankTransfer = $request->payment_method === 'Bank Transfer';

            $allowedMethods = collect([
                'Bank Transfer',
                'Cash on Delivery',
                $activeProvider,
            ]);

            if (app()->environment(['local', 'testing'])) {
                $allowedMethods->push('Test Payment');
            }

            if (! $allowedMethods->contains($request->payment_method)) {
                return redirect()
                    ->back()
                    ->with('error', 'The selected payment method is not available.');
            }

            if ($isCashOnDelivery && ! (bool) ((tenant()->data ?? [])['cash_on_delivery_enabled'] ?? false)) {

                return redirect()

                    ->back()

                    ->with('error', 'Cash on Delivery is not enabled for this store.');

            }

            $orderNumber = 'INV-' . date('Ymd') . '-' . uniqid();

            $checkoutUser = Auth::user() ?: $request->user();

            $this->validateCartStock($cartItems);

            $order = Order::create([

                'order_number' => $orderNumber,

                'user_id' => $checkoutUser?->id,

                'total' => $total,

                'status' => $isTestPayment ? 'processing' : 'pending',

                'notes' => $request->notes,

                'billing_name' => $request->billing_name,

                'billing_email' => $request->billing_email,

                'billing_phone' => $request->billing_phone,

                'billing_address' => $request->billing_address,

                'billing_city' => $request->billing_city,

                'billing_state' => $request->billing_state,

                'billing_country' => $request->billing_country,

                'billing_zipcode' => $request->billing_zipcode,

                'shipping_name' => $request->shipping_name,

                'shipping_address' => $request->shipping_address,

                'shipping_city' => $request->shipping_city,

                'shipping_state' => $request->shipping_state,

                'shipping_country' => $request->shipping_country,

                'shipping_zipcode' => $request->shipping_zipcode,

                'payment_method' => $request->payment_method,

                'payment_provider' => $isExternalProcessor ? $activeProvider : null,

                'payment_status' => $isTestPayment ? 'paid' : 'pending',

                'paid_at' => $isTestPayment ? now() : null,

                'payment_metadata' => [
                    'currency' => strtoupper($tenantData['store_currency'] ?? $activePaymentProcessor?->currency ?? 'TTD'),
                    'active_provider' => $activeProvider,
                    'payment_mode' => $request->payment_method,
                ],

            ]);

            foreach ($cartItems as $item) {

                OrderItem::create([

                    'order_id' => $order->id,

                    'product_id' => $item->product_id,

                    'product_name' => $item->product->name,

                    'quantity' => $item->quantity,

                    'price' => $item->price,

                    'subtotal' => $item->price * $item->quantity,

                ]);

                }


            $this->deductCartStock($cartItems);

            CartItem::whereIn('id', $cartItems->pluck('id'))->delete();

            DB::commit();

            if ($isTestPayment) {

                $this->createPlatformTransactionForPaidOrder($order, $activeProvider . '_test_payment');

            }

            if ($isExternalProcessor && ! $isTestPayment) {
                $checkout = app(UniversalPaymentGateway::class)->createCheckout($order, $activePaymentProcessor);

                $order->update([
                    'provider_payment_id' => $checkout['provider_payment_id'] ?? null,
                    'provider_checkout_url' => $checkout['checkout_url'] ?? null,
                    'provider_reference' => $checkout['provider_reference'] ?? null,
                    'payment_metadata' => array_merge($order->payment_metadata ?? [], [
                        'gateway_checkout' => $checkout,
                    ]),
                ]);

                if (($checkout['success'] ?? false) && ! empty($checkout['checkout_url'])) {
                    return redirect()->away($checkout['checkout_url']);
                }

                return redirect()
                    ->route('orders.confirmation', $order->id)
                    ->with('error', $checkout['message'] ?? 'Online payment could not be started. The order was created as pending.');
            }

            return redirect()

                ->route('orders.confirmation', $order->id)

                ->with('success', 'Your order has been placed successfully!');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()

                ->back()

                ->with('error', 'An error occurred while processing your order: ' . $e->getMessage());

        }

    }

    public function confirmation(Order $order)
    {
        $user = Auth::user();

        if ($order->user_id && $user && (int) $order->user_id !== (int) $user->id) {
            abort(403);
        }

        if ($order->user_id && ! $user) {
            return redirect()->route('login')->with('error', 'Please log in to view this order.');
        }

        $order->load(['items.product']);

        $tenant = tenant();
        $tenantData = $tenant->data ?? [];

        $platformTransaction = null;

        try {
            $platformTransaction = \App\Models\PlatformTransaction::where('tenant_id', tenant('id'))
                ->where('tenant_order_id', $order->id)
                ->latest('id')
                ->first();
        } catch (\Throwable $ignored) {
            $platformTransaction = null;
        }

        return Inertia::render('tenant/OrderConfirmation', [
            'order' => $order,
            'orderItems' => $order->items,
            'store' => [
                'name' => $tenantData['store_name'] ?? $tenant->name ?? 'Online Shop',
                'email' => $tenantData['store_email'] ?? $tenant->email ?? null,
                'currency' => strtoupper($tenantData['store_currency'] ?? 'USD'),
            ],
            'platformTransaction' => $platformTransaction,
        ]);
    }

    private function createPlatformTransactionForPaidOrder(Order $order, string $provider): void

    {

        $tenantId = tenant('id');

        tenancy()->central(function () use ($tenantId, $order, $provider) {

            $tenant = Tenant::with('currentSubscription.plan')->findOrFail($tenantId);

            app(PlatformTransactionService::class)->recordPaidOrder(

                tenant: $tenant,

                order: $order,

                provider: $provider

            );

        });

    }


    private function getCart(Request $request)
    {
        $currentUser = \Illuminate\Support\Facades\Auth::user() ?: $request->user();

        $cartQuery = \App\Models\Cart::query();

        if ($currentUser) {
            $cartQuery->where('user_id', $currentUser->id);
        } else {
            $cartQuery->where('session_id', $request->session()->getId());
        }

        $cart = $cartQuery->latest('id')->first();

        if (! $cart) {
            return collect();
        }

        return \App\Models\CartItem::with('product')
            ->where('cart_id', $cart->id)
            ->get()
            ->filter(fn ($item) => $item->product !== null)
            ->values();
    }

    private function validateCartStock($cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            $product = Product::lockForUpdate()->find($cartItem->product_id);

            if (! $product) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'cart' => 'A product in your cart is no longer available.',
                ]);
            }

            if (! $product->is_active) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'cart' => $product->name . ' is no longer active.',
                ]);
            }

            if ((int) $cartItem->quantity > (int) $product->stock) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'cart' => 'Only ' . $product->stock . ' unit(s) of ' . $product->name . ' are available.',
                ]);
            }
        }
    }

    private function deductCartStock($cartItems): void
    {
        foreach ($cartItems as $cartItem) {
            Product::where('id', $cartItem->product_id)
                ->where('stock', '>=', (int) $cartItem->quantity)
                ->decrement('stock', (int) $cartItem->quantity);
        }
    }

}

