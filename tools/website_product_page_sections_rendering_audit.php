<?php

$root = dirname(__DIR__);
$productDetail = file_get_contents($root . '/resources/js/pages/tenant/ProductDetail.vue');
$renderer = file_get_contents($root . '/app/Services/Themes/ThemePageRenderer.php');
$registry = file_get_contents($root . '/app/Services/Themes/SectionRegistry.php');

$checks = [
    'ProductDetail renders real product page sections' => str_contains($productDetail, 'v-if="productPageHasThemeSections()"')
        && str_contains($productDetail, 'data-product-page-sections')
        && str_contains($productDetail, 'v-for="(section, index) in productThemeSectionsList()"'),
    'Product details section is visible and editable by schema settings' => str_contains($productDetail, "section.type === 'product_details'")
        && str_contains($productDetail, 'data-product-details-section')
        && str_contains($productDetail, "show_add_to_cart")
        && str_contains($productDetail, "show_buy_now"),
    'Product description section renders actual product description' => str_contains($productDetail, "section.type === 'product_description'")
        && str_contains($productDetail, 'data-product-description-section')
        && str_contains($productDetail, 'product.description'),
    'Product reviews placeholder only renders as product section' => str_contains($productDetail, "section.type === 'product_reviews'")
        && str_contains($productDetail, 'data-product-reviews-section')
        && str_contains($productDetail, 'data-product-comment-box-placeholder'),
    'Legacy product page is fallback only' => str_contains($productDetail, '<div v-else>')
        && ! str_contains($productDetail, '<section' . "\n" . '    data-product-reviews-comments-placeholder'),
    'Renderer supports product page payload' => str_contains($renderer, 'renderProductPage')
        && str_contains($renderer, "'page_type' => 'product'")
        && str_contains($renderer, "'product_id' =>"),
    'Registry includes product page sections' => str_contains($registry, "'product_details'")
        && str_contains($registry, "'product_description'")
        && str_contains($registry, "'product_reviews'"),
];

$pass = 0;
$failures = [];

foreach ($checks as $name => $ok) {
    if ($ok) {
        $pass++;
        continue;
    }

    $failures[] = $name;
}

$result = [
    'script' => 'website_product_page_sections_rendering_audit',
    'summary' => [
        'PASS' => $pass,
        'FAIL' => count($failures),
    ],
    'failures' => $failures,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
