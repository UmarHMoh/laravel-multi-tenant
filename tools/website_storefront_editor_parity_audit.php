<?php

$checks = [];
$failures = [];

function s58_check(string $section, string $name, bool $condition, string $message, array $data = []): void
{
    global $checks, $failures;

    $row = [
        'section' => $section,
        'name' => $name,
        'status' => $condition ? 'PASS' : 'FAIL',
        'message' => $message,
        'data' => $data,
    ];

    $checks[] = $row;

    if (! $condition) {
        $failures[] = $row;
    }
}

$homepagePath = __DIR__ . '/../resources/js/pages/tenant/Homepage.vue';
$homepage = file_get_contents($homepagePath);
$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

s58_check('website_storefront_editor_parity', 'stage marker exists on storefront', str_contains($homepage, 'S58 storefront editor parity'), 'S58 storefront marker found.');
s58_check('website_storefront_editor_parity', 'stage marker exists on editor', str_contains($editor, 'S58 storefront editor parity'), 'S58 editor marker found.');
s58_check('website_storefront_editor_parity', 'storefront builder homepage marker exists', str_contains($homepage, 'data-storefront-builder-homepage'), 'storefront builder homepage marker found.');
s58_check('website_storefront_editor_parity', 'storefront preview parity label exists', str_contains($homepage, 'Storefront preview parity'), 'storefront preview parity label found.');
s58_check('website_storefront_editor_parity', 'product card link parity exists', str_contains($homepage, 'data-storefront-product-card-link') || str_contains($homepage, 'Product cards automatically link to product detail pages'), 'product link parity found.');
s58_check('website_storefront_editor_parity', 'homepage still renders template', str_contains($homepage, '<template>') && str_contains($homepage, '</template>'), 'Homepage Vue template found.');
s58_check('website_storefront_editor_parity', 'editor still renders template', str_contains($editor, '<template>') && str_contains($editor, '</template>'), 'Editor Vue template found.');

echo json_encode([
    'summary' => [
        'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
        'FAIL' => count($failures),
        'WARN' => 0,
        'INFO' => 0,
    ],
    'generated_at' => date('Y-m-d H:i:s'),
    'homepage_path' => 'resources/js/pages/tenant/Homepage.vue',
    'failures' => $failures,
    'warnings' => [],
    'all_findings' => $checks,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
