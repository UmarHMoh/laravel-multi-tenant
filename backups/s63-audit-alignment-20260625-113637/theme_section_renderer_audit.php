<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Services\Themes\SectionRegistry;
use App\Services\Themes\ThemeBootstrapper;
use App\Services\Themes\ThemePageRenderer;
use Illuminate\Support\Facades\File;

$results = [];

function add_renderer_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
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
        $registry = app(SectionRegistry::class);
        $schemas = $registry->all();

        foreach (['hero', 'rich_text', 'featured_products', 'product_grid'] as $type) {
            add_renderer_result(
                $tenant->id,
                "section schema registered: {$type}",
                isset($schemas[$type]) ? 'PASS' : 'FAIL',
                isset($schemas[$type]) ? "{$type} schema is registered." : "{$type} schema missing."
            );
        }

        $theme = app(ThemeBootstrapper::class)->ensureDefaultTheme();
        $homepage = $theme->homepage()->first();
        $config = $homepage?->activeConfig() ?: ['sections' => []];

        add_renderer_result(
            $tenant->id,
            'homepage active config exists',
            count($config['sections'] ?? []) > 0 ? 'PASS' : 'FAIL',
            'Homepage active config contains sections.',
            ['section_count' => count($config['sections'] ?? [])]
        );

        $renderable = app(ThemePageRenderer::class)->renderableSections($config);
        $types = array_values(array_map(fn ($section) => $section['type'] ?? null, $renderable));

        foreach (['hero', 'featured_products', 'product_grid'] as $type) {
            add_renderer_result(
                $tenant->id,
                "homepage renders {$type}",
                in_array($type, $types, true) ? 'PASS' : 'FAIL',
                in_array($type, $types, true) ? "Homepage renders {$type}." : "Homepage does not render {$type}.",
                ['rendered_types' => $types]
            );
        }

        $homepageData = app(ThemePageRenderer::class)->homepageData($renderable);

        add_renderer_result(
            $tenant->id,
            'homepage data has featured products key',
            array_key_exists('featuredProducts', $homepageData) ? 'PASS' : 'FAIL',
            'Renderer returns featuredProducts data key.'
        );
    } catch (Throwable $e) {
        add_renderer_result($tenant->id, 'theme section renderer exception', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    } finally {
        tenancy()->end();
    }
}

$files = [
    'app/Services/Themes/ThemePageRenderer.php' => [
        'renderableSections',
        'homepageData',
        'settingsWithDefaults',
    ],
    'resources/js/pages/tenant/Homepage.vue' => [
        "section.type === 'hero'",
        "section.type === 'rich_text'",
        "section.type === 'featured_products'",
        "section.type === 'product_grid'",
        'sm:grid-cols-2',
        'lg:grid-cols-3',
        'xl:grid-cols-4',
        'min-h-11',
    ],
];

foreach ($files as $file => $needles) {
    $content = File::exists(base_path($file)) ? File::get(base_path($file)) : '';

    foreach ($needles as $needle) {
        $results[] = [
            'tenant_id' => 'static',
            'name' => "{$file} contains {$needle}",
            'status' => str_contains($content, $needle) ? 'PASS' : 'FAIL',
            'message' => str_contains($content, $needle)
                ? "{$needle} found in {$file}."
                : "{$needle} missing from {$file}.",
            'data' => [],
        ];
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
