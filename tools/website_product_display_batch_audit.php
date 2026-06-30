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
$productController = 'app/Http/Controllers/Tenant/ProductController.php';
$productDetail = 'resources/js/pages/tenant/ProductDetail.vue';
$editor = 'resources/js/pages/tenant/website/Editor.vue';

foreach ([
    'gallery_style',
    'thumbnail_position',
    'show_variant_options',
    'variant_placeholder_text',
    'related_source',
] as $needle) {
    addFinding("SectionRegistry contains {$needle}", fileContains($registry, $needle), "{$needle} should exist in SectionRegistry.");
    addFinding("ThemeBootstrapper contains {$needle}", fileContains($bootstrapper, $needle), "{$needle} should exist in ThemeBootstrapper.");
}

foreach ([
    'productRecommendations',
    "with(['category', 'images'])",
    'limit(24)',
] as $needle) {
    addFinding("Public ProductController contains {$needle}", fileContains($productController, $needle), "{$needle} should exist in public ProductController.");
}

foreach ([
    'data-s89-s94-product-display-foundation',
    'productGalleryItems',
    'productGalleryStyle',
    'productThumbnailWrapClass',
    'productOptionPlaceholderText',
    'productSectionProducts',
    'productImageForCard',
    'data-product-gallery-thumbnails',
    'data-product-gallery-dots',
    'data-product-variant-options-foundation',
    'productRecommendations',
    'related_source',
] as $needle) {
    addFinding("ProductDetail contains {$needle}", fileContains($productDetail, $needle), "{$needle} should exist in ProductDetail.");
}

foreach ([
    'data-s89-s94-product-display-foundation',
    's89s94GalleryStyle',
    's89s94VariantPlaceholder',
    's89s94RelatedProducts',
    'data-editor-preview-product-gallery-foundation',
    'data-editor-preview-product-gallery-thumbnails',
    'data-editor-preview-product-gallery-dots',
    'data-editor-preview-product-options-foundation',
    'related_source',
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
