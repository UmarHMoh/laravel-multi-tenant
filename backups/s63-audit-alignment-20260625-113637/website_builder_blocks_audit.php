<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Tenant\Manage\WebsiteBuilderController;
use App\Models\Tenant;
use App\Services\Themes\SectionRegistry;
use App\Services\Themes\ThemeBootstrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

$results = [];

function add_blocks_result(string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

$registry = app(SectionRegistry::class);

foreach ([
    ['hero', 'button'],
    ['rich_text', 'feature_card'],
    ['product_grid', 'info_note'],
] as [$sectionType, $blockType]) {
    add_blocks_result(
        "{$sectionType} supports {$blockType} block",
        $registry->block($sectionType, $blockType) ? 'PASS' : 'FAIL',
        "{$sectionType} should register {$blockType} block."
    );
}

$files = [
    'app/Services/Themes/SectionRegistry.php' => [
        'feature_card',
        'info_note',
        'max_blocks',
        'public function block',
    ],
    'app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php' => [
        'normalizeBlocks',
        'sections.*.blocks.*.type',
        'take($maxBlocks)',
        'settingsWithDefaults($blockSchema',
    ],
    'resources/js/pages/tenant/website/Editor.vue' => [
        'Blocks inside section',
        'Block settings',
        'addBlock',
        'selectedBlock',
        'selectedBlockSchema',
        'updateBlockSetting',
        'data-block-setting-id',
        'Move block up',
        'Move block down',
        'Hide block',
        'Remove block',
    ],
    'resources/js/pages/tenant/Homepage.vue' => [
        'visibleBlocks',
        "item.type === 'button'",
        "item.type === 'feature_card'",
        "item.type === 'info_note'",
    ],
];

foreach ($files as $file => $needles) {
    $content = File::exists(base_path($file)) ? File::get(base_path($file)) : '';

    foreach ($needles as $needle) {
        add_blocks_result(
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
                    'id' => 'audit_s40_rich_text',
                    'type' => 'rich_text',
                    'hidden' => false,
                    'settings' => [
                        'heading' => 'S40 Audit Rich Text',
                    ],
                    'blocks' => [
                        [
                            'id' => 'audit_s40_feature_card',
                            'type' => 'feature_card',
                            'hidden' => false,
                            'settings' => [
                                'heading' => 'S40 Feature Card',
                                'text' => 'S40 block text saved.',
                            ],
                        ],
                    ],
                ],
                [
                    'id' => 'audit_s40_product_grid',
                    'type' => 'product_grid',
                    'hidden' => false,
                    'settings' => [
                        'heading' => 'S40 Product Grid',
                    ],
                    'blocks' => [
                        [
                            'id' => 'audit_s40_info_note',
                            'type' => 'info_note',
                            'hidden' => false,
                            'settings' => [
                                'heading' => 'S40 Info Note',
                                'text' => 'S40 info note saved.',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        app()->instance('request', $request);

        app()->call([app(WebsiteBuilderController::class), 'updateHomepage'], [
            'request' => $request,
        ]);

        $homepage->refresh();
        $sections = $homepage->draft_config['sections'] ?? [];

        $featureCardOk = ($sections[0]['blocks'][0]['type'] ?? null) === 'feature_card'
            && ($sections[0]['blocks'][0]['settings']['heading'] ?? null) === 'S40 Feature Card';

        $infoNoteOk = ($sections[1]['blocks'][0]['type'] ?? null) === 'info_note'
            && ($sections[1]['blocks'][0]['settings']['heading'] ?? null) === 'S40 Info Note';

        add_blocks_result(
            "tenant {$tenant->id} block save works",
            ($featureCardOk && $infoNoteOk) ? 'PASS' : 'FAIL',
            ($featureCardOk && $infoNoteOk) ? 'Nested blocks saved correctly.' : 'Nested blocks did not save correctly.',
            ['sections' => $sections]
        );

        $homepage->update([
            'draft_config' => $originalDraftConfig,
        ]);
    } catch (Throwable $e) {
        add_blocks_result(
            "tenant {$tenant->id} block save exception",
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
