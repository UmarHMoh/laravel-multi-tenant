<?php

$root = dirname(__DIR__);

$controller = file_get_contents($root . '/app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php');
$routes = file_get_contents($root . '/routes/web.php');
$manageProductController = file_get_contents($root . '/app/Http/Controllers/Tenant/Manage/ProductController.php');
$productIndex = file_get_contents($root . '/resources/js/pages/tenant/products/Index.vue');
$productShow = file_get_contents($root . '/resources/js/pages/tenant/products/Show.vue');
$productEdit = file_get_contents($root . '/resources/js/pages/tenant/products/Edit.vue');

$checks = [
    'Product editor route exists' => str_contains($routes, '/manage/website/products/{product}/editor')
        && str_contains($routes, "'productEditor'"),
    'Website builder productEditor ensures product theme page' => (str_contains($controller, 'function productEditor(Product $product') || str_contains($controller, 'function productEditor($tenant, Product $product'))
        && str_contains($controller, 'ensureProductPage($theme, $product)')
        && str_contains($controller, "\'page\' => $productPage"),
    'Product admin controller exposes product page editor url' => substr_count($manageProductController, 'product_page_editor_url') >= 3
        && str_contains($manageProductController, '/manage/website/products/{$product->id}/editor'),
    'Product admin UI links to product page editor' => str_contains($productIndex, 'data-product-page-editor-link')
        && str_contains($productShow, 'data-product-page-editor-link')
        && str_contains($productEdit, 'data-product-page-editor-link')
        && str_contains($productIndex . $productShow . $productEdit, 'Edit product page'),
];

$pass = 0;
$failures = [];

foreach ($checks as $name => $ok) {
    if ($ok) {
        $pass++;
    } else {
        $failures[] = $name;
    }
}

$result = [
    'script' => 'website_product_page_editor_route_audit',
    'summary' => [
        'PASS' => $pass,
        'FAIL' => count($failures),
    ],
    'failures' => $failures,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
