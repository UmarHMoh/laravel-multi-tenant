<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$report = [];

function add_dropdown_result(string $section, string $name, string $status, string $message, array $data = []): void
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

function relative_path(string $path): string
{
    return str_replace(base_path() . '/', '', $path);
}

function extract_backend_in_values(string $controllerText): array
{
    $rules = [];

    preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]\s*=>\s*\[(.*?)\]/s', $controllerText, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $field = $match[1];
        $ruleBlock = $match[2];

        if (preg_match('/[\'"]in:([^\'"]+)[\'"]/', $ruleBlock, $inMatch)) {
            $rules[$field] = array_values(array_filter(array_map('trim', explode(',', $inMatch[1]))));
        }
    }

    preg_match_all('/[\'"]([A-Za-z0-9_]+)[\'"]\s*=>\s*[\'"]([^\'"]*in:([^\'"]+)[^\'"]*)[\'"]/', $controllerText, $matches2, PREG_SET_ORDER);

    foreach ($matches2 as $match) {
        $field = $match[1];
        $values = array_values(array_filter(array_map('trim', explode(',', $match[3]))));
        $rules[$field] = $values;
    }

    return $rules;
}

function extract_vue_select_options(string $vueText): array
{
    $fieldOptions = [];

    preg_match_all('/<select\b[^>]*v-model=["\'](?:[A-Za-z0-9_]+\.)?([A-Za-z0-9_]+)["\'][^>]*>(.*?)<\/select>/s', $vueText, $selects, PREG_SET_ORDER);

    foreach ($selects as $select) {
        $field = $select[1];
        $body = $select[2];

        preg_match_all('/<option\b[^>]*value=["\']([^"\']+)["\']/', $body, $optionMatches);

        $values = array_values(array_unique(array_filter($optionMatches[1] ?? [], fn ($value) => $value !== '')));

        if (count($values) > 0) {
            $fieldOptions[$field] = $values;
        }
    }

    preg_match_all('/<Select[^>]*v-model=["\'](?:[A-Za-z0-9_]+\.)?([A-Za-z0-9_]+)["\'][^>]*>(.*?)<\/Select>/s', $vueText, $selects2, PREG_SET_ORDER);

    foreach ($selects2 as $select) {
        $field = $select[1];
        $body = $select[2];

        preg_match_all('/value=["\']([^"\']+)["\']/', $body, $optionMatches);

        $values = array_values(array_unique(array_filter($optionMatches[1] ?? [], fn ($value) => $value !== '')));

        if (count($values) > 0) {
            $fieldOptions[$field] = $values;
        }
    }

    return $fieldOptions;
}

$controllerRules = [];

foreach (glob(base_path('app/Http/Controllers/**/*.php'), GLOB_BRACE) ?: [] as $file) {
    if (! is_file($file)) {
        continue;
    }

    $text = file_get_contents($file);
    $rules = extract_backend_in_values($text);

    if (count($rules) > 0) {
        $controllerRules[relative_path($file)] = $rules;

        add_dropdown_result('backend_rules', relative_path($file), 'INFO', 'Backend in: validation values detected.', [
            'rules' => $rules,
        ]);
    }
}

$vueOptions = [];

foreach (glob(base_path('resources/js/**/*.vue'), GLOB_BRACE) ?: [] as $file) {
    if (! is_file($file)) {
        continue;
    }

    $text = file_get_contents($file);
    $options = extract_vue_select_options($text);

    if (count($options) > 0) {
        $vueOptions[relative_path($file)] = $options;

        add_dropdown_result('vue_options', relative_path($file), 'INFO', 'Vue select options detected.', [
            'options' => $options,
        ]);
    }
}

/*
|--------------------------------------------------------------------------
| Critical known contracts
|--------------------------------------------------------------------------
*/

$contracts = [
    [
        'name' => 'Payment settings environment',
        'controller' => 'app/Http/Controllers/Central/PaymentSettingController.php',
        'vue' => 'resources/js/pages/central/payment-settings/Edit.vue',
        'field' => 'environment',
    ],
    [
        'name' => 'Payment settings fee structure',
        'controller' => 'app/Http/Controllers/Central/PaymentSettingController.php',
        'vue' => 'resources/js/pages/central/payment-settings/Edit.vue',
        'field' => 'fee_structure',
    ],
    [
        'name' => 'Payout account type',
        'controller' => 'app/Http/Controllers/Tenant/Manage/PayoutAccountController.php',
        'vue' => 'resources/js/pages/tenant/payouts/Account.vue',
        'field' => 'type',
    ],
    [
        'name' => 'Payout bank account type',
        'controller' => 'app/Http/Controllers/Tenant/Manage/PayoutAccountController.php',
        'vue' => 'resources/js/pages/tenant/payouts/Account.vue',
        'field' => 'bank_account_type',
    ],
];

foreach ($contracts as $contract) {
    $controllerPath = base_path($contract['controller']);
    $vuePath = base_path($contract['vue']);
    $field = $contract['field'];

    if (! file_exists($controllerPath)) {
        add_dropdown_result('critical_dropdown_contracts', $contract['name'], 'FAIL', 'Controller file missing.', $contract);
        continue;
    }

    if (! file_exists($vuePath)) {
        add_dropdown_result('critical_dropdown_contracts', $contract['name'], 'FAIL', 'Vue file missing.', $contract);
        continue;
    }

    $backendRules = extract_backend_in_values(file_get_contents($controllerPath));
    $selectOptions = extract_vue_select_options(file_get_contents($vuePath));

    $backendValues = $backendRules[$field] ?? [];
    $frontendValues = $selectOptions[$field] ?? [];

    if (count($backendValues) === 0) {
        add_dropdown_result('critical_dropdown_contracts', $contract['name'], 'WARN', 'No backend in: rule found for this field.', [
            ...$contract,
            'backend_values' => $backendValues,
            'frontend_values' => $frontendValues,
        ]);
        continue;
    }

    if (count($frontendValues) === 0) {
        add_dropdown_result('critical_dropdown_contracts', $contract['name'], 'WARN', 'No static frontend select options found. It may be dynamic.', [
            ...$contract,
            'backend_values' => $backendValues,
            'frontend_values' => $frontendValues,
        ]);
        continue;
    }

    $missingInFrontend = array_values(array_diff($backendValues, $frontendValues));
    $invalidInFrontend = array_values(array_diff($frontendValues, $backendValues));

    if (count($missingInFrontend) || count($invalidInFrontend)) {
        add_dropdown_result('critical_dropdown_contracts', $contract['name'], 'FAIL', 'Frontend dropdown values do not match backend validation.', [
            ...$contract,
            'backend_values' => $backendValues,
            'frontend_values' => $frontendValues,
            'missing_in_frontend' => $missingInFrontend,
            'invalid_in_frontend' => $invalidInFrontend,
        ]);
    } else {
        add_dropdown_result('critical_dropdown_contracts', $contract['name'], 'PASS', 'Frontend dropdown values match backend validation.', [
            ...$contract,
            'backend_values' => $backendValues,
            'frontend_values' => $frontendValues,
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
    'failures' => array_values(array_filter($report, fn ($item) => $item['status'] === 'FAIL')),
    'warnings' => array_values(array_filter($report, fn ($item) => $item['status'] === 'WARN')),
    'all_findings' => $report,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
