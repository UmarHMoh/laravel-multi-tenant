<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\Manage\WebsiteBuilderController;
use App\Models\Tenant;
use App\Services\Themes\ThemeBootstrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

$results = [];

function add_publish_result(string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

add_publish_result(
    'publish route exists',
    Route::has('tenant.website.homepage.publish') ? 'PASS' : 'FAIL',
    'tenant.website.homepage.publish route should exist.'
);

$files = [
    'app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php' => [
        'publishHomepage',
        'published_config',
        'published_at',
        'Homepage published.',
    ],
    'app/Http/Controllers/Tenant/HomepageController.php' => [
        'published_config ?:',
    ],
    'resources/js/pages/tenant/website/Editor.vue' => [
        'publishDraft',
        "/manage/website/homepage/publish",
        'Publish',
        'Published:',
        'S43 foundation',
    ],
];

foreach ($files as $file => $needles) {
    $content = File::exists(base_path($file)) ? File::get(base_path($file)) : '';

    foreach ($needles as $needle) {
        add_publish_result(
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

        $originalDraft = $homepage->draft_config;
        $originalPublished = $homepage->published_config;
        $originalPublishedAt = $homepage->published_at;

        $draft = [
            'sections' => [
                [
                    'id' => 'audit_s41_rich_text',
                    'type' => 'rich_text',
                    'hidden' => false,
                    'settings' => [
                        'heading' => 'S41 Draft Heading',
                        'text' => 'S41 draft body',
                        'width' => 'normal',
                    ],
                    'blocks' => [],
                ],
            ],
        ];

        $homepage->update([
            'draft_config' => $draft,
            'published_config' => ['sections' => []],
            'published_at' => null,
        ]);

        app()->call([app(WebsiteBuilderController::class), 'publishHomepage']);

        $homepage->refresh();

        $publishedOk = ($homepage->published_config['sections'][0]['settings']['heading'] ?? null) === 'S41 Draft Heading'
            && $homepage->published_at !== null;

        add_publish_result(
            "tenant {$tenant->id} publish copies draft to published",
            $publishedOk ? 'PASS' : 'FAIL',
            $publishedOk ? 'Draft copied to published_config.' : 'Draft did not copy to published_config.',
            [
                'published_config' => $homepage->published_config,
                'published_at' => optional($homepage->published_at)->toDateTimeString(),
            ]
        );

        $homepage->update([
            'draft_config' => $originalDraft,
            'published_config' => $originalPublished,
            'published_at' => $originalPublishedAt,
        ]);
    } catch (Throwable $e) {
        add_publish_result(
            "tenant {$tenant->id} publish exception",
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
