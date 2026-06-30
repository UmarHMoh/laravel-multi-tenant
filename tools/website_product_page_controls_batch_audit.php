<?php

$findings = [];

function addFinding(string $name, bool $passed, string $message, array $data = []): void
{
    global $findings;

    $findings[] = [
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $message,
        'data' => $data,
    ];
}

function fileContains(string $path, string $needle): bool
{
    return file_exists($path) && str_contains(file_get_contents($path), $needle);
}

$registry = 'app/Services/Themes/SectionRegistry.php';
$bootstrapper = 'app/Services/Themes/ThemeBootstrapper.php';
$productDetail = 'resources/js/pages/tenant/ProductDetail.vue';
$editor = 'resources/js/pages/tenant/website/Editor.vue';

foreach ([
    'image_style',
    'sticky_info',
    'show_quantity_selector',
    'add_to_cart_label',
    'buy_now_label',
    'button_layout',
    'button_style',
    'show_full_description',
    'rating_placeholder_text',
    'comment_heading',
    'comment_placeholder_text',
] as $needle) {
    addFinding("SectionRegistry contains {$needle}", fileContains($registry, $needle), "{$needle} should exist in SectionRegistry.");
    addFinding("ThemeBootstrapper contains {$needle}", fileContains($bootstrapper, $needle), "{$needle} should exist in ThemeBootstrapper defaults.");
}

foreach ([
    'data-s83-s88-live-product-page-controls',
    'productDetailsLayoutClass',
    'productButtonWrapClass',
    'productDescriptionSectionClass',
    'data-product-button-layout',
    'data-product-description-layout',
    'data-product-quantity-selector',
    'add_to_cart_label',
    'buy_now_label',
    'rating_placeholder_text',
    'comment_placeholder_text',
] as $needle) {
    addFinding("ProductDetail contains {$needle}", fileContains($productDetail, $needle), "{$needle} should exist in ProductDetail.");
}

foreach ([
    'data-s83-s88-product-page-editor-batch',
    's83s88ProductDetailsPreviewClass',
    's83s88ProductButtonWrapClass',
    's83s88DescriptionPreviewClass',
    'data-editor-preview-product-details-layout',
    'data-editor-preview-product-button-layout',
    'data-editor-preview-product-description-layout',
    'add_to_cart_label',
    'buy_now_label',
    'rating_placeholder_text',
    'comment_placeholder_text',
] as $needle) {
    addFinding("Editor contains {$needle}", fileContains($editor, $needle), "{$needle} should exist in Editor.");
}

$failures = array_values(array_filter(
    $findings,
    fn ($finding) => ($finding['status'] ?? null) === 'FAIL'
));

$summary = [
    'PASS' => count(array_filter($findings, fn ($finding) => ($finding['status'] ?? null) === 'PASS')),
    'FAIL' => count($failures),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => date('Y-m-d H:i:s'),
    'failures' => $failures,
    'warnings' => [],
    'all_findings' => $findings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
