<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\Theme;
use App\Services\Themes\ThemeBootstrapper;

foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);

    echo "Repairing homepage sections for {$tenant->id}..." . PHP_EOL;

    $theme = app(ThemeBootstrapper::class)->ensureDefaultTheme();
    $homepage = $theme->homepage()->first();

    foreach (['draft_config', 'published_config'] as $field) {
        $config = $homepage->{$field} ?: ['sections' => []];
        $sections = $config['sections'] ?? [];

        $hasProductGrid = collect($sections)->contains(fn ($section) => ($section['type'] ?? null) === 'product_grid');

        if (! $hasProductGrid) {
            $sections[] = [
                'id' => 'section_product_grid_default',
                'type' => 'product_grid',
                'hidden' => false,
                'settings' => [
                    'heading' => 'Shop products',
                    'show_filters' => true,
                    'per_page' => 12,
                ],
                'blocks' => [],
            ];
        }

        $config['sections'] = array_values($sections);
        $homepage->{$field} = $config;
    }

    $homepage->save();

    echo "  section count: " . count($homepage->draft_config['sections'] ?? []) . PHP_EOL;

    tenancy()->end();
}

echo "Homepage section repair complete." . PHP_EOL;
