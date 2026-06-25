<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\Themes\SectionRegistry;
use App\Services\Themes\ThemeBootstrapper;
use App\Services\Themes\ThemePageRenderer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomepageController extends Controller
{
    public function index(
        Request $request,
        ThemeBootstrapper $bootstrapper,
        ThemePageRenderer $renderer,
        SectionRegistry $sectionRegistry
    ) {
        $theme = $bootstrapper->ensureDefaultTheme();
        $homepage = $theme->homepage()->first();

        $pageConfig = $homepage?->published_config ?: ['sections' => []];
        $sections = $renderer->renderableSections($pageConfig);
        $homepageData = $renderer->homepageData($sections);

        $search = trim((string) $request->query('search', ''));
        $category = $request->query('category');
        $sort = $request->query('sort', 'latest');

        $productsQuery = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true);

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($category) {
            $productsQuery->whereHas('category', function ($query) use ($category) {
                $query->where('id', $category)
                    ->orWhere('slug', $category);
            });
        }

        match ($sort) {
            'price_low' => $productsQuery->orderBy('price'),
            'price_high' => $productsQuery->orderByDesc('price'),
            'name' => $productsQuery->orderBy('name'),
            default => $productsQuery->latest(),
        };

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return Inertia::render('tenant/Homepage', [
            'theme' => $theme,
            'homepage' => $homepage,
            'themeSections' => $sections,
            'sectionSchemas' => array_values($sectionRegistry->all()),
            'featuredProducts' => $homepageData['featuredProducts'],
            'products' => $productsQuery->paginate(12)->withQueryString(),
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'sort' => $sort,
            ],
        ]);
    }
}
