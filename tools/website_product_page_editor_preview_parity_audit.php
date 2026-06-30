<?php

$root = dirname(__DIR__);
$editor = file_get_contents($root . '/resources/js/pages/tenant/website/Editor.vue');
$controller = file_get_contents($root . '/app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php');

$checks = [
    'Editor receives product payload for product pages' => str_contains($controller, "'product' => $product")
        && str_contains($editor, 'product: Object'),
    'Editor has current product preview helper' => str_contains($editor, 'editorCurrentProduct')
        && str_contains($editor, 's73ProductImageUrl')
        && str_contains($editor, 's73ProductPrice'),
    'Product details section preview renders real product surface' => str_contains($editor, "section.type === 'product_details'")
        && str_contains($editor, 'data-editor-preview-product-details')
        && str_contains($editor, 'data-editor-preview-add-to-cart')
        && str_contains($editor, 'data-editor-preview-buy-now'),
    'Product description section preview renders product description' => str_contains($editor, "section.type === 'product_description'")
        && str_contains($editor, 'data-editor-preview-product-description')
        && str_contains($editor, 'editorCurrentProduct.description'),
    'Product reviews section preview renders placeholder controls' => str_contains($editor, "section.type === 'product_reviews'")
        && str_contains($editor, 'data-editor-preview-product-reviews')
        && str_contains($editor, 'show_rating_summary')
        && str_contains($editor, 'show_comment_box'),
    'Related products preview exists for product page featured products' => str_contains($editor, 'data-editor-preview-product-related')
        && str_contains($editor, 's73RelatedProducts'),
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
    'script' => 'website_product_page_editor_preview_parity_audit',
    'summary' => [
        'PASS' => $pass,
        'FAIL' => count($failures),
    ],
    'failures' => $failures,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
