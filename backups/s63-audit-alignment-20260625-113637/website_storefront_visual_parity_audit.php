<?php

$checks = [];
$failures = [];

function s59_check(string $section, string $name, bool $condition, string $message, array $data = []): void
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

$homepage = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/Homepage.vue');
$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

s59_check('website_storefront_visual_parity', 'stage marker exists on storefront', str_contains($homepage, 'S59 storefront visual parity'), 'S59 storefront marker found.');
s59_check('website_storefront_visual_parity', 'stage marker exists on editor', str_contains($editor, 'S59 storefront visual parity'), 'S59 editor marker found.');
s59_check('website_storefront_visual_parity', 'storefront visual parity stylesheet exists', str_contains($homepage, '/* S59 storefront visual parity */'), 'visual parity stylesheet found.');
s59_check('website_storefront_visual_parity', 'builder homepage style hook exists', str_contains($homepage, '[data-storefront-builder-homepage]'), 'builder homepage style hook found.');
s59_check('website_storefront_visual_parity', 'hero visual parity styles exist', str_contains($homepage, '[data-section-type="hero"]') && str_contains($homepage, 'box-shadow: 0 24px 60px'), 'hero visual parity styles found.');
s59_check('website_storefront_visual_parity', 'product card visual parity styles exist', str_contains($homepage, '[data-storefront-product-card-link]') && str_contains($homepage, 'a[href*="/products/"]'), 'product card styles found.');
s59_check('website_storefront_visual_parity', 'rich text visual parity styles exist', str_contains($homepage, '[data-section-type="rich_text"]'), 'rich text styles found.');
s59_check('website_storefront_visual_parity', 'visible parity card exists', str_contains($homepage, 'data-storefront-visual-parity-card') && str_contains($homepage, 'Storefront visual parity active'), 'visible parity card found.');
s59_check('website_storefront_visual_parity', 'homepage still renders template', str_contains($homepage, '<template>') && str_contains($homepage, '</template>'), 'Homepage Vue template found.');

echo json_encode([
    'summary' => [
        'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
        'FAIL' => count($failures),
        'WARN' => 0,
        'INFO' => 0,
    ],
    'generated_at' => date('Y-m-d H:i:s'),
    'failures' => $failures,
    'warnings' => [],
    'all_findings' => $checks,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
