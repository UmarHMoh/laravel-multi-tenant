<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$report = [];

function add_action_result(string $section, string $name, string $status, string $message, array $data = []): void
{
    global $report;

    $report[] = [
        'section' => $section,
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

function make_post_request(string $uri, array $data = []): Request
{
    $request = Request::create($uri, 'POST', $data);
    $request->setLaravelSession(app('session.store'));
    app()->instance('request', $request);

    return $request;
}

function make_put_request(string $uri, array $data = []): Request
{
    $request = Request::create($uri, 'PUT', $data);
    $request->setLaravelSession(app('session.store'));
    app()->instance('request', $request);

    return $request;
}

/*
|--------------------------------------------------------------------------
| 1. Tenant billing test payment
|--------------------------------------------------------------------------
*/

try {
    DB::connection('mysql')->beginTransaction();

    $tenant = \App\Models\Tenant::with('currentSubscription.plan')->find('tenant1');

    if (! $tenant || ! $tenant->currentSubscription) {
        add_action_result('tenant_actions', 'tenant billing test payment', 'FAIL', 'tenant1 has no subscription.');
    } else {
        $beforePaymentCount = \App\Models\TenantSubscriptionPayment::where('tenant_id', $tenant->id)->count();
        $beforeRenewal = $tenant->currentSubscription->renews_at?->copy();

        tenancy()->initialize($tenant);

        $request = make_post_request('/manage/billing/test-payment');

        app(\App\Http\Controllers\Tenant\Manage\BillingController::class)->testPayment($request);

        tenancy()->end();

        $afterPaymentCount = \App\Models\TenantSubscriptionPayment::where('tenant_id', $tenant->id)->count();
        $freshTenant = \App\Models\Tenant::with('currentSubscription')->find($tenant->id);
        $latestPayment = \App\Models\TenantSubscriptionPayment::where('tenant_id', $tenant->id)->latest()->first();

        $passed = $afterPaymentCount === $beforePaymentCount + 1
            && $latestPayment
            && $latestPayment->source === 'tenant_portal_test'
            && $latestPayment->status === 'paid'
            && $freshTenant->currentSubscription->renews_at->gt($beforeRenewal);

        add_action_result('tenant_actions', 'tenant billing test payment', $passed ? 'PASS' : 'FAIL', $passed
            ? 'Tenant billing test payment creates payment and extends renewal.'
            : 'Tenant billing test payment did not produce expected database changes.',
            [
                'before_payment_count' => $beforePaymentCount,
                'after_payment_count' => $afterPaymentCount,
                'latest_payment_source' => $latestPayment?->source,
                'latest_payment_status' => $latestPayment?->status,
                'before_renewal' => $beforeRenewal?->toDateTimeString(),
                'after_renewal' => $freshTenant->currentSubscription->renews_at?->toDateTimeString(),
            ]
        );
    }

    DB::connection('mysql')->rollBack();
} catch (Throwable $e) {
    try {
        tenancy()->end();
    } catch (Throwable $ignored) {
    }

    try {
        DB::connection('mysql')->rollBack();
    } catch (Throwable $ignored) {
    }

    add_action_result('tenant_actions', 'tenant billing test payment', 'FAIL', $e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}

/*
|--------------------------------------------------------------------------
| 2. Tenant payout account save
|--------------------------------------------------------------------------
*/

try {
    DB::connection('mysql')->beginTransaction();

    $tenant = \App\Models\Tenant::find('tenant1');

    tenancy()->initialize($tenant);

    $request = make_post_request('/manage/payout-account', [
        'type' => 'bank',
        'account_holder_name' => 'Audit Account Holder',
        'bank_name' => 'Republic Bank',
        'bank_account_number' => '123456789',
        'branch_transit_number' => '00123',
        'bank_account_type' => 'savings',
        'wipay_account_email' => null,
        'notes' => 'Audit payout account save.',
    ]);

    app(\App\Http\Controllers\Tenant\Manage\PayoutAccountController::class)->store($request);

    tenancy()->end();

    $account = \App\Models\TenantPayoutAccount::where('tenant_id', 'tenant1')->latest()->first();

    $branchTransitNumber = $account?->branch_transit_number;

    try {
        if (is_string($branchTransitNumber) && str_starts_with($branchTransitNumber, 'eyJ')) {
            $branchTransitNumber = Crypt::decryptString($branchTransitNumber);
        }
    } catch (Throwable $ignored) {
    }

    $passed = $account
        && $account->account_holder_name === 'Audit Account Holder'
        && $account->bank_name === 'Republic Bank'
        && $branchTransitNumber === '00123'
        && $account->status === 'pending';

    add_action_result('tenant_actions', 'tenant payout account save', $passed ? 'PASS' : 'FAIL', $passed
        ? 'Tenant payout account saves expected fields and resets status to pending.'
        : 'Tenant payout account did not save expected fields.',
        [
            'account_id' => $account?->id,
            'account_holder_name' => $account?->account_holder_name,
            'bank_name' => $account?->bank_name,
            'branch_transit_number' => $branchTransitNumber,
            'raw_branch_transit_number' => $account?->branch_transit_number,
            'status' => $account?->status,
        ]
    );

    DB::connection('mysql')->rollBack();
} catch (Throwable $e) {
    try {
        tenancy()->end();
    } catch (Throwable $ignored) {
    }

    try {
        DB::connection('mysql')->rollBack();
    } catch (Throwable $ignored) {
    }

    add_action_result('tenant_actions', 'tenant payout account save', 'FAIL', $e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}

/*
|--------------------------------------------------------------------------
| 3. Tenant store settings save
|--------------------------------------------------------------------------
*/

try {
    DB::connection('mysql')->beginTransaction();

    $tenant = \App\Models\Tenant::find('tenant1');

    tenancy()->initialize($tenant);

    $request = make_put_request('/manage/store-settings', [
        'store_name' => 'Audit Store Name',
        'store_email' => 'audit-store@example.com',
        'store_description' => 'Audit description',
        'store_phone' => '8681234567',
        'store_currency' => 'TTD',
        'business_address' => 'Audit Business Address',
        'delivery_fee' => 25,
        'cash_on_delivery_enabled' => true,
        'instagram_url' => 'https://instagram.com/audit',
        'facebook_url' => 'https://facebook.com/audit',
        'tiktok_url' => 'https://tiktok.com/@audit',
        'whatsapp_number' => '8681234567',
    ]);

    app(\App\Http\Controllers\Tenant\Manage\StoreSettingsController::class)->update($request);

    tenancy()->end();

    $rawData = DB::connection('mysql')->table('tenants')->where('id', 'tenant1')->value('data');
    $data = json_decode($rawData ?: '{}', true);

    $passed = ($data['store_name'] ?? null) === 'Audit Store Name'
        && ($data['store_email'] ?? null) === 'audit-store@example.com'
        && ($data['store_currency'] ?? null) === 'TTD'
        && (float) ($data['delivery_fee'] ?? 0) === 25.0
        && (bool) ($data['cash_on_delivery_enabled'] ?? false) === true;

    add_action_result('tenant_actions', 'tenant store settings save', $passed ? 'PASS' : 'FAIL', $passed
        ? 'Tenant store settings save expected JSON data.'
        : 'Tenant store settings did not save expected JSON data.',
        [
            'saved_data' => $data,
        ]
    );

    DB::connection('mysql')->rollBack();
} catch (Throwable $e) {
    try {
        tenancy()->end();
    } catch (Throwable $ignored) {
    }

    try {
        DB::connection('mysql')->rollBack();
    } catch (Throwable $ignored) {
    }

    add_action_result('tenant_actions', 'tenant store settings save', 'FAIL', $e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}

/*
|--------------------------------------------------------------------------
| 4. Central manual subscription payment
|--------------------------------------------------------------------------
*/

try {
    DB::connection('mysql')->beginTransaction();

    $tenant = \App\Models\Tenant::with('currentSubscription.plan')->find('tenant1');

    $beforePaymentCount = \App\Models\TenantSubscriptionPayment::where('tenant_id', $tenant->id)->count();
    $beforeRenewal = $tenant->currentSubscription->renews_at?->copy();

    $request = make_post_request('/central/tenants/tenant1/subscription-payment', [
        'amount' => 250,
        'currency' => 'TTD',
        'paid_at' => now()->format('Y-m-d\TH:i'),
        'months' => 1,
        'payment_method' => 'manual',
        'reference' => 'AUDIT-MANUAL-PAYMENT',
        'payer_name' => 'Audit Payer',
        'payer_email' => 'audit-payer@example.com',
        'card_brand' => null,
        'card_last4' => null,
        'notes' => 'Audit manual subscription payment.',
    ]);

    app(\App\Http\Controllers\Central\TenantSubscriptionPaymentController::class)->store($request, $tenant);

    $afterPaymentCount = \App\Models\TenantSubscriptionPayment::where('tenant_id', $tenant->id)->count();
    $freshTenant = \App\Models\Tenant::with('currentSubscription')->find($tenant->id);
    $latestPayment = \App\Models\TenantSubscriptionPayment::where('tenant_id', $tenant->id)->latest()->first();

    $passed = $afterPaymentCount === $beforePaymentCount + 1
        && $latestPayment
        && $latestPayment->reference === 'AUDIT-MANUAL-PAYMENT'
        && $latestPayment->payer_name === 'Audit Payer'
        && $latestPayment->payer_email === 'audit-payer@example.com'
        && $freshTenant->currentSubscription->renews_at->gt($beforeRenewal);

    add_action_result('central_actions', 'central manual subscription payment', $passed ? 'PASS' : 'FAIL', $passed
        ? 'Central manual payment creates payment and extends renewal.'
        : 'Central manual payment did not produce expected result.',
        [
            'before_payment_count' => $beforePaymentCount,
            'after_payment_count' => $afterPaymentCount,
            'latest_reference' => $latestPayment?->reference,
            'latest_payer_name' => $latestPayment?->payer_name,
            'latest_payer_email' => $latestPayment?->payer_email,
            'before_renewal' => $beforeRenewal?->toDateTimeString(),
            'after_renewal' => $freshTenant->currentSubscription->renews_at?->toDateTimeString(),
        ]
    );

    DB::connection('mysql')->rollBack();
} catch (Throwable $e) {
    try {
        DB::connection('mysql')->rollBack();
    } catch (Throwable $ignored) {
    }

    add_action_result('central_actions', 'central manual subscription payment', 'FAIL', $e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}

/*
|--------------------------------------------------------------------------
| 5. Central payout account review actions
|--------------------------------------------------------------------------
*/

foreach ([
    ['approve', 'approve'],
    ['reject', 'reject'],
    ['pending', 'markPending'],
] as [$name, $method]) {
    try {
        DB::connection('mysql')->beginTransaction();

        $account = \App\Models\TenantPayoutAccount::where('tenant_id', 'tenant1')->latest()->first();

        if (! $account) {
            add_action_result('central_actions', 'central payout account ' . $name, 'FAIL', 'No payout account found for tenant1.');
            DB::connection('mysql')->rollBack();
            continue;
        }

        $request = make_post_request('/central/payout-accounts/' . $account->id . '/' . $name, [
            'review_notes' => 'Audit review note for ' . $name,
        ]);

        app(\App\Http\Controllers\Central\PayoutAccountReviewController::class)->{$method}($request, $account);

        $fresh = $account->fresh();

        $expectedStatus = $name === 'approve'
            ? 'verified'
            : ($name === 'reject' ? 'rejected' : 'pending');

        $passed = $fresh->status === $expectedStatus
            && filled($fresh->reviewed_at);

        add_action_result('central_actions', 'central payout account ' . $name, $passed ? 'PASS' : 'FAIL', $passed
            ? 'Central payout account review action updates status correctly.'
            : 'Central payout account review action did not update status correctly.',
            [
                'account_id' => $account->id,
                'expected_status' => $expectedStatus,
                'actual_status' => $fresh->status,
                'reviewed_at' => $fresh->reviewed_at?->toDateTimeString(),
                'review_notes' => $fresh->review_notes,
            ]
        );

        DB::connection('mysql')->rollBack();
    } catch (Throwable $e) {
        try {
            DB::connection('mysql')->rollBack();
        } catch (Throwable $ignored) {
        }

        add_action_result('central_actions', 'central payout account ' . $name, 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| 6. Central platform settings update
|--------------------------------------------------------------------------
*/

try {
    DB::connection('mysql')->beginTransaction();

    $request = make_put_request('/central/settings', [
        'platform_name' => 'Audit Platform',
        'owner_email' => 'audit-owner@example.com',
        'subscription_currency' => 'TTD',
        'renewal_grace_days' => 7,
        'auto_deactivate_overdue_tenants' => true,
        'login_heading' => 'Audit Login Heading',
        'login_subheading' => 'Audit Login Subheading',
    ]);

    app(\App\Http\Controllers\Central\PlatformSettingController::class)->update($request);

    $setting = \App\Models\PlatformSetting::first();

    $passed = $setting
        && $setting->platform_name === 'Audit Platform'
        && $setting->owner_email === 'audit-owner@example.com'
        && $setting->subscription_currency === 'TTD'
        && (int) $setting->renewal_grace_days === 7;

    add_action_result('central_actions', 'central platform settings update', $passed ? 'PASS' : 'FAIL', $passed
        ? 'Central platform settings update saves expected fields.'
        : 'Central platform settings update did not save expected fields.',
        [
            'platform_name' => $setting?->platform_name,
            'owner_email' => $setting?->owner_email,
            'subscription_currency' => $setting?->subscription_currency,
            'renewal_grace_days' => $setting?->renewal_grace_days,
        ]
    );

    DB::connection('mysql')->rollBack();
} catch (Throwable $e) {
    try {
        DB::connection('mysql')->rollBack();
    } catch (Throwable $ignored) {
    }

    add_action_result('central_actions', 'central platform settings update', 'FAIL', $e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}

/*
|--------------------------------------------------------------------------
| 7. Central payment settings update
|--------------------------------------------------------------------------
*/

try {
    DB::connection('mysql')->beginTransaction();

    $request = make_put_request('/central/payment-settings', [
        'name' => 'Audit WiPay Configuration',
                'config_type' => 'saved',
                'provider' => 'wipay',
        'environment' => 'sandbox',
        'country_code' => 'TT',
        'currency' => 'TTD',
        'account_number' => 'AUDIT123',
        'api_key' => 'audit-api-key',
        'fee_structure' => 'merchant_absorb',
                'auth_type' => 'api_key',
        'processor_fee_percent' => 3.0,
        'processor_fee_fixed' => 1.0,
        'is_active' => true,
    ]);

    app(\App\Http\Controllers\Central\PaymentSettingController::class)->update($request);

    $setting = \App\Models\PaymentSetting::where('name', 'Audit WiPay Configuration')
        ->where('provider', 'wipay')
        ->latest()
        ->first();

    $passed = $setting
        && $setting->name === 'Audit WiPay Configuration'
        && $setting->config_type === 'saved'
        && $setting->provider === 'wipay'
        && $setting->auth_type === 'api_key'
        && $setting->environment === 'sandbox'
        && $setting->country_code === 'TT'
        && $setting->currency === 'TTD'
        && $setting->account_number === 'AUDIT123'
        && (float) $setting->processor_fee_percent === 3.0
        && (float) $setting->processor_fee_fixed === 1.0
        && (bool) $setting->is_active === true;

    add_action_result('central_actions', 'central payment settings update', $passed ? 'PASS' : 'FAIL', $passed
        ? 'Central payment settings update saves expected universal payment configuration fields.'
        : 'Central payment settings update did not save expected fields.',
        [
            'id' => $setting?->id,
            'name' => $setting?->name,
            'config_type' => $setting?->config_type,
            'provider' => $setting?->provider,
            'auth_type' => $setting?->auth_type,
            'environment' => $setting?->environment,
            'country_code' => $setting?->country_code,
            'currency' => $setting?->currency,
            'account_number' => $setting?->account_number,
            'processor_fee_percent' => $setting?->processor_fee_percent,
            'processor_fee_fixed' => $setting?->processor_fee_fixed,
            'is_active' => $setting?->is_active,
        ]
    );

    DB::connection('mysql')->rollBack();
} catch (Throwable $e) {
    try {
        DB::connection('mysql')->rollBack();
    } catch (Throwable $ignored) {
    }

    add_action_result('central_actions', 'central payment settings update', 'FAIL', $e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
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
    'failures' => array_values(array_filter($report, fn ($item) => $item['status'] === 'FAIL')),
    'warnings' => array_values(array_filter($report, fn ($item) => $item['status'] === 'WARN')),
    'all_findings' => $report,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
