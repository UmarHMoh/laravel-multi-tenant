<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Services\Themes\ThemeBootstrapper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

$results = [];

function add_link_picker_result(string $name, string $status, string $message, array $data = []): void
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
    add_link_picker_result(
        "{$route} route exists",
        Route::has($route) ? 'PASS' : 'FAIL',
        "{$route} should exist."
    );
}

$controller = File::get(base_path('app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php'));
foreach ([
    'private function linkOptionsForTheme',
    "'linkOptions' => \$this->linkOptionsForTheme(\$theme),",
    "'value' => \$page->type === 'home' ? '/' : \"/pages/{\$page->handle}\"",
] as $needle) {
    add_link_picker_result(
        "controller contains {$needle}",
        str_contains($controller, $needle) ? 'PASS' : 'FAIL',
        str_contains($controller, $needle) ? "{$needle} found." : "{$needle} missing."
    );
}

$editor = File::get(base_path('resources/js/pages/tenant/website/Editor.vue'));
foreach ([
    'linkOptions',
    'function isLinkSetting',
    'function normalisedLinkOptions',
    'function updateLinkSetting',
    'function updateBlockLinkSetting',
    'Custom URL...',
    'Pick an internal page or paste a custom URL.',
    'S46 link picker foundation',
] as $needle) {
    add_link_picker_result(
        "Editor.vue contains {$needle}",
        str_contains($editor, $needle) ? 'PASS' : 'FAIL',
        str_contains($editor, $needle) ? "{$needle} found." : "{$needle} missing."
    );
}

Tenant::query()->orderBy('id')->each(function (Tenant $tenant) {
    tenancy()->initialize($tenant);

    try {
        $theme = app(ThemeBootstrapper::class)->ensureDefaultTheme();
        $pages = $theme->pages()->count();

        add_link_picker_result(
            "tenant {$tenant->id} has pages for link picker",
            $pages >= 1 ? 'PASS' : 'FAIL',
            "Tenant should have at least homepage.",
            ['pages' => $pages]
        );
    } finally {
        tenancy()->end();
    }
});

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
