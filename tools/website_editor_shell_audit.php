<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

$results = [];

function add_editor_shell_result(string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

foreach ([
    'tenant.website.homepage.editor',
    'tenant.website.pages.editor',
] as $route) {
    add_editor_shell_result(
        "{$route} route exists",
        Route::has($route) ? 'PASS' : 'FAIL',
        "{$route} should exist."
    );
}

$file = 'resources/js/pages/tenant/website/Editor.vue';
$content = File::exists(base_path($file)) ? File::get(base_path($file)) : '';

foreach ([
    'Website Editor',
    'Left sidebar',
    'Live preview',
    'Right sidebar',
    'viewportMode',
    'Desktop',
    'Tablet',
    'Mobile',
    'leftSidebarOpen',
    'rightSidebarOpen',
    'Hide left',
    'Hide right',
    'editorGridClass',
    'previewWidthClass',
    'featuredPreviewGridClass',
    'productPreviewGridClass',
    'richTextFeatureGridClass',
    'previewPaddingClass',
    'S43 foundation',
] as $needle) {
    add_editor_shell_result(
        "{$file} contains {$needle}",
        str_contains($content, $needle) ? 'PASS' : 'FAIL',
        str_contains($content, $needle) ? "{$needle} found." : "{$needle} missing."
    );
}

$summary = [
    'PASS' => count(array_filter($results, fn ($result) => $result['status'] === 'PASS')),
    'FAIL' => count(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'failures' => array_values(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'warnings' => [],
    'all_findings' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
