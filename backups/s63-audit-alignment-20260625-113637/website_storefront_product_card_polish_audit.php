<?php

$checks = [];
$failures = [];

function s60_check(string $section, string $name, bool $condition, string $message, array $data = []): void
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

s60_check('website_storefront_product_card_polish', 'stage marker exists on storefront', str_contains($homepage, 'S60 storefront product card polish'), 'S60 storefront marker found.');
s60_check('website_storefront_product_card_polish', 'stage marker exists on editor', str_contains($editor, 'S60 storefront product card polish'), 'S60 editor marker found.');
s60_check('website_storefront_product_card_polish', 'product polish stylesheet exists', str_contains($homepage, '/* S60 storefront product card polish */'), 'S60 stylesheet found.');
s60_check('website_storefront_product_card_polish', 'visible product polish card exists', str_contains($homepage, 'data-storefront-product-card-polish') && str_contains($homepage, 'Storefront product card polish active'), 'visible polish card found.');
s60_check('website_storefront_product_card_polish', 'product image placeholder style exists', str_contains($homepage, 'Product image') && str_contains($homepage, 'aspect-ratio: 4 / 3'), 'product image placeholder style found.');
s60_check('website_storefront_product_card_polish', 'product linked card style exists', str_contains($homepage, '[data-storefront-product-card-link]') && str_contains($homepage, 'a[href*="/products/"]'), 'linked product card style found.');
s60_check('website_storefront_product_card_polish', 'product category style exists', str_contains($homepage, '[data-product-category]') && str_contains($homepage, '.product-category'), 'product category style found.');
s60_check('website_storefront_product_card_polish', 'product price style exists', str_contains($homepage, '[data-product-price]') && str_contains($homepage, '.price'), 'product price style found.');
s60_check('website_storefront_product_card_polish', 'homepage still renders template', str_contains($homepage, '<template>') && str_contains($homepage, '</template>'), 'Homepage Vue template found.');

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
