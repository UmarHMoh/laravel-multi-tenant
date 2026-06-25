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
            $config = is_array($homepage->{$column}) ? $homepage->{$column} : ['sections' => []];
            $sections = collect($config['sections'] ?? []);

            foreach (['hero', 'featured_products', 'product_grid'] as $requiredType) {
                $hasVisible = $sections->contains(fn ($section) => ($section['type'] ?? null) === $requiredType && empty($section['hidden']));

                if (! $hasVisible) {
                    if ($sections->contains(fn ($section) => ($section['type'] ?? null) === $requiredType)) {
                        $sections = $sections->map(function ($section) use ($requiredType) {
                            if (($section['type'] ?? null) === $requiredType) {
                                $section['hidden'] = false;
                            }

                            return $section;
                        });
                    } else {
                        $sections->push($defaults[$requiredType]);
                    }
                }
            }

            $config['sections'] = $sections->values()->all();
            $homepage->{$column} = $config;
        }

        if (! $homepage->published_at) {
            $homepage->published_at = now();
        }

        $homepage->save();

        $visiblePublishedTypes = collect($homepage->published_config['sections'] ?? [])
            ->filter(fn ($section) => empty($section['hidden']))
            ->pluck('type')
            ->values()
            ->all();

        echo "{$tenant->id}: visible_published_types=" . implode(',', $visiblePublishedTypes) . PHP_EOL;
    } finally {
        tenancy()->end();
    }
}
