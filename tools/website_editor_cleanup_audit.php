<?php

$checks = [];
$failures = [];

function s71_check(string $name, bool $passed, string $message = ''): void
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

$blade = file_get_contents(__DIR__ . '/../resources/views/app.blade.php');
$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

s71_check('S66 audit anchors still exist for browser tests', str_contains($blade, 'data-s66-browser-audit-panel') && str_contains($blade, 'data-setting-id="heading"') && str_contains($blade, 'data-editor-image-upload-input'), 'Required browser audit anchors missing.');
s71_check('S66 audit panel remains clickable for browser tests', str_contains($blade, 'data-s66-browser-audit-panel') && str_contains($blade, 'position: fixed'), 'Audit panel must remain clickable for existing browser tests.');
s71_check('S71 cleanup marker exists', str_contains($editor, 'data-s71-editor-cleanup'), 'S71 editor cleanup marker missing.');
s71_check('editor still has product builder foundations', str_contains($editor, 'data-s67-featured-products-editor') && str_contains($editor, 'data-s68-product-grid-editor') && str_contains($editor, 'data-s69-product-page-editor') && str_contains($editor, 'data-s70-reviews-comments-editor'), 'Recent editor foundations missing.');

$summary = [
    'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
    'FAIL' => count(array_filter($checks, fn ($check) => $check['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'script' => 'website_editor_cleanup_audit',
    'summary' => $summary,
    'checks' => $checks,
    'failures' => $failures,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
