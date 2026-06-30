<?php

$checks = [];
$failures = [];

function s68_check(string $name, bool $passed, string $message = ''): void
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

s68_check('product grid schema exists', str_contains($registry, "'product_grid' =>"), 'product_grid schema missing.');
s68_check('product grid source controls exist', str_contains($registry, "'source'") && str_contains($registry, 'category_select'), 'Product source/category controls missing.');
s68_check('product grid responsive columns exist', str_contains($registry, 'columns_desktop') && str_contains($registry, 'columns_tablet') && str_contains($registry, 'columns_mobile'), 'Responsive column settings missing.');
s68_check('product grid filter controls exist', str_contains($registry, 'show_filters') && str_contains($registry, 'show_search') && str_contains($registry, 'show_sort'), 'Filter/search/sort settings missing.');
s68_check('storefront product grid v2 renderer exists', str_contains($home, 'data-product-grid-v2') && str_contains($home, 'productGridProducts'), 'Product Grid v2 renderer missing.');
s68_check('storefront product grid controls exist', str_contains($home, 'data-product-grid-controls') && str_contains($home, 'data-product-grid-search') && str_contains($home, 'data-product-grid-sort'), 'Product Grid controls missing.');
s68_check('storefront product grid cards link to products', str_contains($home, 'data-product-grid-card') && str_contains($home, 'productCardUrl'), 'Product Grid card links missing.');
s68_check('storefront product grid uses real product data', str_contains($home, 'productCardImage') && str_contains($home, 'productCardPrice') && str_contains($home, 'tenantProductList'), 'Product Grid real product helpers missing.');
s68_check('editor product grid v2 foundation exists', str_contains($editor, 'data-s68-product-grid-editor') && str_contains($editor, 's68CreateProductGridSection'), 'Editor Product Grid v2 foundation missing.');

$summary = [
    'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
    'FAIL' => count(array_filter($checks, fn ($check) => $check['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'script' => 'website_product_grid_v2_audit',
    'summary' => $summary,
    'checks' => $checks,
    'failures' => $failures,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
