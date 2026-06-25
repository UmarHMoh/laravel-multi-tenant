<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\Manage\WebsiteBuilderController;
use App\Models\Tenant;
use App\Services\Themes\ThemeBootstrapper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

$results = [];

function add_pages_dashboard_result(string $name, string $status, string $message, array $data = []): void
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
    'tenant.website.index',
    'tenant.website.pages.store',
    'tenant.website.homepage.editor',
    'tenant.website.pages.editor',
    'tenant.website.pages.update',
    'tenant.website.pages.publish',
] as $route) {
    add_pages_dashboard_result(
        "{$route} route exists",
        Route::has($route) ? 'PASS' : 'FAIL',
        "{$route} should exist."
    );
}

$files = [
    'app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php' => [
        'storePage',
        'homepageEditor',
        'pageEditor',
        'updatePage',
        'publishPage',
        'pageStats',
        'can_create_page',
        'editor_url',
    ],
    'resources/js/pages/tenant/website/Index.vue' => [
        'Website pages',
        'Create page',
        'Open Editor',
        'Open homepage editor',
        'Draft + published workflow',
        'canCreatePage',
        '/manage/website/pages',
        'S42 foundation',
    ],
    'resources/js/pages/tenant/website/Editor.vue' => [
        'Website Editor',
        'Back to pages',
        'saveUrl',
        'publishUrl',
        'page: { type: Object',
    ],
];

foreach ($files as $file => $needles) {
    $content = File::exists(base_path($file)) ? File::get(base_path($file)) : '';

    foreach ($needles as $needle) {
        add_pages_dashboard_result(
            "{$file} contains {$needle}",
            str_contains($content, $needle) ? 'PASS' : 'FAIL',
            str_contains($content, $needle) ? "{$needle} found in {$file}." : "{$needle} missing from {$file}."
        );
    }
}

foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);

    try {
        $theme = app(ThemeBootstrapper::class)->ensureDefaultTheme();
        $homepage = $theme->homepage()->first();

        $page = $theme->pages()->firstOrCreate(
            ['handle' => 's42-audit-page'],
            [
                'title' => 'S42 Audit Page',
                'type' => 'static',
                'template' => 'default',
                'draft_config' => ['sections' => []],
                'published_config' => null,
                'published_at' => null,
            ]
        );

        $response = app()->call([app(WebsiteBuilderController::class), 'index']);

        $pageExists = $theme->pages()->where('handle', 's42-audit-page')->exists();
        $homepageExists = (bool) $homepage;

        add_pages_dashboard_result(
            "tenant {$tenant->id} dashboard has homepage and custom page",
            ($pageExists && $homepageExists) ? 'PASS' : 'FAIL',
            ($pageExists && $homepageExists) ? 'Theme pages exist for dashboard.' : 'Theme pages missing.',
            [
                'page_count' => $theme->pages()->count(),
                'homepage_id' => $homepage?->id,
                'audit_page_id' => $page->id,
                'response_class' => get_class($response),
            ]
        );

        $page->delete();
    } catch (Throwable $e) {
        add_pages_dashboard_result(
            "tenant {$tenant->id} dashboard exception",
            'FAIL',
            $e->getMessage(),
            [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]
        );
    } finally {
        tenancy()->end();
    }
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
