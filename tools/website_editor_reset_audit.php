<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

$results = [];

function add_reset_result(string $name, string $status, string $message): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => [],
    ];
}

foreach ([
    'tenant.website.homepage.reset',
    'tenant.website.pages.reset',
] as $route) {
    add_reset_result(
        "{$route} route exists",
        Route::has($route) ? 'PASS' : 'FAIL',
        "{$route} should exist."
    );
}

$controller = File::get(base_path('app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php'));
foreach ([
    'public function resetHomepageDraft',
    'public function resetPageDraft',
    'private function defaultStaticPageSections',
    'Homepage draft reset to default sections.',
    'Page draft reset to default sections.',
] as $needle) {
    add_reset_result(
        "controller contains {$needle}",
        str_contains($controller, $needle) ? 'PASS' : 'FAIL',
        str_contains($controller, $needle) ? "{$needle} found." : "{$needle} missing."
    );
}

$routes = File::get(base_path('routes/web.php'));
foreach ([
    '/manage/website/homepage/reset',
    '/manage/website/pages/{themePage}/reset',
] as $needle) {
    add_reset_result(
        "routes contain {$needle}",
        str_contains($routes, $needle) ? 'PASS' : 'FAIL',
        str_contains($routes, $needle) ? "{$needle} found." : "{$needle} missing."
    );
}

$editor = File::get(base_path('resources/js/pages/tenant/website/Editor.vue'));
foreach ([
    'const resetUrl = computed',
    'router.post(resetUrl.value',
    'Reset this draft to default sections?',
    'S48 reset draft foundation',
] as $needle) {
    add_reset_result(
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
