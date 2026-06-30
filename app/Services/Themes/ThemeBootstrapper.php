<?php

namespace App\Services\Themes;

use App\Models\Theme;
use App\Models\ThemePage;
use Illuminate\Support\Facades\Schema;

class ThemeBootstrapper
{
    public function ensureDefaultTheme(): Theme
    {
        $theme = Theme::active();

        if (! $theme) {
            $theme = Theme::query()->create([
                'name' => 'Default Theme',
                'is_active' => true,
                'settings' => $this->defaultThemeSettings(),
                'published_at' => now(),
            ]);
        } else {
            $theme->update([
                'settings' => array_replace_recursive(
                    $this->defaultThemeSettings(),
                    $theme->settings ?: []
                ),
            ]);
        }

        $this->ensureHomepage($theme);
        $this->ensureContactPage($theme);

        return $theme->fresh(['pages']);
    }

    public function ensureHomepage(Theme $theme): ThemePage
    {
        return ThemePage::query()->firstOrCreate(
            [
                'theme_id' => $theme->id,
                'handle' => 'home',
            ],
            [
                'title' => 'Homepage',
                'type' => 'home',
                'template' => 'home',
                'draft_config' => ['sections' => []],
                'published_config' => ['sections' => []],
                'published_at' => now(),
            ]
        );
    }

    public function ensureContactPage(Theme $theme): ThemePage
    {
        return ThemePage::query()->firstOrCreate(
            [
                'theme_id' => $theme->id,
                'handle' => 'contact',
            ],
            [
                'title' => 'Contact',
                'type' => 'contact',
                'template' => 'contact',
                'draft_config' => [
                    'sections' => [
                        [
                            'id' => 'section_contact_intro_default',
                            'type' => 'rich_text',
                            'hidden' => false,
                            'settings' => [
                                'heading' => 'Contact us',
                                'text' => 'Add your store contact details here.',
                                'width' => 'normal',
                            ],
                            'blocks' => [],
                        ],
                    ],
                ],
                'published_config' => null,
                'published_at' => null,
            ]
        );
    }

    public function ensureProductPage(Theme $theme, $product): ThemePage
    {
        $productId = is_object($product) ? $product->id : $product;
        $productName = is_object($product) ? ($product->name ?? 'Product') : 'Product';
        $hasProductIdColumn = Schema::hasColumn('theme_pages', 'product_id');

        $identity = [
            'theme_id' => $theme->id,
            'type' => 'product',
        ];

        if ($hasProductIdColumn) {
            $identity['product_id'] = $productId;
        } else {
            $identity['handle'] = 'product-' . $productId;
        }

        $create = [
            'handle' => 'product-' . $productId,
            'title' => $productName,
            'draft_config' => [
                'sections' => $this->defaultProductPageSections($productId),
            ],
            'published_config' => [
                'sections' => $this->defaultProductPageSections($productId),
            ],
            'published_at' => now(),
        ];

        if ($hasProductIdColumn) {
            $create['product_id'] = $productId;
        }

        $productPage = ThemePage::query()->firstOrCreate($identity, $create);

        $updates = [];

        if ($hasProductIdColumn && ! $productPage->product_id) {
            $updates['product_id'] = $productId;
        }

        if ($productPage->handle !== 'product-' . $productId) {
            $updates['handle'] = 'product-' . $productId;
        }

        if (! $productPage->title || $productPage->title === 'Product') {
            $updates['title'] = $productName;
        }

        $draftSections = data_get($productPage->draft_config, 'sections', []);
        if (! is_array($draftSections) || count($draftSections) === 0) {
            $updates['draft_config'] = [
                'sections' => $this->defaultProductPageSections($productId),
            ];
        }

        $publishedSections = data_get($productPage->published_config, 'sections', []);
        if (! is_array($publishedSections) || count($publishedSections) === 0) {
            $updates['published_config'] = [
                'sections' => $this->defaultProductPageSections($productId),
            ];
            $updates['published_at'] = $productPage->published_at ?: now();
        }

        if ($updates) {
            $productPage->update($updates);
            $productPage = $productPage->fresh();
        }

        return $productPage;
    }

    public function defaultProductPageSections($productId = null): array
    {
        $suffix = $productId ? '_' . $productId : '';

        return [
            [
                'id' => 'product_details_default' . $suffix,
                'type' => 'product_details',
                'hidden' => false,
                'settings' => [
                    'show_images' => true,
                    'show_title' => true,
                    'show_price' => true,
                    'show_description' => true,
                    'show_add_to_cart' => true,
                    'show_buy_now' => true,
                    'layout' => 'two_column',
                    'image_style' => 'contained',
                    'gallery_style' => 'thumbnails',
                    'thumbnail_position' => 'bottom',
                    'sticky_info' => false,
                    'show_quantity_selector' => true,
                    'show_variant_options' => false,
                    'variant_placeholder_text' => 'Product options such as size and color will appear here.',
                    'add_to_cart_label' => 'Add to Cart',
                    'buy_now_label' => 'Buy now',
                    'button_layout' => 'inline',
                    'button_style' => 'solid',
                ],
                'blocks' => [],
            ],
            [
                'id' => 'product_description_default' . $suffix,
                'type' => 'product_description',
                'hidden' => false,
                'settings' => [
                    'heading' => 'Description',
                    'show_full_description' => true,
                    'width' => 'normal',
                    'layout' => 'plain',
                ],
                'blocks' => [],
            ],
            [
                'id' => 'product_reviews_default' . $suffix,
                'type' => 'product_reviews',
                'hidden' => false,
                'settings' => [
                    'heading' => 'Product Reviews',
                    'placeholder' => 'Reviews are coming soon.',
                    'show_rating_summary' => true,
                    'show_comment_box' => true,
                    'rating_placeholder_text' => 'Product rating summary placeholder',
                    'comment_heading' => 'Customer comments',
                    'comment_placeholder_text' => 'Comment submissions will be enabled later.',
                ],
                'blocks' => [],
            ],
            [
                'id' => 'product_featured_products_default' . $suffix,
                'type' => 'featured_products',
                'hidden' => false,
                'settings' => [
                    'heading' => 'You may also like',
                    'subheading' => '',
                    'product_ids' => [],
                    'cards' => [],
                    'layout' => 'grid',
                    'related_source' => 'category',
                    'limit' => 4,
                    'columns' => 4,
                    'show_price' => true,
                    'show_vendor' => false,
                ],
                'blocks' => [],
            ],
        ];
    }

    private function defaultThemeSettings(): array
    {
        return [
            'colors' => [
                'primary' => '#111827',
                'secondary' => '#ffffff',
            ],
            'header' => [
                'enabled' => true,
                'logo_text' => 'Storefront',
                'logo_image_url' => '',
                'logo_position' => 'left',
                'links' => [
                    ['label' => 'Shop', 'url' => '/home'],
                    ['label' => 'Contact', 'url' => '/pages/contact'],
                    ['label' => 'Cart', 'url' => '/cart'],
                ],
                'mobile_menu' => true,
            ],
            'footer' => [
                'enabled' => true,
                'text' => 'Powered by your store.',
                'links' => [
                    ['label' => 'Shop', 'url' => '/home'],
                    ['label' => 'Contact', 'url' => '/pages/contact'],
                ],
            ],
        ];
    }
}
