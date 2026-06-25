<?php

$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');
$controller = file_get_contents(__DIR__ . '/../app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php');

$checks = [
    'controller imports product model' => [$controller, 'use App\\Models\\Product;'],
    'controller preview products helper exists' => [$controller, 'private function websiteEditorPreviewProducts'],
    'controller passes preview products prop' => [$controller, "'previewProducts' => \$this->websiteEditorPreviewProducts(),"],
    'editor preview products prop exists' => [$editor, 'previewProducts:'],
    'editor preview products computed exists' => [$editor, 'const editorPreviewProducts = computed'],
    'product card url helper exists' => [$editor, 'function productCardUrl'],
    'product price formatter exists' => [$editor, 'function formatPreviewPrice'],
    'product card uses product URL' => [$editor, 'productCardUrl(product)'],
    'product card formats real product price' => [$editor, 'formatPreviewPrice(product)'],
    'product card image uses image url' => [$editor, 'product.image_url'],
    'blank space reduced' => [$editor, 'pb-16'],
    'stage marker exists' => [$editor, 'S56 rendering parity product cards foundation'],
];

$results = [];

foreach ($checks as $name => [$haystack, $needle]) {
    $passed = str_contains($haystack, $needle);

    $results[] = [
        'section' => 'website_editor_rendering_parity',
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $passed ? "{$name} found." : "{$name} missing.",
        'data' => [],
    ];
}

$summary = [
    'PASS' => count(array_filter($results, fn ($result) => $result['status'] === 'PASS')),
    'FAIL' => count(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => date('Y-m-d H:i:s'),
    'failures' => array_values(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'warnings' => [],
    'all_findings' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
