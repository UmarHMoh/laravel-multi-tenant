<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

$results = [];

function add_image_picker_result(string $name, string $status, string $message): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => [],
    ];
}

add_image_picker_result(
    'homepage editor route exists',
    Route::has('tenant.website.homepage.editor') ? 'PASS' : 'FAIL',
    'Homepage editor route should exist.'
);

$registry = File::get(base_path('app/Services/Themes/SectionRegistry.php'));
foreach ([
    "['type' => 'image', 'id' => 'image_url', 'label' => 'Hero image'",
    "'image_url' => ''",
] as $needle) {
    add_image_picker_result(
        "SectionRegistry contains {$needle}",
        str_contains($registry, $needle) ? 'PASS' : 'FAIL',
        str_contains($registry, $needle) ? "{$needle} found." : "{$needle} missing."
    );
}

$editor = File::get(base_path('resources/js/pages/tenant/website/Editor.vue'));
foreach ([
    'function isImageSetting',
    'function updateImageSetting',
    'function updateBlockImageSetting',
    'Hero image preview placeholder',
    'Image picker foundation',
    'Block image picker foundation',
    'Paste image URL for now',
    'Clear image',
    'S47 image picker foundation',
] as $needle) {
    add_image_picker_result(
        "Editor.vue contains {$needle}",
        str_contains($editor, $needle) ? 'PASS' : 'FAIL',
        str_contains($editor, $needle) ? "{$needle} found." : "{$needle} missing."
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
