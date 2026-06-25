<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Theme;
use App\Models\ThemePage;
use App\Services\Themes\SectionRegistry;
use App\Services\Themes\ThemeBootstrapper;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$results = [];

function add_builder_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'tenant_id' => $tenantId,
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);

    try {
        add_builder_result($tenant->id, 'themes table exists', Schema::hasTable('themes') ? 'PASS' : 'FAIL', Schema::hasTable('themes') ? 'themes table exists.' : 'themes table missing.');
        add_builder_result($tenant->id, 'theme_pages table exists', Schema::hasTable('theme_pages') ? 'PASS' : 'FAIL', Schema::hasTable('theme_pages') ? 'theme_pages table exists.' : 'theme_pages table missing.');

        $theme = app(ThemeBootstrapper::class)->ensureDefaultTheme();
        $homepage = $theme->homepage()->first();

        add_builder_result($tenant->id, 'active theme exists', $theme && $theme->is_active ? 'PASS' : 'FAIL', $theme ? 'Active theme exists.' : 'Active theme missing.');
        add_builder_result($tenant->id, 'homepage exists', $homepage instanceof ThemePage ? 'PASS' : 'FAIL', $homepage ? 'Homepage exists.' : 'Homepage missing.');
        add_builder_result($tenant->id, 'homepage has sections', count($homepage?->draft_config['sections'] ?? []) > 0 ? 'PASS' : 'FAIL', 'Homepage draft config contains sections.', [
            'section_count' => count($homepage?->draft_config['sections'] ?? []),
        ]);

        $schemas = app(SectionRegistry::class)->all();

        add_builder_result($tenant->id, 'section registry has hero', isset($schemas['hero']) ? 'PASS' : 'FAIL', isset($schemas['hero']) ? 'Hero schema registered.' : 'Hero schema missing.');
        add_builder_result($tenant->id, 'section registry has rich text', isset($schemas['rich_text']) ? 'PASS' : 'FAIL', isset($schemas['rich_text']) ? 'Rich text schema registered.' : 'Rich text schema missing.');
        add_builder_result($tenant->id, 'section registry has featured products', isset($schemas['featured_products']) ? 'PASS' : 'FAIL', isset($schemas['featured_products']) ? 'Featured products schema registered.' : 'Featured products schema missing.');

        add_builder_result($tenant->id, 'website builder route exists', Route::getRoutes()->getByName('tenant.website.index') ? 'PASS' : 'FAIL', Route::getRoutes()->getByName('tenant.website.index') ? 'Website builder route exists.' : 'Website builder route missing.');

        add_builder_result($tenant->id, 'website builder page exists', file_exists(base_path('resources/js/pages/tenant/website/Index.vue')) ? 'PASS' : 'FAIL', 'Website builder Vue page exists.');
    } catch (Throwable $e) {
        add_builder_result($tenant->id, 'website builder foundation exception', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
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
