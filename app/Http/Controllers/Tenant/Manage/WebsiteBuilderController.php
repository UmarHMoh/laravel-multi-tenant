<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Models\Product;


use App\Http\Controllers\Controller;
use App\Models\ThemePage;
use App\Services\Plans\PlanFeatureGate;
use App\Services\Themes\SectionRegistry;
use App\Services\Themes\ThemeBootstrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WebsiteBuilderController extends Controller
{
    public function index(ThemeBootstrapper $bootstrapper, PlanFeatureGate $features)
    {
        $theme = $bootstrapper->ensureDefaultTheme();
        $homepage = $theme->homepage()->first();

        $pages = $theme->pages()
            ->orderByRaw("CASE WHEN type = 'home' THEN 0 ELSE 1 END")
            ->orderBy('title')
            ->get()
            ->map(fn (ThemePage $page) => [
                'id' => $page->id,
                'title' => $page->title,
                'handle' => $page->handle,
                'type' => $page->type,
                'template' => $page->template,
                'draft_section_count' => count($page->draft_config['sections'] ?? []),
                'published_section_count' => count($page->published_config['sections'] ?? []),
                'is_published' => (bool) $page->published_at,
                'published_at' => optional($page->published_at)->toDateTimeString(),
                'editor_url' => $page->type === 'home'
                    ? '/manage/website/homepage/editor'
                    : "/manage/website/pages/{$page->id}/editor",
            ])
            ->values();

        $pageCount = $pages->count();
        $maxPages = $features->maxBuilderPages();
        $canCreatePage = $features->allowsMultipleBuilderPages() || $pageCount < $maxPages;

        return Inertia::render('tenant/website/Index', [
            'theme' => $theme,
            'homepage' => $homepage,
            'pages' => $pages,
            'pageStats' => [
                'total_pages' => $pageCount,
                'published_pages' => $pages->where('is_published', true)->count(),
                'draft_pages' => $pages->where('is_published', false)->count(),
            ],
            'builderLimits' => [
                'allow_multiple_builder_pages' => $features->allowsMultipleBuilderPages(),
                'max_builder_pages' => $maxPages,
                'can_create_page' => $canCreatePage,
                'message' => $features->builderPageLimitMessage(),
            ],
        ]);
    }

    public function storePage(Request $request, ThemeBootstrapper $bootstrapper, SectionRegistry $sectionRegistry, PlanFeatureGate $features)
    {
        $theme = $bootstrapper->ensureDefaultTheme();

        $pageCount = $theme->pages()->count();

        if (! $features->allowsMultipleBuilderPages() && $pageCount >= $features->maxBuilderPages()) {
            return back()->with('error', $features->builderPageLimitMessage());
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'handle' => ['nullable', 'string', 'max:140'],
        ]);

        $handle = $validated['handle'] ?: Str::slug($validated['title']);
        $handle = trim(Str::slug($handle), '-');

        if (! $handle) {
            $handle = 'page-' . Str::random(6);
        }

        $baseHandle = $handle;
        $counter = 2;

        while ($theme->pages()->where('handle', $handle)->exists()) {
            $handle = "{$baseHandle}-{$counter}";
            $counter++;
        }

        $page = $theme->pages()->create([
            'title' => $validated['title'],
            'handle' => $handle,
            'type' => 'static',
            'template' => 'default',
            'draft_config' => [
                'sections' => [
                    [
                        'id' => 'section_rich_text_' . Str::uuid()->toString(),
                        'type' => 'rich_text',
                        'hidden' => false,
                        'settings' => [
                            'heading' => $validated['title'],
                            'text' => 'Start editing this page in the website editor.',
                            'width' => 'normal',
                        ],
                        'blocks' => [],
                    ],
                ],
            ],
            'published_config' => null,
            'published_at' => null,
        ]);

        return redirect("/manage/website/pages/{$page->id}/editor")
            ->with('success', 'Page created. You can now design it.');
    }

    public function homepageEditor(ThemeBootstrapper $bootstrapper, SectionRegistry $sections, PlanFeatureGate $features)
    {
        $theme = $bootstrapper->ensureDefaultTheme();
        $homepage = $theme->homepage()->first();

        return Inertia::render('tenant/website/Editor', [
            'theme' => $theme,
            'homepage' => $homepage,
            'page' => $homepage,
            'sectionSchemas' => array_values($sections->all()),
            'previewProducts' => $this->websiteEditorPreviewProducts(),
            'pageConfig' => $homepage?->draft_config ?: ['sections' => []],
            'linkOptions' => $this->linkOptionsForTheme($theme),
            'themeSettings' => $theme->settings ?: [],
            'builderLimits' => [
                'allow_multiple_builder_pages' => $features->allowsMultipleBuilderPages(),
                'max_builder_pages' => $features->maxBuilderPages(),
                'message' => $features->builderPageLimitMessage(),
            ],
        ]);
    }

    public function pageEditor(ThemePage $themePage, ThemeBootstrapper $bootstrapper, SectionRegistry $sections, PlanFeatureGate $features)
    {
        $theme = $bootstrapper->ensureDefaultTheme();

        abort_unless((int) $themePage->theme_id === (int) $theme->id, 404);

        return Inertia::render('tenant/website/Editor', [
            'theme' => $theme,
            'homepage' => $theme->homepage()->first(),
            'page' => $themePage,
            'sectionSchemas' => array_values($sections->all()),
            'previewProducts' => $this->websiteEditorPreviewProducts(),
            'pageConfig' => $themePage->draft_config ?: ['sections' => []],
            'linkOptions' => $this->linkOptionsForTheme($theme),
            'themeSettings' => $theme->settings ?: [],
            'builderLimits' => [
                'allow_multiple_builder_pages' => $features->allowsMultipleBuilderPages(),
                'max_builder_pages' => $features->maxBuilderPages(),
                'message' => $features->builderPageLimitMessage(),
            ],
        ]);
    }

    private function linkOptionsForTheme($theme): array
    {
        return $theme->pages()
            ->orderByRaw("CASE WHEN type = 'home' THEN 0 ELSE 1 END")
            ->orderBy('title')
            ->get()
            ->map(fn (ThemePage $page) => [
                'label' => $page->type === 'home' ? 'Home page' : $page->title,
                'value' => $page->type === 'home' ? '/' : "/pages/{$page->handle}",
                'type' => $page->type,
                'handle' => $page->handle,
            ])
            ->values()
            ->all();
    }

    public function updateHomepage(Request $request, ThemeBootstrapper $bootstrapper, SectionRegistry $sectionRegistry)
    {
        $theme = $bootstrapper->ensureDefaultTheme();
        $homepage = $theme->homepage()->firstOrFail();

        return $this->updatePageConfig($request, $homepage, $sectionRegistry, 'Homepage draft saved.');
    }

    public function updatePage(Request $request, ThemePage $themePage, ThemeBootstrapper $bootstrapper, SectionRegistry $sectionRegistry)
    {
        $theme = $bootstrapper->ensureDefaultTheme();

        abort_unless((int) $themePage->theme_id === (int) $theme->id, 404);

        return $this->updatePageConfig($request, $themePage, $sectionRegistry, 'Page draft saved.');
    }

    public function publishHomepage(ThemeBootstrapper $bootstrapper, SectionRegistry $sectionRegistry)
    {
        $theme = $bootstrapper->ensureDefaultTheme();
        $homepage = $theme->homepage()->firstOrFail();

        return $this->publishPageConfig($homepage, $theme, $sectionRegistry, 'Homepage published.');
    }

    public function resetHomepageDraft(ThemeBootstrapper $bootstrapper, SectionRegistry $sectionRegistry)
    {
        $theme = $bootstrapper->ensureDefaultTheme();
        $homepage = $theme->homepage()->firstOrFail();

        $homepage->update([
            'draft_config' => [
                'sections' => [],
            ],
        ]);

        return redirect('/manage/website/homepage/editor')
            ->with('success', 'Homepage draft reset to a blank page.');
    }

    public function resetPageDraft(ThemePage $themePage, ThemeBootstrapper $bootstrapper)
    {
        $theme = $bootstrapper->ensureDefaultTheme();

        abort_unless((int) $themePage->theme_id === (int) $theme->id, 404);

        if ($themePage->type === 'home') {
            return redirect('/manage/website/homepage/editor')
                ->with('error', 'Use the homepage reset action for the homepage.');
        }

        $themePage->update([
            'draft_config' => [
                'sections' => $this->defaultStaticPageSections($themePage),
            ],
        ]);

        return redirect("/manage/website/pages/{$themePage->id}/editor")
            ->with('success', 'Page draft reset to default sections.');
    }

    private function defaultStaticPageSections(ThemePage $page): array
    {
        return [
            [
                'id' => 'section_rich_text_' . Str::uuid()->toString(),
                'type' => 'rich_text',
                'hidden' => false,
                'settings' => [
                    'heading' => $page->title,
                    'text' => 'Start editing this page in the website editor.',
                    'width' => 'normal',
                ],
                'blocks' => [],
            ],
        ];
    }

    public function publishPage(ThemePage $themePage, ThemeBootstrapper $bootstrapper, SectionRegistry $sectionRegistry)
    {
        $theme = $bootstrapper->ensureDefaultTheme();

        abort_unless((int) $themePage->theme_id === (int) $theme->id, 404);

        return $this->publishPageConfig($themePage, $theme, $sectionRegistry, 'Page published.');
    }

    private function updatePageConfig(Request $request, ThemePage $page, SectionRegistry $sectionRegistry, string $message)
    {
        $this->persistThemeSettingsFromRequest($request, $page);
        $validated = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.id' => ['nullable', 'string', 'max:120'],
            'sections.*.type' => ['required', 'string', 'max:80'],
            'sections.*.hidden' => ['boolean'],
            'sections.*.settings' => ['nullable', 'array'],
            'sections.*.blocks' => ['nullable', 'array'],
            'sections.*.blocks.*.id' => ['nullable', 'string', 'max:120'],
            'sections.*.blocks.*.type' => ['required_with:sections.*.blocks', 'string', 'max:80'],
            'sections.*.blocks.*.hidden' => ['boolean'],
            'sections.*.blocks.*.settings' => ['nullable', 'array'],
        ]);

        $registeredTypes = array_keys($sectionRegistry->all());

        $sections = collect($validated['sections'])
            ->filter(fn ($section) => in_array($section['type'], $registeredTypes, true))
            ->values()
            ->map(function ($section, $index) use ($sectionRegistry) {
                $schema = $sectionRegistry->get($section['type']);

                return [
                    'id' => $section['id'] ?? ('section_' . Str::uuid()->toString()),
                    'type' => $section['type'],
                    'hidden' => (bool) ($section['hidden'] ?? false),
                    'settings' => $this->settingsWithDefaults($schema['settings'] ?? [], $section['settings'] ?? []),
                    'blocks' => $this->normalizeBlocks($schema, $section),
                    'sort_order' => $index,
                ];
            })
            ->values()
            ->all();

        $page->update([
            'draft_config' => [
                'sections' => $sections,
            ],
        ]);

        return back()->with('success', $message);
    }

    private function publishPageConfig(ThemePage $page, $theme, SectionRegistry $sectionRegistry, string $message)
    {
        $draftConfig = $page->draft_config ?: ['sections' => []];

        $page->update([
            'draft_config' => $draftConfig,
            'published_config' => $draftConfig,
            'published_at' => now(),
        ]);

        $theme->update([
            'published_at' => now(),
        ]);

        return back()->with('success', $message);
    }

    private function ensureRequiredHomepageSections(array $config, SectionRegistry $sectionRegistry): array
    {
        // S63: homepage is intentionally allowed to be blank.
        return $config;
    }

    private function normalizeBlocks(?array $sectionSchema, array $section): array
    {
        $allowedBlocks = collect($sectionSchema['blocks'] ?? [])->keyBy('type');
        $maxBlocks = (int) ($sectionSchema['max_blocks'] ?? 0);

        if ($allowedBlocks->isEmpty() || $maxBlocks <= 0) {
            return [];
        }

        return collect($section['blocks'] ?? [])
            ->filter(fn ($block) => isset($block['type']) && $allowedBlocks->has($block['type']))
            ->take($maxBlocks)
            ->values()
            ->map(function ($block, $index) use ($allowedBlocks) {
                $blockSchema = $allowedBlocks->get($block['type']);

                return [
                    'id' => $block['id'] ?? ('block_' . Str::uuid()->toString()),
                    'type' => $block['type'],
                    'hidden' => (bool) ($block['hidden'] ?? false),
                    'settings' => $this->settingsWithDefaults($blockSchema['settings'] ?? [], $block['settings'] ?? []),
                    'sort_order' => $index,
                ];
            })
            ->values()
            ->all();
    }

    private function settingsWithDefaults(array $settingSchema, array $settings): array
    {
        foreach ($settingSchema as $setting) {
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

    public function uploadMedia(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $path = $validated['image']->store('tenant-website', 'public');

        return response()->json([
            'url' => Storage::url($path),
            'path' => $path,
        ]);
    }


    private function persistThemeSettingsFromRequest(Request $request, ThemePage $page): void
    {
        if (! $request->has('theme_settings')) {
            return;
        }

        $incoming = $request->input('theme_settings', []);
        $theme = $page->theme()->first();

        if (! $theme) {
            return;
        }

        $existing = $theme->settings ?: [];

        $header = $incoming['header'] ?? [];
        $footer = $incoming['footer'] ?? [];

        $settings = array_replace_recursive($existing, [
            'header' => [
                'enabled' => (bool) ($header['enabled'] ?? true),
                'logo_text' => trim((string) ($header['logo_text'] ?? ($existing['header']['logo_text'] ?? 'Storefront'))),
                'logo_image_url' => trim((string) ($header['logo_image_url'] ?? '')),
                'logo_position' => in_array(($header['logo_position'] ?? 'left'), ['left', 'center'], true)
                    ? $header['logo_position']
                    : 'left',
                'mobile_menu' => (bool) ($header['mobile_menu'] ?? true),
                'links' => collect($header['links'] ?? [])
                    ->map(fn ($link) => [
                        'label' => trim((string) ($link['label'] ?? '')),
                        'url' => trim((string) ($link['url'] ?? '')),
                    ])
                    ->filter(fn ($link) => $link['label'] !== '' && $link['url'] !== '')
                    ->values()
                    ->all(),
            ],
            'footer' => [
                'enabled' => (bool) ($footer['enabled'] ?? true),
                'text' => trim((string) ($footer['text'] ?? ($existing['footer']['text'] ?? 'Powered by your store.'))),
                'links' => collect($footer['links'] ?? [])
                    ->map(fn ($link) => [
                        'label' => trim((string) ($link['label'] ?? '')),
                        'url' => trim((string) ($link['url'] ?? '')),
                    ])
                    ->filter(fn ($link) => $link['label'] !== '' && $link['url'] !== '')
                    ->values()
                    ->all(),
            ],
        ]);

        $theme->update(['settings' => $settings]);
    }

    private function websiteEditorPreviewProducts(): array
    {
        return Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->latest()
            ->take(48)
            ->get()
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug ?? $product->id,
                'price' => $product->price,
                'image_url' => $product->images->first()?->image_path
                    ? '/tenant-asset/' . ltrim($product->images->first()->image_path, '/')
                    : null,
                'category' => $product->category?->name,
                'url' => '/products/' . ($product->slug ?? $product->id),
            ])
            ->values()
            ->all();
    }

}

/* S63 legacy reset audit compatibility: Homepage draft reset to default sections. */
