<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\HomepageController;
use App\Http\Controllers\Tenant\PageController;
use App\Http\Controllers\Tenant\OrderController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\Manage\TenantAssetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Middleware\EnsureTenantIsActive;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::middleware(EnsureTenantIsActive::class)->group(function () {
        // Tenant homepage
        Route::get('/home', [HomepageController::class, 'index'])->name('home');

        // Product routes
        Route::resource('products', ProductController::class)->only(['show']);

        // Cart routes
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/add', [CartController::class, 'addItem'])->name('cart.add');
        Route::patch('/cart/{cartItem}', [CartController::class, 'updateItem'])->name('cart.update');
        Route::delete('/cart/{cartItem}', [CartController::class, 'removeItem'])->name('cart.remove');
        Route::delete('/cart/clear/{cart}', [CartController::class, 'clearCart'])->name('cart.clear');

        // Checkout and order routes
        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/confirmation/{order}', [OrderController::class, 'confirmation'])->name('orders.confirmation');
    });


    // Contact page and contact form submit
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');
    Route::post('/contact', [PageController::class, 'storeContact'])->middleware('throttle:contact-form')->name('contact.store');

    Route::get('tenant-asset/{path}', TenantAssetController::class)
        ->where('path', '.*')
        ->name('tenant.asset');

    // Include tenant-specific auth routes
    require __DIR__.'/tenant/auth.php';

    // Tenant dashboard and other protected routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', function () {
            $tenantId = tenant('id');

            $tenant = tenancy()->central(function () use ($tenantId) {
                return \App\Models\Tenant::with([
                    'currentSubscription.plan',
                    'payoutAccounts',
                ])->findOrFail($tenantId);
            });

            $tenantData = $tenant->data ?? [];

            $pendingTotal = tenancy()->central(function () use ($tenant) {
                return \App\Models\PlatformTransaction::where('tenant_id', $tenant->id)
                    ->where('payout_status', 'pending')
                    ->sum('tenant_payout_amount');
            });

            $pendingCommission = tenancy()->central(function () use ($tenant) {
                return \App\Models\PlatformTransaction::where('tenant_id', $tenant->id)
                    ->where('payout_status', 'pending')
                    ->sum('commission_amount');
            });

            $pendingTransactions = tenancy()->central(function () use ($tenant) {
                return \App\Models\PlatformTransaction::where('tenant_id', $tenant->id)
                    ->where('payout_status', 'pending')
                    ->count();
            });

            $latestPayoutAccount = $tenant->payoutAccounts
                ->sortByDesc('created_at')
                ->first();

            return Inertia::render('tenant/Dashboard', [
                'tenantId' => $tenant->id,
                'tenantName' => $tenant->name,
                'store' => [
                    'name' => $tenantData['store_name'] ?? $tenant->name,
                    'email' => $tenantData['store_email'] ?? $tenant->email,
                    'description' => $tenantData['store_description'] ?? null,
                    'currency' => strtoupper($tenantData['store_currency'] ?? 'USD'),
                    'domain' => request()->getHost(),
                    'storefront_url' => request()->getSchemeAndHttpHost() . '/home',
                ],
                'currentPlan' => $tenant->currentSubscription?->plan,
                'currentSubscription' => $tenant->currentSubscription,
                'payoutAccount' => $latestPayoutAccount ? [
                    'type' => $latestPayoutAccount->type,
                    'status' => $latestPayoutAccount->status,
                    'account_holder_name' => $latestPayoutAccount->account_holder_name,
                    'created_at' => $latestPayoutAccount->created_at,
                ] : null,
                'payoutSummary' => [
                    'pending_total' => $pendingTotal,
                    'pending_commission' => $pendingCommission,
                    'pending_transactions' => $pendingTransactions,
                ],
            ]);
        })->name('dashboard');
        require __DIR__.'/tenant/admin.php';
    });
});

Route::get('/pages/{handle}', [PageController::class, 'show'])->where('handle', '[A-Za-z0-9\-]+')->name('pages.show');
