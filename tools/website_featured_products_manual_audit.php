<?php

$checks = [];
$failures = [];

function s67_check(string $name, bool $passed, string $message = ''): void
{
    global $checks, $failures;

    $checks[] = [
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $message,
    ];

    if (! $passed) {
        $failures[] = [
            'name' => $name,
            'message' => $message,
        ];
    }
}

$registry = file_get_contents(__DIR__ . '/../app/Services/Themes/SectionRegistry.php');
$home = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/Homepage.vue');
$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

s67_check('featured products schema exists', str_contains($registry, "'featured_products' =>"), 'featured_products schema missing.');
s67_check('product picker schema exists', str_contains($registry, 'product_picker') && str_contains($registry, 'product_select'), 'Product picker/select settings missing.');
s67_check('manual cards schema exists', str_contains($registry, "'cards'") && str_contains($registry, 'product_card'), 'Manual product cards schema missing.');
s67_check('storefront manual renderer exists', str_contains($home, 'data-featured-products-manual') && str_contains($home, 'featuredProductsForSection'), 'Manual featured products renderer missing.');
s67_check('storefront card links to product detail', str_contains($home, 'productCardUrl') && str_contains($home, 'data-featured-product-card'), 'Featured product card links missing.');
s67_check('storefront uses real product data', str_contains($home, 'tenantProductList') && str_contains($home, 'productCardPrice') && str_contains($home, 'productCardImage'), 'Real product data helpers missing.');
s67_check('editor product picker foundation exists', str_contains($editor, 'data-featured-product-picker') && str_contains($editor, 's67ProductOptions'), 'Editor product picker foundation missing.');
s67_check('editor featured products section factory exists', str_contains($editor, 's67CreateFeaturedProductsSection'), 'Editor featured product section factory missing.');

$summary = [
    'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
    'FAIL' => count(array_filter($checks, fn ($check) => $check['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'script' => 'website_featured_products_manual_audit',
    'summary' => $summary,
    'checks' => $checks,
    'failures' => $failures,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
