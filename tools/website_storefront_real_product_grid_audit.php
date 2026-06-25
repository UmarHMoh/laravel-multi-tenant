<?php

$checks = [];
$failures = [];

function s62_check(string $section, string $name, bool $condition, string $message, array $data = []): void
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

s62_check('website_storefront_real_product_grid', 'stage marker exists on storefront', str_contains($homepage, 'S62 storefront real product grid'), 'S62 storefront marker found.');
s62_check('website_storefront_real_product_grid', 'stage marker exists on editor', str_contains($editor, 'S62 storefront real product grid'), 'S62 editor marker found.');
s62_check('website_storefront_real_product_grid', 'real product grid section exists', str_contains($homepage, 'data-storefront-real-product-grid'), 'real product grid section found.');
s62_check('website_storefront_real_product_grid', 'real product grid cards exist', str_contains($homepage, 'data-storefront-real-product-grid-card'), 'real product grid cards found.');
s62_check('website_storefront_real_product_grid', 'product card link marker exists', str_contains($homepage, 'data-storefront-product-card-link'), 'product card link marker found.');
s62_check('website_storefront_real_product_grid', 'real product image binding exists', str_contains($homepage, ':src="storefrontProductImage(product)"'), 'real product image binding found.');
s62_check('website_storefront_real_product_grid', 'product placeholder exists', str_contains($homepage, 'data-storefront-real-product-grid-placeholder') && str_contains($homepage, 'Product image'), 'product placeholder found.');
s62_check('website_storefront_real_product_grid', 'category and price bindings exist', str_contains($homepage, 'data-product-category') && str_contains($homepage, 'data-product-price'), 'category and price bindings found.');
s62_check('website_storefront_real_product_grid', 'responsive grid classes exist', str_contains($homepage, 'lg:grid-cols-3') && str_contains($homepage, 'xl:grid-cols-4'), 'responsive grid classes found.');
s62_check('website_storefront_real_product_grid', 'shop products visible text exists', str_contains($homepage, 'Featured products') && str_contains($homepage, 'Shop products'), 'shop products text found.');
s62_check('website_storefront_real_product_grid', 'S62 stylesheet exists', str_contains($homepage, '/* S62 storefront real product grid */'), 'S62 stylesheet found.');
s62_check('website_storefront_real_product_grid', 'homepage still renders template', str_contains($homepage, '<template>') && str_contains($homepage, '</template>'), 'Homepage Vue template found.');

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
