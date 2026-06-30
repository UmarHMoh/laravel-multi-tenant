<?php

$root = dirname(__DIR__);
$specPath = $root . '/tests/browser-audits/website-product-page-live-parity.spec.js';
$spec = file_exists($specPath) ? file_get_contents($specPath) : '';
$productDetail = file_get_contents($root . '/resources/js/pages/tenant/ProductDetail.vue');

$checks = [
    'Product page live parity browser spec exists' => file_exists($specPath),
    'Spec resets product page draft before publish' => str_contains($spec, "name: /^reset$/i")
        && str_contains($spec, "name: /^publish$/i"),
    'Spec verifies live product page theme wrapper' => str_contains($spec, 'data-product-page-sections')
        || str_contains($spec, 'data-s69-product-page-theme'),
    'Spec verifies details description reviews and related text' => str_contains($spec, 'Add to Cart')
        && str_contains($spec, 'Product Reviews')
        && str_contains($spec, 'You may also like'),
    'Live product detail supports product details section' => str_contains($productDetail, "section.type === 'product_details'")
        && str_contains($productDetail, 'data-product-details-section'),
    'Live product detail supports product description section' => str_contains($productDetail, "section.type === 'product_description'")
        && str_contains($productDetail, 'data-product-description-section'),
    'Live product detail supports product reviews section' => str_contains($productDetail, "section.type === 'product_reviews'")
        && str_contains($productDetail, 'data-product-reviews-section'),
    'Live product detail supports product featured products section' => str_contains($productDetail, "section.type === 'featured_products'")
        && str_contains($productDetail, 'data-product-featured-products-section'),
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
    'script' => 'website_product_page_live_parity_audit',
    'summary' => [
        'PASS' => $pass,
        'FAIL' => count($failures),
    ],
    'failures' => $failures,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
