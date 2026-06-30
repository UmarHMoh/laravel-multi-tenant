<?php

$checks = [];
$failures = [];

function s69_check(string $name, bool $passed, string $message = ''): void
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
$bootstrap = file_get_contents(__DIR__ . '/../app/Services/Themes/ThemeBootstrapper.php');
$renderer = file_get_contents(__DIR__ . '/../app/Services/Themes/ThemePageRenderer.php');
$controller = file_get_contents(__DIR__ . '/../app/Http/Controllers/Tenant/ProductController.php');
$detail = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/ProductDetail.vue');
$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

s69_check('product page section schemas exist', str_contains($registry, "'product_details'") && str_contains($registry, "'product_description'") && str_contains($registry, "'product_reviews'"), 'Product page section schemas missing.');
s69_check('product details controls exist', str_contains($registry, 'show_add_to_cart') && str_contains($registry, 'show_buy_now') && str_contains($registry, 'show_description'), 'Product details controls missing.');
s69_check('bootstrapper creates product theme pages', str_contains($bootstrap, 'ensureProductPage') && str_contains($bootstrap, "'type' => 'product'") && str_contains($bootstrap, "'product_id'"), 'ensureProductPage missing.');
s69_check('renderer supports product pages', str_contains($renderer, 'renderProductPage') && str_contains($renderer, "'page_type' => 'product'"), 'renderProductPage missing.');
s69_check('product controller passes product theme page payload', str_contains($controller, 'productThemePage') && str_contains($controller, 'productThemeSections') && str_contains($controller, 'ensureProductPage'), 'Product controller theme payload missing.');
s69_check('product detail page has product section foundation', str_contains($detail, 'data-s69-product-page-theme') && str_contains($detail, 'productThemeSectionsList'), 'ProductDetail product page foundation missing.');
s69_check('product detail page keeps add to cart and buy now language', str_contains($detail, 'Add to cart') && str_contains($detail, 'Buy now'), 'Product purchase controls language missing.');
s69_check('editor product page foundation exists', str_contains($editor, 'data-s69-product-page-editor') && str_contains($editor, 's69CreateProductDetailsSection'), 'Editor product page foundation missing.');

$summary = [
    'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
    'FAIL' => count(array_filter($checks, fn ($check) => $check['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'script' => 'website_product_pages_editable_audit',
    'summary' => $summary,
    'checks' => $checks,
    'failures' => $failures,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
