<?php

$findings = [];

function addFinding(string $name, bool $passed, string $message, array $data = []): void
{
    global $findings;

    $findings[] = [
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $message,
        'data' => $data,
    ];
}

function fileContains(string $path, string $needle): bool
{
    return file_exists($path) && str_contains(file_get_contents($path), $needle);
}

$asset = 'app/Http/Controllers/Tenant/Manage/TenantAssetController.php';
$builder = 'app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php';
$web = 'routes/web.php';

foreach ([
    'safeTenantAssetPath',
    'str_contains($path, \'..\')',
    'str_starts_with($path, $prefix)',
    'preg_match(\'/\\.(jpg|jpeg|png|webp|gif)$/i\'',
    '\'X-Content-Type-Options\' => \'nosniff\'',
    'str_starts_with((string) $mimeType, \'image/\')',
] as $needle) {
    addFinding("TenantAssetController contains {$needle}", fileContains($asset, $needle), "{$needle} should exist in TenantAssetController.");
}

foreach ([
    'tenant.website.media.upload',
    '\'web\'',
    'PreventAccessFromCentralDomains::class',
    'InitializeTenancyByDomain::class',
    '\'auth\'',
] as $needle) {
    addFinding("web routes contain {$needle}", fileContains($web, $needle), "{$needle} should exist in web routes.");
}

foreach ([
    'MAX_BUILDER_SECTIONS',
    'MAX_BUILDER_BLOCKS',
    'MAX_SETTING_STRING_LENGTH',
    'MAX_SETTING_ARRAY_ITEMS',
    'MAX_SETTING_DEPTH',
    'sanitizeBuilderSettingValue',
    'ensureRequiredProductPageSections',
    'isSafeWebsiteMediaPath',
    'storeAs(\'tenant-website\'',
    'Str::uuid()',
    'str_starts_with($path, \'tenant-website/\')',
    'preg_match(\'/\\.(jpg|jpeg|png|webp|gif)$/i\'',
] as $needle) {
    addFinding("WebsiteBuilderController contains {$needle}", fileContains($builder, $needle), "{$needle} should exist in WebsiteBuilderController.");
}

$failures = array_values(array_filter(
    $findings,
    fn ($finding) => ($finding['status'] ?? null) === 'FAIL'
));

$summary = [
    'PASS' => count(array_filter($findings, fn ($finding) => ($finding['status'] ?? null) === 'PASS')),
    'FAIL' => count($failures),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => date('Y-m-d H:i:s'),
    'failures' => $failures,
    'warnings' => [],
    'all_findings' => $findings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
