<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\Manage\WebsiteBuilderController;
use App\Models\Tenant;
use App\Services\Themes\ThemeBootstrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

$results = [];

function add_settings_result(string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

$vue = File::exists(base_path('resources/js/pages/tenant/website/Editor.vue'))
    ? File::get(base_path('resources/js/pages/tenant/website/Editor.vue'))
    : '';

foreach ([
    'Section settings',
    'selectedSection',
    'selectedSchema',
    'updateSetting',
    'settingValue',
    "setting.type === 'textarea'",
    "setting.type === 'number'",
    "setting.type === 'select'",
    "setting.type === 'checkbox'",
    "isLinkSetting(setting)",
    'data-setting-id',
    'Heading: {{ section.settings.heading }}',
    'min-h-11',
    'editorGridClass',
] as $needle) {
    add_settings_result(
        "builder Vue contains {$needle}",
        str_contains($vue, $needle) ? 'PASS' : 'FAIL',
        str_contains($vue, $needle) ? "{$needle} found." : "{$needle} missing."
    );
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
                    'id' => 'audit_s39_hero',
                    'type' => 'hero',
                    'hidden' => false,
                    'settings' => [
                        'eyebrow' => 'Audit Eyebrow',
                        'heading' => 'S39 Edited Audit Heading',
                        'subheading' => 'Audit subheading',
                        'button_label' => 'Audit CTA',
                        'button_link' => '/audit',
                        'alignment' => 'left',
                    ],
                    'blocks' => [],
                ],
                [
                    'id' => 'audit_s39_product_grid',
                    'type' => 'product_grid',
                    'hidden' => false,
                    'settings' => [
                        'heading' => 'Audit Product Grid',
                        'show_filters' => false,
                        'per_page' => 6,
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

        $heroOk = ($sections[0]['settings']['heading'] ?? null) === 'S39 Edited Audit Heading'
            && ($sections[0]['settings']['alignment'] ?? null) === 'left';

        $productGridOk = ($sections[1]['settings']['show_filters'] ?? null) === false
            && (int) ($sections[1]['settings']['per_page'] ?? 0) === 6;

        add_settings_result(
            "tenant {$tenant->id} edited settings persist",
            ($heroOk && $productGridOk) ? 'PASS' : 'FAIL',
            ($heroOk && $productGridOk) ? 'Edited section settings saved correctly.' : 'Edited section settings did not save correctly.',
            ['sections' => $sections]
        );

        $homepage->update([
            'draft_config' => $originalDraftConfig,
        ]);
    } catch (Throwable $e) {
        add_settings_result(
            "tenant {$tenant->id} settings update exception",
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
