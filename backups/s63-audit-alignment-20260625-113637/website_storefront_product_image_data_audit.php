<?php

$checks = [];
$failures = [];

function s61_check(string $section, string $name, bool $condition, string $message, array $data = []): void
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

s61_check('website_storefront_product_image_data', 'stage marker exists on storefront', str_contains($homepage, 'S61 storefront product image data'), 'S61 storefront marker found.');
s61_check('website_storefront_product_image_data', 'stage marker exists on editor', str_contains($editor, 'S61 storefront product image data'), 'S61 editor marker found.');
s61_check('website_storefront_product_image_data', 'product image helper exists', str_contains($homepage, 'function storefrontProductImage(product)'), 'product image helper found.');
s61_check('website_storefront_product_image_data', 'product url helper exists', str_contains($homepage, 'function storefrontProductUrl(product)'), 'product url helper found.');
s61_check('website_storefront_product_image_data', 'real product image section exists', str_contains($homepage, 'data-storefront-real-product-image-section'), 'real product image section found.');
s61_check('website_storefront_product_image_data', 'real product image card exists', str_contains($homepage, 'data-storefront-real-product-image-card'), 'real product image card found.');
s61_check('website_storefront_product_image_data', 'real image binding exists', str_contains($homepage, ':src="storefrontProductImage(product)"'), 'real image binding found.');
s61_check('website_storefront_product_image_data', 'placeholder fallback exists', str_contains($homepage, 'data-storefront-product-image-placeholder') && str_contains($homepage, 'Product image'), 'placeholder fallback found.');
s61_check('website_storefront_product_image_data', 'category and price bindings exist', str_contains($homepage, 'data-product-category') && str_contains($homepage, 'data-product-price'), 'category and price bindings found.');
s61_check('website_storefront_product_image_data', 'S61 stylesheet exists', str_contains($homepage, '/* S61 storefront product image data */'), 'S61 stylesheet found.');
s61_check('website_storefront_product_image_data', 'homepage still renders template', str_contains($homepage, '<template>') && str_contains($homepage, '</template>'), 'Homepage Vue template found.');

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
