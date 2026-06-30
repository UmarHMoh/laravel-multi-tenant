<?php

namespace App\Http\Controllers\Tenant;



use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Theme;
use App\Models\ThemePage;
use App\Services\Themes\ThemeBootstrapper;
use App\Services\Themes\ThemePageRenderer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function __construct(
        private readonly ThemeBootstrapper $themeBootstrapper,
        private readonly ThemePageRenderer $renderer,
    ) {
    }

    public function show(Request $request, string $handle)
    {
        $theme = $this->themeBootstrapper->ensureDefaultTheme();

        $page = ThemePage::query()
            ->where('theme_id', $theme->id)
            ->where('handle', $handle)
            ->whereIn('type', ['static', 'contact'])
            ->firstOrFail();

        $rendered = $this->renderer->render($page, published: true);

        return Inertia::render('tenant/Homepage', [
            'theme' => [
                'id' => $theme->id,
                'name' => $theme->name,
                'settings' => $theme->settings ?: [],
            ],
            'homepage' => [
                'id' => $page->id,
                'title' => $page->title,
                'handle' => $page->handle,
                'type' => $page->type,
                'draft_config' => $page->draft_config ?: ['sections' => []],
                'published_config' => $page->published_config ?: $page->draft_config ?: ['sections' => []],
            ],
            'themeSections' => $rendered['sections'] ?? [],
            'sectionSchemas' => [],
            'featuredProducts' => $this->safeTenantProducts(8),
            'products' => $this->safeTenantProducts(12),
            'categories' => $this->safeTenantCategories(),
            'isContactPage' => $handle === 'contact',
            'filters' => [
                'category' => null,
                'sort' => null,
                'search' => null,
            ],
        ]);
    }

    public function contact(Request $request)
    {
        $theme = Theme::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if ($theme) {
            $this->themeBootstrapper->ensureContactPage($theme);

            $page = ThemePage::query()
                ->where('theme_id', $theme->id)
                ->where('handle', 'contact')
                ->first();
        } else {
            $page = null;
        }

        return Inertia::render('tenant/Contact', [
            'store' => [
                'name' => config('app.name', 'Storefront'),
            ],
            'theme' => $theme,
            'page' => $page,
            'themeSettings' => $theme?->settings ?: [],
            'isContactPage' => true,
        ]);
    }


    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:60'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
            'source_page' => ['nullable', 'string', 'max:160'],
        ]);

        if (! Schema::hasTable('contact_messages')) {
            return back()->with('error', 'Contact messages are not ready yet. Please run tenant migrations.');
        }

        ContactMessage::create([
            ...$validated,
            'source_page' => $validated['source_page'] ?? 'contact',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'is_read' => false,
        ]);

        return back()->with('success', 'Message sent successfully.');
    }

    private function safeTenantProducts(int $limit)
    {
        try {
            if (! Schema::hasTable('products')) {
                return collect();
            }

            return Product::query()
                ->where('is_active', true)
                ->with(['category', 'images'])
                ->latest()
                ->limit($limit)
                ->get();
        } catch (QueryException) {
            return collect();
        }
    }

    private function safeTenantCategories()
    {
        try {
            if (! Schema::hasTable('categories')) {
                return collect();
            }

            return Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } catch (QueryException) {
            return collect();
        }
    }

}
