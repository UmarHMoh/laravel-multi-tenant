<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

function result_item(string $section, string $status, string $message, array $data = []): array
{
    return [
        'section' => $section,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

function get_php_files(string $path): array
{
    return array_values(array_filter(glob(base_path($path), GLOB_BRACE) ?: [], 'is_file'));
}

function get_vue_files(): array
{
    $files = [];

    foreach ([
        base_path('resources/js/pages/**/*.vue'),
        base_path('resources/js/components/**/*.vue'),
        base_path('resources/js/layouts/**/*.vue'),
    ] as $pattern) {
        $files = array_merge($files, glob($pattern, GLOB_BRACE) ?: []);
    }

    return array_values(array_unique($files));
}

function relative_path(string $path): string
{
    return str_replace(base_path() . '/', '', $path);
}

function extract_useform_fields(string $text): array
{
    $fields = [];

    preg_match_all('/useForm\s*\(\s*\{(.*?)\}\s*\)/s', $text, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $block = $match[1];

        preg_match_all('/^\s*([A-Za-z0-9_]+)\s*:/m', $block, $fieldMatches);

        foreach ($fieldMatches[1] ?? [] as $field) {
            $fields[] = $field;
        }
    }

    return array_values(array_unique($fields));
}

function extract_vmodel_fields(string $text): array
{
    $fields = [];

    preg_match_all('/v-model(?:\.[A-Za-z]+)?=["\'](?:[A-Za-z0-9_]+\.)?([A-Za-z0-9_]+)["\']/', $text, $matches);

    foreach ($matches[1] ?? [] as $field) {
        $fields[] = $field;
    }

    return array_values(array_unique($fields));
}

function extract_form_actions(string $text): array
{
    $actions = [];

    $patterns = [
        'post' => '/\b([A-Za-z0-9_]+)\.post\(\s*[\'"]([^\'"]+)[\'"]/',
        'put' => '/\b([A-Za-z0-9_]+)\.put\(\s*[\'"]([^\'"]+)[\'"]/',
        'patch' => '/\b([A-Za-z0-9_]+)\.patch\(\s*[\'"]([^\'"]+)[\'"]/',
        'delete' => '/\b([A-Za-z0-9_]+)\.delete\(\s*[\'"]([^\'"]+)[\'"]/',
        'router_post' => '/router\.post\(\s*[\'"]([^\'"]+)[\'"]/',
        'router_put' => '/router\.put\(\s*[\'"]([^\'"]+)[\'"]/',
        'router_delete' => '/router\.delete\(\s*[\'"]([^\'"]+)[\'"]/',
    ];

    foreach ($patterns as $method => $regex) {
        preg_match_all($regex, $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if (str_starts_with($method, 'router_')) {
                $actions[] = [
                    'form_variable' => 'router',
                    'method' => str_replace('router_', '', $method),
                    'path' => $match[1],
                ];
            } else {
                $actions[] = [
                    'form_variable' => $match[1],
                    'method' => $method,
                    'path' => $match[2],
                ];
            }
        }
    }

    return $actions;
}

function extract_validation_fields(string $text): array
{
    $fields = [];

    preg_match_all('/\$request->validate\s*\(\s*\[(.*?)\]\s*\)/s', $text, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]\s*=>/', $match[1], $fieldMatches);

        foreach ($fieldMatches[1] ?? [] as $field) {
            $fields[] = $field;
        }
    }

    preg_match_all('/Validator::make\s*\([^,]+,\s*\[(.*?)\]\s*\)/s', $text, $matches2, PREG_SET_ORDER);

    foreach ($matches2 as $match) {
        preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]\s*=>/', $match[1], $fieldMatches);

        foreach ($fieldMatches[1] ?? [] as $field) {
            $fields[] = $field;
        }
    }

    return array_values(array_unique($fields));
}

function extract_create_update_fields(string $text): array
{
    $fields = [];

    $patterns = [
        '/::create\s*\(\s*\[(.*?)\]\s*\)/s',
        '/->create\s*\(\s*\[(.*?)\]\s*\)/s',
        '/->update\s*\(\s*\[(.*?)\]\s*\)/s',
        '/::update\s*\(\s*\[(.*?)\]\s*\)/s',
    ];

    foreach ($patterns as $pattern) {
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]\s*=>/', $match[1], $fieldMatches);

            foreach ($fieldMatches[1] ?? [] as $field) {
                $fields[] = $field;
            }
        }
    }

    return array_values(array_unique($fields));
}

function extract_model_fillable(string $text): array
{
    if (! preg_match('/protected\s+\$fillable\s*=\s*\[(.*?)\];/s', $text, $match)) {
        return [];
    }

    preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]/', $match[1], $matches);

    return array_values(array_unique($matches[1] ?? []));
}

function model_to_table_name(string $modelClass): ?string
{
    $map = [
        'Tenant' => 'tenants',
        'Plan' => 'plans',
        'TenantSubscription' => 'tenant_subscriptions',
        'TenantSubscriptionPayment' => 'tenant_subscription_payments',
        'TenantPayoutAccount' => 'tenant_payout_accounts',
        'TenantPayout' => 'tenant_payouts',
        'TenantPayoutItem' => 'tenant_payout_items',
        'PlatformTransaction' => 'platform_transactions',
        'PaymentSetting' => 'payment_settings',
        'PlatformSetting' => 'platform_settings',
        'PayoutBankOption' => 'payout_bank_options',
        'User' => 'users',
        'Product' => 'products',
        'Category' => 'categories',
        'Order' => 'orders',
        'OrderItem' => 'order_items',
        'Cart' => 'carts',
        'CartItem' => 'cart_items',
    ];

    return $map[$modelClass] ?? null;
}

$findings = [];

/*
|--------------------------------------------------------------------------
| 1. Vue form field audit
|--------------------------------------------------------------------------
*/

$vueForms = [];

foreach (get_vue_files() as $file) {
    $text = file_get_contents($file);

    $useFormFields = extract_useform_fields($text);
    $vModelFields = extract_vmodel_fields($text);
    $actions = extract_form_actions($text);

    if (count($useFormFields) || count($vModelFields) || count($actions)) {
        $vueForms[] = [
            'file' => relative_path($file),
            'useForm_fields' => $useFormFields,
            'v_model_fields' => $vModelFields,
            'actions' => $actions,
        ];

        $findings[] = result_item('vue_forms', 'INFO', 'Vue form/action detected.', [
            'file' => relative_path($file),
            'useForm_fields' => $useFormFields,
            'v_model_fields' => $vModelFields,
            'actions' => $actions,
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| 2. Controller validation and write audit
|--------------------------------------------------------------------------
*/

$controllerAudits = [];

foreach (get_php_files('app/Http/Controllers/**/*.php') as $file) {
    $text = file_get_contents($file);

    $validationFields = extract_validation_fields($text);
    $writeFields = extract_create_update_fields($text);

    if (count($validationFields) || count($writeFields)) {
        $controllerAudits[] = [
            'file' => relative_path($file),
            'validation_fields' => $validationFields,
            'write_fields' => $writeFields,
        ];

        $validatedButNotWritten = array_values(array_diff($validationFields, $writeFields));

        $safeNotWritten = [
            'password_confirmation',
            'remember',
            'current_password',
            'terms',
            'months',
            'api_key',
        ];

        $realValidatedButNotWritten = array_values(array_filter(
            $validatedButNotWritten,
            fn ($field) => ! in_array($field, $safeNotWritten, true)
        ));

        if (count($realValidatedButNotWritten) > 0) {
            $findings[] = result_item('controllers', 'WARN', 'Controller validates fields that do not appear directly in create/update arrays. May be intentional if saved into JSON/metadata or handled manually.', [
                'file' => relative_path($file),
                'validated_but_not_directly_written' => $realValidatedButNotWritten,
                'validation_fields' => $validationFields,
                'write_fields' => $writeFields,
            ]);
        } else {
            $findings[] = result_item('controllers', 'PASS', 'Controller validation/write fields look coordinated.', [
                'file' => relative_path($file),
                'validation_fields' => $validationFields,
                'write_fields' => $writeFields,
            ]);
        }
    }
}

/*
|--------------------------------------------------------------------------
| 3. Model fillable and DB column audit
|--------------------------------------------------------------------------
*/

foreach (get_php_files('app/Models/*.php') as $file) {
    $text = file_get_contents($file);
    $relative = relative_path($file);

    $modelName = basename($file, '.php');
    $fillable = extract_model_fillable($text);
    $table = model_to_table_name($modelName);

    if (! $table) {
        $findings[] = result_item('models', 'INFO', 'No table mapping configured for model.', [
            'model' => $modelName,
            'file' => $relative,
            'fillable' => $fillable,
        ]);
        continue;
    }

    if (! Schema::hasTable($table)) {
        $findings[] = result_item('models', 'WARN', 'Mapped database table does not exist on current connection.', [
            'model' => $modelName,
            'table' => $table,
            'file' => $relative,
            'fillable' => $fillable,
        ]);
        continue;
    }

    $columns = Schema::getColumnListing($table);
    $fillableMissingColumns = array_values(array_diff($fillable, $columns));

    if (count($fillableMissingColumns) > 0) {
        $findings[] = result_item('models', 'FAIL', 'Model fillable contains fields that do not exist as database columns.', [
            'model' => $modelName,
            'table' => $table,
            'file' => $relative,
            'fillable_missing_columns' => $fillableMissingColumns,
            'fillable' => $fillable,
            'columns' => $columns,
        ]);
    } else {
        $findings[] = result_item('models', 'PASS', 'Model fillable fields exist in database table.', [
            'model' => $modelName,
            'table' => $table,
            'file' => $relative,
            'fillable' => $fillable,
            'columns' => $columns,
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| 4. Known critical feature contract checks
|--------------------------------------------------------------------------
*/

$criticalContracts = [
    [
        'name' => 'Tenant payout account fields',
        'model' => 'TenantPayoutAccount',
        'table' => 'tenant_payout_accounts',
        'required_fields' => [
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
    ],
    [
        'name' => 'Tenant subscription payment fields',
        'model' => 'TenantSubscriptionPayment',
        'table' => 'tenant_subscription_payments',
        'required_fields' => [
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
    ],
    [
        'name' => 'Payment settings fields',
        'model' => 'PaymentSetting',
        'table' => 'payment_settings',
        'required_fields' => [
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
    ],
];

foreach ($criticalContracts as $contract) {
    $modelFile = base_path('app/Models/' . $contract['model'] . '.php');

    if (! file_exists($modelFile)) {
        $findings[] = result_item('critical_contracts', 'FAIL', 'Critical model file missing.', $contract);
        continue;
    }

    $modelText = file_get_contents($modelFile);
    $fillable = extract_model_fillable($modelText);

    $columns = Schema::hasTable($contract['table'])
        ? Schema::getColumnListing($contract['table'])
        : [];

    $missingFromFillable = array_values(array_diff($contract['required_fields'], $fillable));
    $missingFromColumns = array_values(array_diff($contract['required_fields'], $columns));

    if (count($missingFromFillable) || count($missingFromColumns)) {
        $findings[] = result_item('critical_contracts', 'FAIL', 'Critical feature contract missing required fields.', [
            ...$contract,
            'missing_from_fillable' => $missingFromFillable,
            'missing_from_columns' => $missingFromColumns,
            'fillable' => $fillable,
            'columns' => $columns,
        ]);
    } else {
        $findings[] = result_item('critical_contracts', 'PASS', 'Critical feature contract fields are coordinated.', [
            ...$contract,
            'fillable' => $fillable,
            'columns' => $columns,
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Final output
|--------------------------------------------------------------------------
*/

$summary = [
    'PASS' => 0,
    'FAIL' => 0,
    'WARN' => 0,
    'INFO' => 0,
];

foreach ($findings as $finding) {
    $summary[$finding['status']] = ($summary[$finding['status']] ?? 0) + 1;
}

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'vue_forms_detected' => count($vueForms),
    'controllers_with_validation_or_writes' => count($controllerAudits),
    'failures' => array_values(array_filter($findings, fn ($item) => $item['status'] === 'FAIL')),
    'warnings' => array_values(array_filter($findings, fn ($item) => $item['status'] === 'WARN')),
    'all_findings' => $findings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
