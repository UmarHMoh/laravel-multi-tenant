<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Services\Plans\PlanFeatureGate;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $stockStatus = $request->query('stock_status');

        $productsQuery = \App\Models\Product::query()
            ->with('category')
            ->latest();

        if ($search !== '') {
            $productsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($stockStatus === 'out') {
            $productsQuery->where('stock', '<=', 0);
        } elseif ($stockStatus === 'low') {
            $productsQuery->where('stock', '>', 0)->where('stock', '<=', 5);
        } elseif ($stockStatus === 'available') {
            $productsQuery->where('stock', '>', 5);
        }

        return Inertia::render('tenant/products/Index', [
            'products' => $productsQuery->paginate(15)->through(fn ($product) => [
                ...$product->toArray(),
                'product_page_editor_url' => "/manage/website/products/{$product->id}/editor",
                'storefront_url' => "/products/{$product->id}",
            ])->withQueryString(),
            'stats' => [
                'total_products' => \App\Models\Product::count(),
                'active_products' => \App\Models\Product::where('is_active', true)->count(),
                'out_of_stock' => \App\Models\Product::where('stock', '<=', 0)->count(),
                'low_stock' => \App\Models\Product::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
                'inventory_units' => (int) \App\Models\Product::sum('stock'),
            ],
            'filters' => [
                'search' => $search,
                'stock_status' => $stockStatus,
            ],
            'stockStatusOptions' => [
                ['value' => 'available', 'label' => 'Available'],
                ['value' => 'low', 'label' => 'Low Stock'],
                ['value' => 'out', 'label' => 'Out of Stock'],
            ],
        ]);
    }

    /**
     * Show the form for creating a new product.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();

        return Inertia::render('tenant/products/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created product in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $planGate = app(PlanFeatureGate::class);

        if ($planGate->productLimitReached()) {
            return back()
                ->with('error', $planGate->productLimitMessage())
                ->withInput();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Generate slug from name
        $slug = Str::slug($request->name);

        // Generate SKU
        $sku = $this->generateSku($request->name);

        // Create product
        $product = Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'is_active' => $request->input('is_active', true),
            'sku' => $sku,
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $imageFile) {
                try {
                    // Store in tenant's storage
                    $path = $imageFile->store('products', 'public');

                    // Set first image as primary by default
                    $isPrimary = $index === 0;

                    // Create the image record
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => $isPrimary,
                        'sort_order' => $index + 1,
                    ]);
                } catch (\Exception $e) {
                    // Log error
                    Log::error('Failed to upload image: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('product.index')
            ->with('success', 'Product created successfully');
    }

    /**
     * Display the specified product.
     *
     * @param  \App\Models\Product  $product
     * @return \Inertia\Response
     */
    public function show(Product $product)
    {
        $product->load(['category', 'images']);

        return Inertia::render('tenant/products/Show', [
            'product' => array_merge($product->toArray(), [
                'product_page_editor_url' => "/manage/website/products/{$product->id}/editor",
                'storefront_url' => "/products/{$product->id}",
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param  \App\Models\Product  $product
     * @return \Inertia\Response
     */
    public function edit(Product $product)
    {
        $product->load('images');
        $categories = Category::where('is_active', true)->get();

        return Inertia::render('tenant/products/Edit', [
            'product' => array_merge($product->toArray(), [
                'product_page_editor_url' => "/manage/website/products/{$product->id}/editor",
                'storefront_url' => "/products/{$product->id}",
            ]),
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified product in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'boolean',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'removed_images' => 'sometimes|array',
            'removed_images.*' => 'exists:product_images,id',
        ]);

        // Update slug if name has changed
        $slug = $product->name !== $request->name
            ? Str::slug($request->name)
            : $product->slug;

        // Update product
        $product->update([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'is_active' => $request->input('is_active', $product->is_active),
        ]);

        // Handle removed images
        if ($request->has('removed_images') && is_array($request->removed_images)) {
            foreach ($request->removed_images as $imageId) {
                $image = ProductImage::find($imageId);
                if ($image && $image->product_id === $product->id) {
                    // Delete file from storage
                    Storage::disk('public')->delete($image->image_path);
                    // Delete database record
                    $image->delete();
                }
            }
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $currentImagesCount = $product->images()->count();
            $hasPrimaryImage = $product->images()->where('is_primary', true)->exists();

            foreach ($request->file('images') as $index => $imageFile) {
                try {
                    // Store in tenant's storage
                    $path = $imageFile->store('products', 'public');

                    // Set as primary if there's no primary and this is the first image
                    $isPrimary = !$hasPrimaryImage && $currentImagesCount === 0 && $index === 0;

                    // Create the image record
                    $product->images()->create([
                        'image_path' => $path,
                        'is_primary' => $isPrimary,
                        'sort_order' => $currentImagesCount + $index + 1,
                    ]);
                } catch (\Exception $e) {
                    // Log error
                    Log::error('Failed to upload image: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('product.index')
            ->with('success', 'Product updated successfully');
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Product $product)
    {
        // Delete related images
        foreach ($product->images as $image) {
            try {
                Storage::disk('public')->delete($image->image_path);
            } catch (\Exception $e) {
                Log::error('Failed to delete image file: ' . $e->getMessage());
            }
            $image->delete();
        }

        // Delete the product
        $product->delete();

        return redirect()->route('product.index')
            ->with('success', 'Product deleted successfully');
    }

    /**
     * Generate a unique SKU for the product.
     *
     * @param  string  $name
     * @return string
     */
    private function generateSku($name)
    {
        // Take first 3 characters of the name (uppercase)
        $namePrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));

        // Generate a random 6-digit number
        $randomPart = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

        return $namePrefix . '-' . $randomPart;
    }
}
