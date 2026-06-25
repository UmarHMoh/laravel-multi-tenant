<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Services\Themes\SectionRegistry;
use App\Services\Themes\ThemeBootstrapper;

$registry = app(SectionRegistry::class);
$defaults = collect($registry->defaultSections())->keyBy('type');

foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);

    try {
        $theme = app(ThemeBootstrapper::class)->ensureDefaultTheme();
        $homepage = $theme->homepage()->firstOrFail();

        foreach (['draft_config', 'published_config'] as $column) {
            $config = $homepage->{$column};

            if (! is_array($config)) {
                $config = ['sections' => []];
            }

            $sections = collect($config['sections'] ?? []);

            foreach (['hero', 'featured_products', 'product_grid'] as $requiredType) {
                if (! $sections->contains(fn ($section) => ($section['type'] ?? null) === $requiredType)) {
                    $sections->push($defaults[$requiredType]);
                }
            }

            $config['sections'] = $sections->values()->all();
            $homepage->{$column} = $config;
        }

        if (! $homepage->published_at) {
            $homepage->published_at = now();
        }

        $homepage->save();

        $publishedTypes = collect($homepage->published_config['sections'] ?? [])
            ->pluck('type')
            ->values()
            ->all();

        echo "{$tenant->id}: repaired. published_types=" . implode(',', $publishedTypes) . PHP_EOL;
    } finally {
        tenancy()->end();
    }
}
