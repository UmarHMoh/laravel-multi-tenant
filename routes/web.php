

<?php
use App\Http\Controllers\Tenant\HomepageController as TenantHomepageController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\Central\PaymentSettingController;
use App\Http\Controllers\Central\PayoutRequestController as CentralPayoutRequestController;
use App\Http\Controllers\Central\PayoutBatchController as CentralPayoutBatchController;
use App\Http\Controllers\Central\AccountingExportController;
use App\Http\Controllers\Tenant\Manage\PayoutRequestController as TenantPayoutRequestController;
use App\Http\Controllers\Central\PlatformSettingController;
use App\Http\Controllers\Central\PlatformTransactionController;
use App\Http\Controllers\Central\PayoutController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/central/login', function () {
    return \Inertia\Inertia::render('central/Login');
})->name('central.login');

Route::post('/central/login', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $expectedEmail = env('CENTRAL_ADMIN_EMAIL', 'umarhmohammed04@gmail.com');
    $expectedPassword = env('CENTRAL_ADMIN_PASSWORD', 'password123');

    if (
        $validated['email'] !== $expectedEmail
        || ! hash_equals($expectedPassword, $validated['password'])
    ) {
        return back()->withErrors([
            'email' => 'The central admin login details are incorrect.',
        ])->onlyInput('email');
    }

    $request->session()->put('central_admin_authenticated', true);
    $request->session()->put('central_admin_email', $validated['email']);

    return redirect()->intended('/central')->with('success', 'Logged in successfully.');
})->name('central.login.store');

Route::post('/central/logout', function (\Illuminate\Http\Request $request) {
    $request->session()->forget([
        'central_admin_authenticated',
        'central_admin_email',
    ]);

    return redirect('/central/login')->with('success', 'Logged out successfully.');
})->name('central.logout');




/*
|--------------------------------------------------------------------------
| Local tenant storefront root
|--------------------------------------------------------------------------
| This ensures local tenant domains such as tenant1.localhost hit the
| tenant storefront instead of central routes or 404.
*/
Route::domain('{tenant}.localhost')
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
    ])
    ->group(function () {
        Route::get('/', [TenantHomepageController::class, 'index'])->name('tenant.local.home');
    });

Route::domain('localhost')->middleware(['web'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('central.home');

    Route::prefix('central')->name('central.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
        Route::post('/tenants/{tenant}/plan', [TenantController::class, 'assignPlan'])->name('tenants.assign-plan');
        Route::post('/tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
        Route::post('/tenants/{tenant}/deactivate', [TenantController::class, 'deactivate'])->name('tenants.deactivate');
        Route::post('/tenants/{tenant}/custom-domain/approve', [TenantController::class, 'approveCustomDomain'])->name('tenants.custom-domain.approve');
        Route::post('/tenants/{tenant}/custom-domain/reject', [TenantController::class, 'rejectCustomDomain'])->name('tenants.custom-domain.reject');

        Route::get('/payment-settings', [PaymentSettingController::class, 'edit'])->name('payment-settings.edit');
        Route::put('/payment-settings', [PaymentSettingController::class, 'update'])->name('payment-settings.update');
        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
        Route::get('/plans/create', [PlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');

        Route::get('/transactions', [PlatformTransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/create', [PlatformTransactionController::class, 'create'])->name('transactions.create');
        Route::get('/transactions/{transaction}', [PlatformTransactionController::class, 'show'])->name('transactions.show');
        Route::post('/transactions', [PlatformTransactionController::class, 'store'])->name('transactions.store');

        Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
        Route::get('/payouts/{tenant}', [PayoutController::class, 'show'])->name('payouts.show');
        Route::post('/payouts/{tenant}/mark-paid', [PayoutController::class, 'markPaid'])->name('payouts.mark-paid');
        Route::get('/payout-batches/{payout}', [PayoutController::class, 'batchShow'])->name('payouts.batch-show');
    });
});

Route::post('/central/tenants/{tenant}/subscription-payment', [\App\Http\Controllers\Central\TenantSubscriptionPaymentController::class, 'store'])->name('central.tenants.subscription-payment.store');

Route::domain('localhost')->get('/', function () {
    return \Inertia\Inertia::render('central/Login');
})->name('central.login');

Route::get('/central/settings', function () {
    return \Inertia\Inertia::render('central/settings/Edit');
})->name('central.settings.edit');


Route::get('/central/settings', [PlatformSettingController::class, 'edit'])->name('central.settings.edit');
Route::put('/central/settings', [PlatformSettingController::class, 'update'])->name('central.settings.update');


Route::post('/central/payout-accounts/{account}/approve', [\App\Http\Controllers\Central\PayoutAccountReviewController::class, 'approve'])->name('central.payout-accounts.approve');
Route::post('/central/payout-accounts/{account}/reject', [\App\Http\Controllers\Central\PayoutAccountReviewController::class, 'reject'])->name('central.payout-accounts.reject');
Route::post('/central/payout-accounts/{account}/pending', [\App\Http\Controllers\Central\PayoutAccountReviewController::class, 'markPending'])->name('central.payout-accounts.pending');


Route::get('/central/bank-options', [\App\Http\Controllers\Central\PayoutBankOptionController::class, 'index'])->name('central.bank-options.index');
Route::post('/central/bank-options', [\App\Http\Controllers\Central\PayoutBankOptionController::class, 'store'])->name('central.bank-options.store');
Route::put('/central/bank-options/{bank}', [\App\Http\Controllers\Central\PayoutBankOptionController::class, 'update'])->name('central.bank-options.update');







        Route::get('/manage/payouts', [TenantPayoutRequestController::class, 'index'])->middleware('auth')->name('tenant.payouts.index');
        Route::post('/manage/payouts', [TenantPayoutRequestController::class, 'store'])->middleware('auth')->name('tenant.payouts.store');



        Route::get('/central/payout-requests', [CentralPayoutRequestController::class, 'index'])->name('central.payout-requests.index');

        Route::get('/central/payout-batches', [CentralPayoutBatchController::class, 'index'])->name('central.payout-batches.index');

                Route::get('/central/accounting/exports', fn () => Inertia::render('central/accounting/Exports'))->middleware('auth')->name('central.accounting.exports.index');
Route::get('/central/accounting/exports/payout-requests', [AccountingExportController::class, 'payoutRequests'])->middleware('auth')->name('central.accounting.exports.payout-requests');
        Route::get('/central/accounting/exports/payout-batches', [AccountingExportController::class, 'payoutBatches'])->middleware('auth')->name('central.accounting.exports.payout-batches');
        Route::get('/central/accounting/exports/payout-ledger', [AccountingExportController::class, 'payoutLedger'])->middleware('auth')->name('central.accounting.exports.payout-ledger');
        Route::get('/central/accounting/exports/platform-transactions', [AccountingExportController::class, 'platformTransactions'])->middleware('auth')->name('central.accounting.exports.platform-transactions');
        Route::post('/central/payout-batches', [CentralPayoutBatchController::class, 'store'])->name('central.payout-batches.store');
        Route::post('/central/payout-batches/{payoutBatch}/mark-paid', [CentralPayoutBatchController::class, 'markPaid'])->name('central.payout-batches.mark-paid');
        Route::post('/central/payout-batches/{payoutBatch}/cancel', [CentralPayoutBatchController::class, 'cancel'])->name('central.payout-batches.cancel');
        Route::post('/central/payout-requests/{payoutRequest}/approve', [CentralPayoutRequestController::class, 'approve'])->name('central.payout-requests.approve');
        Route::post('/central/payout-requests/{payoutRequest}/reject', [CentralPayoutRequestController::class, 'reject'])->name('central.payout-requests.reject');
        Route::post('/central/payout-requests/{payoutRequest}/mark-paid', [CentralPayoutRequestController::class, 'markPaid'])->name('central.payout-requests.mark-paid');


Route::domain('{tenant}.localhost')
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
    ])
    ->get('/orders', [\App\Http\Controllers\Tenant\OrderController::class, 'index'])
    ->name('orders.customer.index');

Route::domain('{tenant}.localhost')
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        'auth',
    ])
    ->get('/manage/website', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'index'])
    ->name('tenant.website.index');


Route::domain('{tenant}.localhost')
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        'auth',
    ])
    ->post('/manage/website/pages', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'storePage'])
    ->name('tenant.website.pages.store');

Route::domain('{tenant}.localhost')
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        'auth',
    ])
    ->get('/manage/website/homepage/editor', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'homepageEditor'])
    ->name('tenant.website.homepage.editor');

Route::domain('{tenant}.localhost')
    ->middleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        'auth',
    ])
    ->post('/manage/website/media', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'uploadMedia'])
    ->name('tenant.website.media.upload');

Route::domain('{tenant}.localhost')
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        'auth',
    ])
    ->get('/manage/website/pages/{themePage}/editor', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'pageEditor'])
    ->name('tenant.website.pages.editor');

Route::domain('{tenant}.localhost')
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        'auth',
    ])
    ->put('/manage/website/pages/{themePage}', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'updatePage'])
    ->name('tenant.website.pages.update');

Route::domain('{tenant}.localhost')
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        'auth',
    ])
    ->post('/manage/website/pages/{themePage}/publish', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'publishPage'])
    ->name('tenant.website.pages.publish');

Route::domain('{tenant}.localhost')->middleware(['web', 'auth', InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])
    ->post('/manage/website/pages/{themePage}/reset', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'resetPageDraft'])
    ->name('tenant.website.pages.reset');


Route::domain('{tenant}.localhost')
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        'auth',
    ])
    ->put('/manage/website/homepage', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'updateHomepage'])
    ->name('tenant.website.homepage.update');

Route::domain('{tenant}.localhost')
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        'auth',
    ])
    ->post('/manage/website/homepage/publish', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'publishHomepage'])
    ->name('tenant.website.homepage.publish');

Route::domain('{tenant}.localhost')->middleware(['web', 'auth', InitializeTenancyByDomain::class, PreventAccessFromCentralDomains::class])
    ->post('/manage/website/homepage/reset', [\App\Http\Controllers\Tenant\Manage\WebsiteBuilderController::class, 'resetHomepageDraft'])
    ->name('tenant.website.homepage.reset');

