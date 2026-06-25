<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

$results = [];

function add_selection_result(string $name, string $status, string $message): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => [],
    ];
}

add_selection_result(
    'homepage editor route exists',
    Route::has('tenant.website.homepage.editor') ? 'PASS' : 'FAIL',
    'Homepage editor route should exist.'
);

$file = 'resources/js/pages/tenant/website/Editor.vue';
$content = File::exists(base_path($file)) ? File::get(base_path($file)) : '';

foreach ([
    'selectedItemType',
    'inspectorTitle',
    'selectionBreadcrumb',
    'function selectPage',
    'function selectPreviewSection',
    'function selectPreviewBlock',
    'previewSectionClass',
    'previewBlockClass',
    'Select page',
    'Selected: {{ selectionBreadcrumb }}',
    'Page settings',
    '@click.stop="selectPreviewSection(section)"',
    '@click.stop="selectPreviewBlock(section, block)"',
    '@click.prevent.stop="selectPreviewBlock(section, block)"',
    'S44 selection model',
] as $needle) {
    add_selection_result(
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
