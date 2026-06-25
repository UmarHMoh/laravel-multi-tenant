<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$report = [];

function add_result(string $section, string $name, string $status, string $message, array $data = []): void
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

function pass_result(string $section, string $name, string $message, array $data = []): void
{
    add_result($section, $name, 'PASS', $message, $data);
}

function fail_result(string $section, string $name, string $message, array $data = []): void
{
    add_result($section, $name, 'FAIL', $message, $data);
}

function warn_result(string $section, string $name, string $message, array $data = []): void
{
    add_result($section, $name, 'WARN', $message, $data);
}

function info_result(string $section, string $name, string $message, array $data = []): void
{
    add_result($section, $name, 'INFO', $message, $data);
}

function safe_call(string $section, string $name, callable $callback): void
{
    try {
        $callback();
    } catch (Throwable $e) {
        fail_result($section, $name, $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}

function file_contains(string $path, array $needles): array
{
    if (! file_exists(base_path($path))) {
        return [
            'exists' => false,
            'missing' => $needles,
        ];
    }

    $text = file_get_contents(base_path($path));

    $missing = [];

    foreach ($needles as $needle) {
        if (! str_contains($text, $needle)) {
            $missing[] = $needle;
        }
    }

    return [
        'exists' => true,
        'missing' => $missing,
    ];
}

function decrypt_or_hidden(?string $value): ?string
{
    if (! $value) {
        return null;
    }

    try {
        return Crypt::decryptString($value);
    } catch (Throwable $e) {
        return 'Hidden or not decryptable';
    }
}

$centralConnection = config('tenancy.database.central_connection') ?: config('database.default');

info_result('environment', 'app', 'Application environment loaded.', [
    'app_name' => config('app.name'),
    'app_env' => config('app.env'),
    'app_url' => config('app.url'),
    'default_db_connection' => config('database.default'),
    'central_connection' => $centralConnection,
]);

/*
|--------------------------------------------------------------------------
| 1. Route audit
|--------------------------------------------------------------------------
*/

safe_call('routes', 'required route names', function () {
    $routes = [
        'central.login',
        'central.dashboard',
        'central.tenants.index',
        'central.tenants.show',
        'central.tenants.store',
        'central.tenants.assign-plan',
        'central.tenants.subscription-payment.store',

        'central.payouts.index',
        'central.payouts.show',
        'central.payouts.mark-paid',
        'central.payouts.batch-show',

        'central.payment-settings.edit',
        'central.payment-settings.update',
        'central.settings.edit',
        'central.settings.update',

        'manage.billing.index',
        'manage.billing.test-payment',
        'manage.payout-account',
        'manage.payout-account.store',
        'manage.store-settings.edit',
        'manage.store-settings.update',
    ];

    foreach ($routes as $name) {
        if (Route::has($name)) {
            pass_result('routes', $name, 'Route exists.');
        } else {
            fail_result('routes', $name, 'Route is missing.');
        }
    }

    $optionalRoutes = [
        'central.payout-accounts.approve',
        'central.payout-accounts.reject',
        'central.payout-accounts.pending',
        'central.bank-options.index',
        'central.bank-options.store',
        'central.bank-options.update',
    ];

    foreach ($optionalRoutes as $name) {
        if (Route::has($name)) {
            pass_result('routes', $name, 'Optional improved route exists.');
        } else {
            warn_result('routes', $name, 'Optional improved route is missing. Add this if the related feature should exist.');
        }
    }
});

/*
|--------------------------------------------------------------------------
| 2. Database table and column audit
|--------------------------------------------------------------------------
*/

safe_call('database', 'tables and columns', function () {
    $tables = [
        'tenants' => [
            'id',
            'name',
            'email',
            'is_active',
            'data',
        ],
        'tenant_subscriptions' => [
            'id',
            'tenant_id',
            'plan_id',
            'status',
            'starts_at',
            'renews_at',
            'trial_ends_at',
            'ends_at',
        ],
        'tenant_subscription_payments' => [
            'id',
            'tenant_id',
            'tenant_subscription_id',
            'plan_id',
            'amount',
            'currency',
            'status',
            'source',
            'paid_at',
            'previous_renews_at',
            'new_renews_at',
            'payment_method',
            'provider',
            'provider_transaction_id',
            'payer_name',
            'payer_email',
            'card_brand',
            'card_last4',
            'receipt_url',
            'recorded_by',
            'reference',
            'notes',
            'metadata',
        ],
        'tenant_payout_accounts' => [
            'id',
            'tenant_id',
            'type',
            'account_holder_name',
            'bank_name',
            'bank_account_number',
            'branch_transit_number',
            'bank_account_type',
            'wipay_account_email',
            'status',
            'notes',
            'review_notes',
            'reviewed_at',
            'reviewed_by',
        ],
        'platform_settings' => [
            'id',
            'platform_name',
            'owner_email',
            'subscription_currency',
            'renewal_grace_days',
            'auto_deactivate_overdue_tenants',
            'login_heading',
            'login_subheading',
            'metadata',
        ],
        'payment_settings' => [
            'id',
            'provider',
            'environment',
            'country_code',
            'currency',
            'account_number',
            'api_key',
            'fee_structure',
            'processor_fee_percent',
            'processor_fee_fixed',
            'is_active',
            'metadata',
        ],
        'payout_bank_options' => [
            'id',
            'name',
            'is_active',
        ],
    ];

    foreach ($tables as $table => $requiredColumns) {
        if (! Schema::hasTable($table)) {
            fail_result('database', $table, 'Table is missing.');
            continue;
        }

        $actualColumns = Schema::getColumnListing($table);
        $missing = array_values(array_diff($requiredColumns, $actualColumns));

        if (count($missing) === 0) {
            pass_result('database', $table, 'Table exists with required columns.', [
                'columns' => $actualColumns,
            ]);
        } else {
            fail_result('database', $table, 'Table exists but columns are missing.', [
                'missing_columns' => $missing,
                'actual_columns' => $actualColumns,
            ]);
        }
    }
});

/*
|--------------------------------------------------------------------------
| 3. File and frontend content audit
|--------------------------------------------------------------------------
*/

safe_call('files', 'important files', function () {
    $checks = [
        'app/Http/Controllers/Central/TenantController.php' => [
            'subscriptionPayments',
            'latestSubscriptionPayment',
        ],
        'app/Http/Controllers/Central/TenantSubscriptionPaymentController.php' => [
            'TenantSubscriptionPayment::create',
            'renews_at',
            'with(\'success\'',
        ],
        'app/Http/Controllers/Central/PayoutController.php' => [
            'markPaid',
            'verified',
        ],
        'app/Http/Controllers/Central/PayoutAccountReviewController.php' => [
            'approve',
            'reject',
            'markPending',
        ],
        'app/Http/Controllers/Tenant/Manage/PayoutAccountController.php' => [
            'PayoutBankOption',
            'branch_transit_number',
            'pending',
        ],
        'app/Http/Controllers/Tenant/Manage/StoreSettingsController.php' => [
            'store_name',
            'cash_on_delivery_enabled',
            'with(\'success\'',
        ],
        'resources/js/pages/central/tenants/Show.vue' => [
            'isRecordingPayment',
            'Record New Payment',
            'Save Payment & Extend Renewal',
        ],
        'resources/js/pages/tenant/payouts/Account.vue' => [
            'bankOptions',
            'branch_transit_number',
            'Save Payout Account',
        ],
        'resources/js/pages/central/payouts/Show.vue' => [
            'Approve',
            'Reject',
            'Mark Pending',
            'Account Number',
            'Branch / Transit Number',
        ],
        'resources/js/pages/tenant/settings/Store.vue' => [
            'Edit Settings',
            'Save Store Settings',
            'cash_on_delivery_enabled',
        ],
        'resources/js/components/FlashMessages.vue' => [
            'success',
            'error',
        ],
    ];

    foreach ($checks as $path => $needles) {
        $result = file_contains($path, $needles);

        if (! $result['exists']) {
            fail_result('files', $path, 'File is missing.', [
                'expected_strings' => $needles,
            ]);
            continue;
        }

        if (count($result['missing']) === 0) {
            pass_result('files', $path, 'File exists and contains expected feature markers.');
        } else {
            warn_result('files', $path, 'File exists but some expected feature markers are missing.', [
                'missing_strings' => $result['missing'],
            ]);
        }
    }
});

/*
|--------------------------------------------------------------------------
| 4. Current saved data audit
|--------------------------------------------------------------------------
*/

safe_call('data', 'tenants summary', function () {
    if (! class_exists(\App\Models\Tenant::class)) {
        fail_result('data', 'Tenant model', 'Tenant model does not exist.');
        return;
    }

    $tenants = \App\Models\Tenant::with([
        'domains',
        'currentSubscription.plan',
        'subscriptionPayments',
        'payoutAccounts',
    ])->get();

    pass_result('data', 'tenants loaded', 'Tenants loaded successfully.', [
        'count' => $tenants->count(),
        'tenants' => $tenants->map(fn ($tenant) => [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'email' => $tenant->email,
            'is_active' => $tenant->is_active,
            'domains' => $tenant->domains->pluck('domain')->values(),
            'current_subscription' => $tenant->currentSubscription ? [
                'id' => $tenant->currentSubscription->id,
                'status' => $tenant->currentSubscription->status,
                'plan' => $tenant->currentSubscription->plan?->name,
                'renews_at' => $tenant->currentSubscription->renews_at?->toDateTimeString(),
                'ends_at' => $tenant->currentSubscription->ends_at?->toDateTimeString(),
            ] : null,
            'subscription_payments_count' => $tenant->subscriptionPayments->count(),
            'payout_accounts' => $tenant->payoutAccounts->map(fn ($account) => [
                'id' => $account->id,
                'type' => $account->type,
                'status' => $account->status,
                'holder' => $account->account_holder_name,
                'bank' => $account->bank_name,
                'account_type' => $account->bank_account_type,
                'has_account_number' => ! empty($account->bank_account_number),
                'has_branch_transit' => ! empty($account->branch_transit_number),
                'notes' => $account->notes,
                'review_notes' => $account->review_notes,
                'reviewed_at' => $account->reviewed_at?->toDateTimeString(),
                'reviewed_by' => $account->reviewed_by,
            ])->values(),
        ])->values(),
    ]);
});

safe_call('data', 'subscription payments', function () {
    if (! class_exists(\App\Models\TenantSubscriptionPayment::class)) {
        fail_result('data', 'TenantSubscriptionPayment model', 'TenantSubscriptionPayment model does not exist.');
        return;
    }

    $payments = \App\Models\TenantSubscriptionPayment::with(['tenant', 'plan'])
        ->latest()
        ->take(10)
        ->get();

    pass_result('data', 'latest subscription payments', 'Latest subscription payments loaded.', [
        'count' => $payments->count(),
        'payments' => $payments->map(fn ($payment) => [
            'id' => $payment->id,
            'tenant_id' => $payment->tenant_id,
            'tenant' => $payment->tenant?->name,
            'plan' => $payment->plan?->name,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'source' => $payment->source,
            'paid_at' => $payment->paid_at?->toDateString(),
            'previous_renews_at' => $payment->previous_renews_at?->toDateString(),
            'new_renews_at' => $payment->new_renews_at?->toDateString(),
            'method' => $payment->payment_method,
            'payer' => $payment->payer_name,
            'payer_email' => $payment->payer_email,
            'card' => trim(($payment->card_brand ?? '') . ' ' . ($payment->card_last4 ?? '')),
            'reference' => $payment->reference,
            'notes' => $payment->notes,
        ])->values(),
    ]);
});

safe_call('data', 'payout bank options', function () {
    if (! class_exists(\App\Models\PayoutBankOption::class)) {
        warn_result('data', 'PayoutBankOption model', 'PayoutBankOption model does not exist yet.');
        return;
    }

    if (! Schema::hasTable('payout_bank_options')) {
        warn_result('data', 'payout_bank_options table', 'payout_bank_options table does not exist yet.');
        return;
    }

    $banks = \App\Models\PayoutBankOption::orderBy('name')->get(['id', 'name', 'is_active']);

    if ($banks->isEmpty()) {
        warn_result('data', 'bank options', 'No bank options are saved.');
    } else {
        pass_result('data', 'bank options', 'Bank options loaded.', [
            'count' => $banks->count(),
            'banks' => $banks->toArray(),
        ]);
    }
});

/*
|--------------------------------------------------------------------------
| 5. Non-destructive action tests with rollback
|--------------------------------------------------------------------------
*/

safe_call('actions', 'central subscription payment controller', function () use ($centralConnection) {
    DB::connection($centralConnection)->beginTransaction();

    try {
        $tenant = \App\Models\Tenant::with('currentSubscription.plan')->find('tenant1');

        if (! $tenant) {
            warn_result('actions', 'manual subscription payment', 'tenant1 does not exist. Skipping action test.');
            DB::connection($centralConnection)->rollBack();
            return;
        }

        $beforeCount = \App\Models\TenantSubscriptionPayment::count();
        $beforeRenewal = $tenant->currentSubscription?->renews_at?->toDateTimeString();

        if (! $tenant->currentSubscription) {
            warn_result('actions', 'manual subscription payment', 'tenant1 has no current subscription. Skipping action test.');
            DB::connection($centralConnection)->rollBack();
            return;
        }

        $request = Request::create(
            '/central/tenants/' . $tenant->id . '/subscription-payment',
            'POST',
            [
                'amount' => $tenant->currentSubscription->plan?->monthly_price ?? 250,
                'currency' => 'TTD',
                'paid_at' => now()->toDateString(),
                'months' => 1,
                'payment_method' => 'manual',
                'reference' => 'AUDIT-MANUAL-SUBSCRIPTION',
                'payer_name' => $tenant->name,
                'payer_email' => $tenant->email,
                'card_brand' => 'Visa',
                'card_last4' => '4242',
                'notes' => 'Rollback audit test.',
            ]
        );

        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        $response = app(\App\Http\Controllers\Central\TenantSubscriptionPaymentController::class)
            ->store($request, $tenant);

        $afterCount = \App\Models\TenantSubscriptionPayment::count();

        $freshTenant = \App\Models\Tenant::with('currentSubscription')->find($tenant->id);
        $createdPayment = \App\Models\TenantSubscriptionPayment::where('reference', 'AUDIT-MANUAL-SUBSCRIPTION')->latest()->first();

        if ($afterCount === $beforeCount + 1 && $createdPayment) {
            pass_result('actions', 'manual subscription payment', 'Controller created payment and updated renewal date. Rolled back after test.', [
                'before_count' => $beforeCount,
                'after_count_inside_transaction' => $afterCount,
                'before_renewal' => $beforeRenewal,
                'after_renewal_inside_transaction' => $freshTenant->currentSubscription?->renews_at?->toDateTimeString(),
                'created_payment_id_inside_transaction' => $createdPayment->id,
                'response_class' => is_object($response) ? get_class($response) : gettype($response),
            ]);
        } else {
            fail_result('actions', 'manual subscription payment', 'Controller did not create expected payment.', [
                'before_count' => $beforeCount,
                'after_count_inside_transaction' => $afterCount,
                'created_payment_found' => (bool) $createdPayment,
            ]);
        }

        DB::connection($centralConnection)->rollBack();
    } catch (Throwable $e) {
        DB::connection($centralConnection)->rollBack();
        throw $e;
    }
});

safe_call('actions', 'payout account approval/reject/pending', function () use ($centralConnection) {
    if (! class_exists(\App\Http\Controllers\Central\PayoutAccountReviewController::class)) {
        warn_result('actions', 'payout account review', 'PayoutAccountReviewController does not exist. Skipping.');
        return;
    }

    DB::connection($centralConnection)->beginTransaction();

    try {
        $tenant = \App\Models\Tenant::find('tenant1') ?: \App\Models\Tenant::first();

        if (! $tenant) {
            warn_result('actions', 'payout account review', 'No tenant exists. Skipping.');
            DB::connection($centralConnection)->rollBack();
            return;
        }

        $data = [
            'tenant_id' => $tenant->id,
            'type' => 'bank',
            'account_holder_name' => 'Audit Holder',
            'bank_name' => 'Republic Bank',
            'bank_account_number' => Crypt::encryptString('1234567890'),
            'bank_account_type' => 'savings',
            'status' => 'pending',
            'notes' => 'Rollback audit payout account.',
        ];

        if (Schema::hasColumn('tenant_payout_accounts', 'branch_transit_number')) {
            $data['branch_transit_number'] = Crypt::encryptString('00123');
        }

        $account = \App\Models\TenantPayoutAccount::create($data);

        $controller = app(\App\Http\Controllers\Central\PayoutAccountReviewController::class);

        $approveRequest = Request::create('/central/payout-accounts/' . $account->id . '/approve', 'POST', [
            'review_notes' => 'Audit approved.',
        ]);
        $approveRequest->setLaravelSession(app('session.store'));
        app()->instance('request', $approveRequest);
        $controller->approve($approveRequest, $account);
        $account->refresh();

        $approved = $account->status === 'verified';

        $rejectRequest = Request::create('/central/payout-accounts/' . $account->id . '/reject', 'POST', [
            'review_notes' => 'Audit rejected.',
        ]);
        $rejectRequest->setLaravelSession(app('session.store'));
        app()->instance('request', $rejectRequest);
        $controller->reject($rejectRequest, $account);
        $account->refresh();

        $rejected = $account->status === 'rejected' && $account->review_notes === 'Audit rejected.';

        $pendingRequest = Request::create('/central/payout-accounts/' . $account->id . '/pending', 'POST', [
            'review_notes' => 'Audit pending.',
        ]);
        $pendingRequest->setLaravelSession(app('session.store'));
        app()->instance('request', $pendingRequest);
        $controller->markPending($pendingRequest, $account);
        $account->refresh();

        $pending = $account->status === 'pending';

        if ($approved && $rejected && $pending) {
            pass_result('actions', 'payout account review', 'Approve, reject, and mark pending actions work. Rolled back after test.', [
                'account_id_inside_transaction' => $account->id,
                'approved_result' => $approved,
                'rejected_result' => $rejected,
                'pending_result' => $pending,
            ]);
        } else {
            fail_result('actions', 'payout account review', 'One or more payout review transitions failed.', [
                'approved_result' => $approved,
                'rejected_result' => $rejected,
                'pending_result' => $pending,
                'final_status' => $account->status,
            ]);
        }

        DB::connection($centralConnection)->rollBack();
    } catch (Throwable $e) {
        DB::connection($centralConnection)->rollBack();
        throw $e;
    }
});

safe_call('actions', 'tenant payout account submit/update', function () use ($centralConnection) {
    if (! class_exists(\App\Http\Controllers\Tenant\Manage\PayoutAccountController::class)) {
        warn_result('actions', 'tenant payout account submit/update', 'Tenant payout account controller does not exist. Skipping.');
        return;
    }

    if (! Schema::hasTable('payout_bank_options')) {
        warn_result('actions', 'tenant payout account submit/update', 'payout_bank_options table missing. Skipping.');
        return;
    }

    $bank = \App\Models\PayoutBankOption::where('is_active', true)->orderBy('name')->first();

    if (! $bank) {
        warn_result('actions', 'tenant payout account submit/update', 'No active payout bank option found. Skipping.');
        return;
    }

    DB::connection($centralConnection)->beginTransaction();

    try {
        $tenant = \App\Models\Tenant::find('tenant1');

        if (! $tenant) {
            warn_result('actions', 'tenant payout account submit/update', 'tenant1 does not exist. Skipping.');
            DB::connection($centralConnection)->rollBack();
            return;
        }

        tenancy()->initialize($tenant);

        $beforeCount = \App\Models\TenantPayoutAccount::where('tenant_id', $tenant->id)->count();

        $request = Request::create('/manage/payout-account', 'POST', [
            'type' => 'bank',
            'account_holder_name' => 'Audit Tenant Holder',
            'bank_name' => $bank->name,
            'bank_account_number' => '9876543210',
            'branch_transit_number' => '00987',
            'bank_account_type' => 'checking',
            'notes' => 'Rollback audit tenant payout account submit.',
        ]);

        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        app(\App\Http\Controllers\Tenant\Manage\PayoutAccountController::class)->store($request);

        tenancy()->end();

        $latest = \App\Models\TenantPayoutAccount::where('tenant_id', $tenant->id)->latest()->first();

        $passed = $latest
            && $latest->status === 'pending'
            && $latest->bank_name === $bank->name
            && $latest->bank_account_type === 'checking';

        if ($passed) {
            pass_result('actions', 'tenant payout account submit/update', 'Tenant payout account submit/update works. Rolled back after test.', [
                'before_count' => $beforeCount,
                'latest_account_id_inside_transaction' => $latest->id,
                'status_inside_transaction' => $latest->status,
                'bank_inside_transaction' => $latest->bank_name,
                'account_type_inside_transaction' => $latest->bank_account_type,
                'decrypted_account_number_inside_transaction' => decrypt_or_hidden($latest->bank_account_number),
                'decrypted_branch_transit_inside_transaction' => decrypt_or_hidden($latest->branch_transit_number ?? null),
            ]);
        } else {
            fail_result('actions', 'tenant payout account submit/update', 'Tenant payout account submit/update did not save expected values.', [
                'latest_account' => $latest?->toArray(),
            ]);
        }

        DB::connection($centralConnection)->rollBack();
    } catch (Throwable $e) {
        try {
            tenancy()->end();
        } catch (Throwable $ignored) {
        }

        DB::connection($centralConnection)->rollBack();
        throw $e;
    }
});

safe_call('actions', 'tenant store settings update', function () use ($centralConnection) {
    if (! class_exists(\App\Http\Controllers\Tenant\Manage\StoreSettingsController::class)) {
        warn_result('actions', 'tenant store settings update', 'StoreSettingsController does not exist. Skipping.');
        return;
    }

    DB::connection($centralConnection)->beginTransaction();

    try {
        $tenant = \App\Models\Tenant::find('tenant1');

        if (! $tenant) {
            warn_result('actions', 'tenant store settings update', 'tenant1 does not exist. Skipping.');
            DB::connection($centralConnection)->rollBack();
            return;
        }

        tenancy()->initialize($tenant);

        $request = Request::create('/manage/store-settings', 'PUT', [
            'store_name' => 'Audit Store Name',
            'store_email' => 'audit@example.com',
            'store_description' => 'Rollback audit description.',
            'store_phone' => '868-000-0000',
            'store_currency' => 'TTD',
            'business_address' => 'Rollback audit address.',
            'delivery_fee' => 25,
            'cash_on_delivery_enabled' => true,
            'instagram_url' => 'https://instagram.com/audit',
            'facebook_url' => '',
            'tiktok_url' => '',
            'whatsapp_number' => '8680000000',
        ]);

        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        app(\App\Http\Controllers\Tenant\Manage\StoreSettingsController::class)->update($request);

        tenancy()->end();

        $freshTenant = \App\Models\Tenant::find($tenant->id);

        $rawData = \Illuminate\Support\Facades\DB::connection(config('tenancy.database.central_connection') ?: config('database.default'))
            ->table('tenants')
            ->where('id', $tenant->id)
            ->value('data');

        $settings = [];

        if (is_array($rawData)) {
            $settings = $rawData;
        } elseif (is_string($rawData) && $rawData !== '') {
            $decoded = json_decode($rawData, true);
            $settings = is_array($decoded) ? $decoded : [];
        }

        $passed = ($freshTenant->name === 'Audit Store Name')
            && (($settings['store_name'] ?? null) === 'Audit Store Name')
            && (($settings['store_email'] ?? null) === 'audit@example.com')
            && (($settings['delivery_fee'] ?? null) == 25)
            && (($settings['cash_on_delivery_enabled'] ?? false) == true);

        if ($passed) {
            pass_result('actions', 'tenant store settings update', 'Tenant store settings update works. Rolled back after test.', [
                'tenant_name_inside_transaction' => $freshTenant->name,
                'raw_data_inside_transaction' => $rawData,
                'settings_inside_transaction' => $settings,
            ]);
        } else {
            fail_result('actions', 'tenant store settings update', 'Tenant store settings did not update expected values.', [
                'tenant_name_inside_transaction' => $freshTenant->name,
                'raw_data_inside_transaction' => $rawData,
                'settings_inside_transaction' => $settings,
            ]);
        }

        DB::connection($centralConnection)->rollBack();
    } catch (Throwable $e) {
        try {
            tenancy()->end();
        } catch (Throwable $ignored) {
        }

        DB::connection($centralConnection)->rollBack();
        throw $e;
    }
});

/*
|--------------------------------------------------------------------------
| 6. Payout mark-paid guard test
|--------------------------------------------------------------------------
*/

safe_call('actions', 'payout mark-paid guard', function () use ($centralConnection) {
    DB::connection($centralConnection)->beginTransaction();

    try {
        $tenant = \App\Models\Tenant::find('tenant1');

        if (! $tenant) {
            warn_result('actions', 'payout mark-paid guard', 'tenant1 does not exist. Skipping.');
            DB::connection($centralConnection)->rollBack();
            return;
        }

        $account = \App\Models\TenantPayoutAccount::where('tenant_id', $tenant->id)->latest()->first();

        if (! $account) {
            warn_result('actions', 'payout mark-paid guard', 'tenant1 has no payout account. Skipping.');
            DB::connection($centralConnection)->rollBack();
            return;
        }

        $originalStatus = $account->status;

        $account->update([
            'status' => 'pending',
        ]);

        $transactionClassExists = class_exists(\App\Models\PlatformTransaction::class);

        if (! $transactionClassExists) {
            warn_result('actions', 'payout mark-paid guard', 'PlatformTransaction model missing. Skipping.');
            DB::connection($centralConnection)->rollBack();
            return;
        }

        $transaction = \App\Models\PlatformTransaction::create([
            'tenant_id' => $tenant->id,
            'provider' => 'audit',
            'provider_transaction_id' => 'AUDIT-PAYOUT-GUARD',
            'order_reference' => 'AUDIT-ORDER',
            'customer_email' => 'audit@example.com',
            'currency' => 'TTD',
            'gross_amount' => 100,
            'processor_fee' => 4,
            'net_amount' => 96,
            'commission_rate' => 10,
            'commission_amount' => 9.60,
            'tenant_payout_amount' => 86.40,
            'payment_status' => 'paid',
            'payout_status' => 'pending',
            'paid_at' => now(),
        ]);

        $request = Request::create('/central/payouts/' . $tenant->id . '/mark-paid', 'POST', [
            'reference' => 'AUDIT-PAYOUT-REF',
            'notes' => 'Rollback audit mark paid.',
        ]);

        $request->setLaravelSession(app('session.store'));
        app()->instance('request', $request);

        $response = app(\App\Http\Controllers\Central\PayoutController::class)->markPaid($request, $tenant);

        $transaction->refresh();

        if ($transaction->payout_status === 'pending') {
            pass_result('actions', 'payout mark-paid guard', 'Pending/unverified payout account prevented payout from being marked paid. Rolled back after test.', [
                'account_original_status' => $originalStatus,
                'account_test_status' => 'pending',
                'transaction_payout_status_after_attempt' => $transaction->payout_status,
                'response_class' => is_object($response) ? get_class($response) : gettype($response),
            ]);
        } else {
            fail_result('actions', 'payout mark-paid guard', 'Payout was marked paid even though payout account was pending.', [
                'transaction_payout_status_after_attempt' => $transaction->payout_status,
            ]);
        }

        DB::connection($centralConnection)->rollBack();
    } catch (Throwable $e) {
        DB::connection($centralConnection)->rollBack();
        throw $e;
    }
});

/*
|--------------------------------------------------------------------------
| 7. Final summary
|--------------------------------------------------------------------------
*/

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
    'report' => $report,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
