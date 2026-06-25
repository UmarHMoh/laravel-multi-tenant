<?php

namespace App\Services\Themes;

use App\Models\Product;

class ThemePageRenderer
{
    public function renderableSections(array $pageConfig): array
    {
        $registry = app(SectionRegistry::class);
        $sections = $pageConfig['sections'] ?? [];

        return collect($sections)
            ->filter(fn ($section) => empty($section['hidden']))
            ->map(function ($section) use ($registry) {
                $schema = $registry->get((string) ($section['type'] ?? ''));

                if (! $schema) {
                    return null;
                }

                return [
                    'id' => $section['id'] ?? ('section_' . uniqid()),
                    'type' => $section['type'],
                    'name' => $schema['name'],
                    'settings' => $this->settingsWithDefaults($schema, $section['settings'] ?? []),
                    'blocks' => $this->visibleBlocks($section['blocks'] ?? []),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function homepageData(array $sections): array
    {
        $needsProducts = collect($sections)->contains(function ($section) {
            if (in_array($section['type'] ?? '', ['featured_products', 'product_grid'], true)) {
                return true;
            }

            return collect($section['blocks'] ?? [])->contains(fn ($block) => ($block['type'] ?? '') === 'product_card');
        });

        return [
            'featuredProducts' => $needsProducts
                ? Product::query()
                    ->with(['category', 'images'])
                    ->where('is_active', true)
                    ->latest()
                    ->limit(24)
                    ->get()
                : collect(),
        ];
    }

    private function visibleBlocks(array $blocks): array
    {
        return collect($blocks)
            ->filter(fn ($block) => empty($block['hidden']))
            ->values()
            ->all();
    }

    protected function settingsWithDefaults(array $schema, array $settings): array
    {
        foreach (($schema['settings'] ?? []) as $setting) {
            $id = $setting['id'] ?? null;

            if (! $id) {
                continue;
            }

            if (! array_key_exists($id, $settings)) {
                $settings[$id] = $setting['default'] ?? null;
            }
        }

        return $settings;
    }

    public function render($page, bool $published = true): array
    {
        $config = $published
            ? ($page->published_config ?: $page->draft_config ?: ['sections' => []])
            : ($page->draft_config ?: $page->published_config ?: ['sections' => []]);

        $sections = $config['sections'] ?? [];

        $sections = array_values(array_filter($sections, function ($section) {
            return is_array($section) && ! ($section['hidden'] ?? false);
        }));

        return [
            'sections' => $sections,
        ];
    }

}
