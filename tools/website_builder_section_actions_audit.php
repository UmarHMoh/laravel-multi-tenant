<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\Manage\WebsiteBuilderController;
use App\Models\Tenant;
use App\Models\Theme;
use App\Services\Themes\ThemeBootstrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

$results = [];

function add_builder_action_result(string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

add_builder_action_result(
    'website builder update route exists',
    Route::has('tenant.website.homepage.update') ? 'PASS' : 'FAIL',
    'tenant.website.homepage.update route should exist.'
);

add_builder_action_result(
    'WebsiteBuilderController has updateHomepage',
    method_exists(app(WebsiteBuilderController::class), 'updateHomepage') ? 'PASS' : 'FAIL',
    'WebsiteBuilderController should expose updateHomepage.'
);

$files = [
    'app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php' => [
        'updateHomepage',
        'settingsWithDefaults',
        'draft_config',
        'Str::uuid',
    ],
    'resources/js/pages/tenant/website/Editor.vue' => [
        'Save draft',
        'Add {{ section.name }}',
        'Hide section',
        'Show section',
        'Move up',
        'Move down',
        'Remove',
        'form.put',
        '/manage/website/homepage',
        'min-h-11',
        'sm:grid-cols-2',
        'editorGridClass',
    ],
];

foreach ($files as $file => $needles) {
    $content = File::exists(base_path($file)) ? File::get(base_path($file)) : '';

    foreach ($needles as $needle) {
        add_builder_action_result(
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
        $originalDraftConfig = $homepage->draft_config;

        $request = Request::create('/manage/website/homepage', 'PUT', [
            'sections' => [
                [
                    'id' => 'audit_s38_hero',
                    'type' => 'hero',
                    'hidden' => false,
                    'settings' => [
                        'heading' => 'S38 Audit Hero',
                    ],
                    'blocks' => [],
                ],
                [
                    'id' => 'audit_s38_rich_text',
                    'type' => 'rich_text',
                    'hidden' => true,
                    'settings' => [
                        'heading' => 'S38 Audit Rich Text',
                    ],
                    'blocks' => [],
                ],
            ],
        ]);

        app()->instance('request', $request);

        app()->call([app(WebsiteBuilderController::class), 'updateHomepage'], [
            'request' => $request,
        ]);

        $homepage->refresh();
        $sections = $homepage->draft_config['sections'] ?? [];

        $ok = count($sections) === 2
            && ($sections[0]['type'] ?? null) === 'hero'
            && ($sections[1]['type'] ?? null) === 'rich_text'
            && ($sections[1]['hidden'] ?? null) === true
            && array_key_exists('subheading', $sections[0]['settings'] ?? []);

        add_builder_action_result(
            "tenant {$tenant->id} homepage draft update works",
            $ok ? 'PASS' : 'FAIL',
            $ok ? 'Homepage draft saved valid section structure.' : 'Homepage draft did not save expected structure.',
            ['sections' => $sections]
        );

        $homepage->update([
            'draft_config' => $originalDraftConfig,
        ]);
    } catch (Throwable $e) {
        add_builder_action_result(
            "tenant {$tenant->id} homepage draft update exception",
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
