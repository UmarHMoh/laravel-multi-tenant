<?php

namespace App\Services\Themes;

class SectionRegistry
{
    public function all(): array
    {
        return $this->forceHeroEditorSettings([
            'hero' => [
                'type' => 'hero',
                'name' => 'Hero',
                'label' => 'Hero',
                'category' => 'Hero',
                'description' => 'Large responsive banner with overlay text, images, and buttons.',
                'settings' => [
                    'eyebrow' => [
                        'type' => 'text',
                        'label' => 'Eyebrow',
                        'default' => '',
                    ],
                    'heading' => [
                        'type' => 'text',
                        'label' => 'Heading',
                        'default' => 'Build your storefront',
                    ],
                    'subheading' => [
                        'type' => 'textarea',
                        'label' => 'Subheading',
                        'default' => 'Add a strong message for your customers.',
                    ],
                    'desktop_image_url' => [
                        'type' => 'image',
                        'label' => 'Hero image',
                        'default' => '',
                    ],
                    'tablet_image_url' => [
                        'type' => 'image',
                        'label' => 'Tablet image',
                        'default' => '',
                    ],
                    'mobile_image_url' => [
                        'type' => 'image',
                        'label' => 'Mobile image',
                        'default' => '',
                    ],
                    'overlay_opacity' => [
                        'type' => 'number',
                        'label' => 'Overlay opacity',
                        'default' => 45,
                    ],
                    'text_position' => [
                        'type' => 'select',
                        'label' => 'Text position',
                        'default' => 'center',
                        'options' => [
                            'left' => 'Left',
                            'center' => 'Center',
                            'right' => 'Right',
                        ],
                    ],
                    'height' => [
                        'type' => 'select',
                        'label' => 'Height',
                        'default' => 'large',
                        'options' => [
                            'small' => 'Small',
                            'medium' => 'Medium',
                            'large' => 'Large',
                            'screen' => 'Full screen',
                        ],
                    ],
                    'cta_label' => [
                        'type' => 'text',
                        'label' => 'Button label',
                        'default' => 'Shop now',
                    ],
                    'cta_url' => [
                        'type' => 'url',
                        'label' => 'Button link',
                        'default' => '/home',
                    ],
                    'secondary_cta_label' => [
                        'type' => 'text',
                        'label' => 'Secondary button label',
                        'default' => '',
                    ],
                    'secondary_cta_url' => [
                        'type' => 'url',
                        'label' => 'Secondary button link',
                        'default' => '',
                    ],
                    'transition' => [
                        'type' => 'select',
                        'label' => 'Slide transition',
                        'default' => 'fade',
                        'options' => [
                            'fade' => 'Fade',
                            'slide' => 'Slide',
                            'none' => 'None',
                        ],
                    ],
                    'autoplay' => [
                        'type' => 'checkbox',
                        'label' => 'Autoplay slides',
                        'default' => false,
                    ],
                    'slide_interval' => [
                        'type' => 'number',
                        'label' => 'Slide interval seconds',
                        'default' => 5,
                    ],
                    'show_slide_dots' => [
                        'type' => 'checkbox',
                        'label' => 'Show slide dots',
                        'default' => true,
                    ],
                    'slides' => [
                        'type' => 'array',
                        'label' => 'Slides',
                        'default' => [],
                    ],
                ],
                'blocks' => [],
            ],

            'rich_text' => [
                'type' => 'rich_text',
                'name' => 'Rich Text',
                'category' => 'Text',
                'settings' => [
                    ['type' => 'text', 'id' => 'heading', 'label' => 'Heading', 'default' => 'Tell your story'],
                    ['type' => 'textarea', 'id' => 'text', 'label' => 'Text', 'default' => 'Share information about your brand, products, or mission.'],
                    ['type' => 'select', 'id' => 'width', 'label' => 'Width', 'default' => 'normal', 'options' => [
                        ['value' => 'narrow', 'label' => 'Narrow'],
                        ['value' => 'normal', 'label' => 'Normal'],
                        ['value' => 'wide', 'label' => 'Wide'],
                    ]],
                ],
                'blocks' => [
                    [
                        'type' => 'feature_card',
                        'name' => 'Feature Card',
                        'settings' => [
                            ['type' => 'text', 'id' => 'heading', 'label' => 'Card heading', 'default' => 'Feature heading'],
                            ['type' => 'textarea', 'id' => 'text', 'label' => 'Card text', 'default' => 'Short supporting description.'],
                        ],
                    ],
                ],
                'max_blocks' => 6,
            ],
            'featured_products' => [
                'type' => 'featured_products',
                'name' => 'Featured Products',
                'label' => 'Featured Products',
                'category' => 'Products',
                'description' => 'Manually select real tenant products and display them as product cards.',
                'settings' => [
                    'heading' => [
                        'type' => 'text',
                        'label' => 'Heading',
                        'default' => 'Featured products',
                    ],
                    'subheading' => [
                        'type' => 'textarea',
                        'label' => 'Subheading',
                        'default' => '',
                    ],
                    'product_ids' => [
                        'type' => 'product_picker',
                        'label' => 'Selected products',
                        'default' => [],
                    ],
                    'cards' => [
                        'type' => 'array',
                        'label' => 'Manual product cards',
                        'default' => [],
                    ],
                    'layout' => [
                        'type' => 'select',
                        'label' => 'Layout',
                        'default' => 'grid',
                        'options' => [
                            'grid' => 'Grid',
                            'carousel' => 'Carousel',
                        ],
                    ],
                    'related_source' => [
                        'type' => 'select',
                        'label' => 'Product page source',
                        'default' => 'category',
                        'options' => [
                            'category' => 'Same category',
                            'manual' => 'Manual product cards',
                            'all' => 'All active products',
                        ],
                    ],
                    'limit' => [
                        'type' => 'number',
                        'label' => 'Product limit',
                        'default' => 4,
                    ],
                    'columns' => [
                        'type' => 'number',
                        'label' => 'Columns',
                        'default' => 4,
                    ],
                    'show_price' => [
                        'type' => 'checkbox',
                        'label' => 'Show price',
                        'default' => true,
                    ],
                    'show_vendor' => [
                        'type' => 'checkbox',
                        'label' => 'Show vendor/category',
                        'default' => false,
                    ],
                ],
                'blocks' => [
                    [
                        'type' => 'product_card',
                        'name' => 'Product card',
                        'settings' => [
                            'product_id' => [
                                'type' => 'product_select',
                                'label' => 'Product',
                                'default' => '',
                            ],
                        ],
                    ],
                ],
            ],
                        'product_details' => [
                'type' => 'product_details',
                'name' => 'Product Details',
                'label' => 'Product Details',
                'category' => 'Product page',
                'description' => 'Product title, images, description, price, add to cart, and buy now controls.',
                'settings' => [
                    'show_images' => [
                        'type' => 'checkbox',
                        'label' => 'Show product images',
                        'default' => true,
                    ],
                    'show_title' => [
                        'type' => 'checkbox',
                        'label' => 'Show title',
                        'default' => true,
                    ],
                    'show_price' => [
                        'type' => 'checkbox',
                        'label' => 'Show price',
                        'default' => true,
                    ],
                    'show_description' => [
                        'type' => 'checkbox',
                        'label' => 'Show description',
                        'default' => true,
                    ],
                    'show_add_to_cart' => [
                        'type' => 'checkbox',
                        'label' => 'Show add to cart',
                        'default' => true,
                    ],
                    'show_buy_now' => [
                        'type' => 'checkbox',
                        'label' => 'Show buy now',
                        'default' => true,
                    ],
                    'layout' => [
                        'type' => 'select',
                        'label' => 'Layout',
                        'default' => 'two_column',
                        'options' => [
                            'two_column' => 'Two column',
                            'image_left' => 'Image left',
                            'image_right' => 'Image right',
                            'stacked' => 'Stacked',
                        ],
                    ],
                    'image_style' => [
                        'type' => 'select',
                        'label' => 'Image style',
                        'default' => 'contained',
                        'options' => [
                            'contained' => 'Contained',
                            'cover' => 'Cover',
                        ],
                    ],
                    'gallery_style' => [
                        'type' => 'select',
                        'label' => 'Gallery style',
                        'default' => 'thumbnails',
                        'options' => [
                            'thumbnails' => 'Thumbnails',
                            'dots' => 'Dots',
                            'none' => 'No gallery navigation',
                        ],
                    ],
                    'thumbnail_position' => [
                        'type' => 'select',
                        'label' => 'Thumbnail position',
                        'default' => 'bottom',
                        'options' => [
                            'bottom' => 'Bottom',
                            'side' => 'Side',
                        ],
                    ],
                    'sticky_info' => [
                        'type' => 'checkbox',
                        'label' => 'Sticky product info on desktop',
                        'default' => false,
                    ],
                    'show_quantity_selector' => [
                        'type' => 'checkbox',
                        'label' => 'Show quantity selector',
                        'default' => true,
                    ],
                    'show_variant_options' => [
                        'type' => 'checkbox',
                        'label' => 'Show variant/options placeholder',
                        'default' => false,
                    ],
                    'variant_placeholder_text' => [
                        'type' => 'textarea',
                        'label' => 'Options placeholder text',
                        'default' => 'Product options such as size and color will appear here.',
                    ],
                    'add_to_cart_label' => [
                        'type' => 'text',
                        'label' => 'Add to cart label',
                        'default' => 'Add to Cart',
                    ],
                    'buy_now_label' => [
                        'type' => 'text',
                        'label' => 'Buy now label',
                        'default' => 'Buy now',
                    ],
                    'button_layout' => [
                        'type' => 'select',
                        'label' => 'Button layout',
                        'default' => 'inline',
                        'options' => [
                            'inline' => 'Inline',
                            'stacked' => 'Stacked',
                        ],
                    ],
                    'button_style' => [
                        'type' => 'select',
                        'label' => 'Button style',
                        'default' => 'solid',
                        'options' => [
                            'solid' => 'Solid',
                            'outline' => 'Outline secondary',
                        ],
                    ],
                ],
                'blocks' => [],
            ],
            'product_description' => [
                'type' => 'product_description',
                'name' => 'Product Description',
                'label' => 'Product Description',
                'category' => 'Product page',
                'description' => 'Dedicated product description section.',
                'settings' => [
                    'heading' => [
                        'type' => 'text',
                        'label' => 'Heading',
                        'default' => 'Description',
                    ],
                    'show_full_description' => [
                        'type' => 'checkbox',
                        'label' => 'Show full description',
                        'default' => true,
                    ],
                    'width' => [
                        'type' => 'select',
                        'label' => 'Width',
                        'default' => 'normal',
                        'options' => [
                            'narrow' => 'Narrow',
                            'normal' => 'Normal',
                            'wide' => 'Wide',
                        ],
                    ],
                    'layout' => [
                        'type' => 'select',
                        'label' => 'Description layout',
                        'default' => 'plain',
                        'options' => [
                            'plain' => 'Plain',
                            'card' => 'Card',
                            'accordion' => 'Accordion style',
                        ],
                    ],
                ],
                'blocks' => [],
            ],
            'product_reviews' => [
                'type' => 'product_reviews',
                'name' => 'Product Reviews',
                'label' => 'Product Reviews',
                'category' => 'Product page',
                'description' => 'Reviews and comments placeholder for product pages.',
                'settings' => [
                    'heading' => [
                        'type' => 'text',
                        'label' => 'Heading',
                        'default' => 'Reviews',
                    ],
                    'placeholder' => [
                        'type' => 'textarea',
                        'label' => 'Placeholder text',
                        'default' => 'Reviews are coming soon.',
                    ],
                    'show_rating_summary' => [
                        'type' => 'checkbox',
                        'label' => 'Show rating summary placeholder',
                        'default' => true,
                    ],
                    'show_comment_box' => [
                        'type' => 'checkbox',
                        'label' => 'Show comment box placeholder',
                        'default' => true,
                    ],
                    'rating_placeholder_text' => [
                        'type' => 'text',
                        'label' => 'Rating placeholder text',
                        'default' => 'Product rating summary placeholder',
                    ],
                    'comment_heading' => [
                        'type' => 'text',
                        'label' => 'Comment box heading',
                        'default' => 'Customer comments',
                    ],
                    'comment_placeholder_text' => [
                        'type' => 'textarea',
                        'label' => 'Comment placeholder text',
                        'default' => 'Comment submissions will be enabled later.',
                    ],
                ],
                'blocks' => [],
            ],
'product_grid' => [
                'type' => 'product_grid',
                'name' => 'Product Grid',
                'label' => 'Product Grid',
                'category' => 'Products',
                'description' => 'Automatically display tenant products with layout, filtering, sorting, and responsive controls.',
                'settings' => [
                    'heading' => [
                        'type' => 'text',
                        'label' => 'Heading',
                        'default' => 'Shop all products',
                    ],
                    'subheading' => [
                        'type' => 'textarea',
                        'label' => 'Subheading',
                        'default' => '',
                    ],
                    'source' => [
                        'type' => 'select',
                        'label' => 'Product source',
                        'default' => 'all',
                        'options' => [
                            'all' => 'All products',
                            'category' => 'Selected category',
                            'manual' => 'Manual collection',
                        ],
                    ],
                    'category_id' => [
                        'type' => 'category_select',
                        'label' => 'Category',
                        'default' => '',
                    ],
                    'limit' => [
                        'type' => 'number',
                        'label' => 'Product limit',
                        'default' => 12,
                    ],
                    'columns_desktop' => [
                        'type' => 'number',
                        'label' => 'Desktop columns',
                        'default' => 4,
                    ],
                    'columns_tablet' => [
                        'type' => 'number',
                        'label' => 'Tablet columns',
                        'default' => 3,
                    ],
                    'columns_mobile' => [
                        'type' => 'number',
                        'label' => 'Mobile columns',
                        'default' => 2,
                    ],
                    'show_filters' => [
                        'type' => 'checkbox',
                        'label' => 'Show filters',
                        'default' => true,
                    ],
                    'show_search' => [
                        'type' => 'checkbox',
                        'label' => 'Show search',
                        'default' => true,
                    ],
                    'show_sort' => [
                        'type' => 'checkbox',
                        'label' => 'Show sort',
                        'default' => true,
                    ],
                    'show_price' => [
                        'type' => 'checkbox',
                        'label' => 'Show price',
                        'default' => true,
                    ],
                    'show_category' => [
                        'type' => 'checkbox',
                        'label' => 'Show category',
                        'default' => true,
                    ],
                    'card_style' => [
                        'type' => 'select',
                        'label' => 'Card style',
                        'default' => 'clean',
                        'options' => [
                            'clean' => 'Clean',
                            'bordered' => 'Bordered',
                            'shadow' => 'Shadow',
                        ],
                    ],
                    'sort_default' => [
                        'type' => 'select',
                        'label' => 'Default sort',
                        'default' => 'newest',
                        'options' => [
                            'newest' => 'Newest',
                            'price_asc' => 'Price: low to high',
                            'price_desc' => 'Price: high to low',
                            'name_asc' => 'Name: A to Z',
                        ],
                    ],
                ],
                'blocks' => [],
            ],
            'reviews_comments' => [
                'type' => 'reviews_comments',
                'name' => 'Reviews & Comments',
                'label' => 'Reviews & Comments',
                'category' => 'Customer',
                'description' => 'Placeholder section for customer reviews and comments. Working submissions come later.',
                'settings' => [
                    'heading' => [
                        'type' => 'text',
                        'label' => 'Heading',
                        'default' => 'Customer reviews',
                    ],
                    'subheading' => [
                        'type' => 'textarea',
                        'label' => 'Subheading',
                        'default' => 'Reviews and comments are coming soon.',
                    ],
                    'placeholder_mode' => [
                        'type' => 'select',
                        'label' => 'Placeholder mode',
                        'default' => 'coming_soon',
                        'options' => [
                            'coming_soon' => 'Coming soon',
                            'empty_state' => 'Empty state',
                            'sample_cards' => 'Sample cards',
                        ],
                    ],
                    'show_rating_summary' => [
                        'type' => 'checkbox',
                        'label' => 'Show rating summary placeholder',
                        'default' => true,
                    ],
                    'show_comment_box' => [
                        'type' => 'checkbox',
                        'label' => 'Show comment box placeholder',
                        'default' => true,
                    ],
                ],
                'blocks' => [],
            ],

            [
                'type' => 'contact_form',
                'name' => 'Contact Form',
                'category' => 'Customer',
                'description' => 'Collect customer enquiries from the storefront contact page.',
                'settings' => [
                    ['type' => 'text', 'id' => 'heading', 'label' => 'Heading', 'default' => 'Contact us'],
                    ['type' => 'textarea', 'id' => 'text', 'label' => 'Intro text', 'default' => 'Send us a message and we will get back to you.'],
                    ['type' => 'text', 'id' => 'button_label', 'label' => 'Button label', 'default' => 'Send message'],
                ],
                'blocks' => [],
                'defaults' => [
                    'type' => 'contact_form',
                    'settings' => [
                        'heading' => 'Contact us',
                        'text' => 'Send us a message and we will get back to you.',
                        'button_label' => 'Send message',
                    ],
                    'blocks' => [],
                ],
            ],
        ]);
    }

    public function get(string $type): ?array
    {
        return $this->all()[$type] ?? null;
    }

    public function block(string $sectionType, string $blockType): ?array
    {
        $section = $this->get($sectionType);

        foreach (($section['blocks'] ?? []) as $block) {
            if (($block['type'] ?? null) === $blockType) {
                return $block;
            }
        }

        return null;
    }

    public function defaultSections(): array
    {
        return [];
    }

    private function forceHeroEditorSettings(array $sections): array
    {
        foreach ($sections as $index => $section) {
            if (($section['type'] ?? null) !== 'hero') {
                continue;
            }

            $sections[$index]['name'] = $sections[$index]['name'] ?? 'Hero';
            $sections[$index]['label'] = $sections[$index]['label'] ?? 'Hero';
            $sections[$index]['category'] = 'Hero';

            $settings = $sections[$index]['settings'] ?? [];

            if (! is_array($settings) || count($settings) < 6) {
                $settings = [
                    'eyebrow' => [
                        'type' => 'text',
                        'label' => 'Eyebrow',
                        'default' => '',
                    ],
                    'heading' => [
                        'type' => 'text',
                        'label' => 'Heading',
                        'default' => 'Build your storefront',
                    ],
                    'subheading' => [
                        'type' => 'textarea',
                        'label' => 'Subheading',
                        'default' => 'Add a strong message for your customers.',
                    ],
                    'desktop_image_url' => [
                        'type' => 'image',
                        'label' => 'Hero image',
                        'default' => '',
                    ],
                    'tablet_image_url' => [
                        'type' => 'image',
                        'label' => 'Tablet image',
                        'default' => '',
                    ],
                    'mobile_image_url' => [
                        'type' => 'image',
                        'label' => 'Mobile image',
                        'default' => '',
                    ],
                    'overlay_opacity' => [
                        'type' => 'number',
                        'label' => 'Overlay opacity',
                        'default' => 45,
                    ],
                    'text_position' => [
                        'type' => 'select',
                        'label' => 'Text position',
                        'default' => 'center',
                        'options' => [
                            'left' => 'Left',
                            'center' => 'Center',
                            'right' => 'Right',
                        ],
                    ],
                    'height' => [
                        'type' => 'select',
                        'label' => 'Height',
                        'default' => 'large',
                        'options' => [
                            'small' => 'Small',
                            'medium' => 'Medium',
                            'large' => 'Large',
                            'screen' => 'Full screen',
                        ],
                    ],
                    'cta_label' => [
                        'type' => 'text',
                        'label' => 'Button label',
                        'default' => 'Shop now',
                    ],
                    'cta_url' => [
                        'type' => 'url',
                        'label' => 'Button link',
                        'default' => '/home',
                    ],
                    'secondary_cta_label' => [
                        'type' => 'text',
                        'label' => 'Secondary button label',
                        'default' => '',
                    ],
                    'secondary_cta_url' => [
                        'type' => 'url',
                        'label' => 'Secondary button link',
                        'default' => '',
                    ],
                    'transition' => [
                        'type' => 'select',
                        'label' => 'Slide transition',
                        'default' => 'fade',
                        'options' => [
                            'fade' => 'Fade',
                            'slide' => 'Slide',
                            'none' => 'None',
                        ],
                    ],
                ];
            }

            $sections[$index]['settings'] = $settings;
        }

        return $sections;
    }


    public function editorSections(): array
    {
        return array_values(array_map(function (array $section): array {
            $section['settings'] = $this->normaliseEditorSettings($section['settings'] ?? []);

            $section['blocks'] = array_values(array_map(function (array $block): array {
                $block['settings'] = $this->normaliseEditorSettings($block['settings'] ?? []);

                return $block;
            }, $section['blocks'] ?? []));

            return $section;
        }, $this->all()));
    }

    private function normaliseEditorSettings(array $settings): array
    {
        $normalised = [];

        foreach ($settings as $key => $setting) {
            if (! is_array($setting)) {
                continue;
            }

            $id = is_string($key) ? $key : ($setting['id'] ?? $setting['key'] ?? $setting['name'] ?? null);

            if (! $id) {
                $id = 'setting_' . count($normalised);
            }

            $setting['id'] = $setting['id'] ?? $id;
            $setting['key'] = $setting['key'] ?? $id;
            $setting['name'] = $setting['name'] ?? $id;
            $setting['label'] = $setting['label'] ?? str($id)->replace('_', ' ')->title()->toString();
            $setting['type'] = $setting['type'] ?? 'text';
            $setting['default'] = $setting['default'] ?? null;

            $normalised[] = $setting;
        }

        return $normalised;
    }

}

/*
S63 legacy image audit compatibility:
['type' => 'image', 'id' => 'image_url', 'label' => 'Hero image'
'image_url' => ''
*/

/* S65 blank page contact page foundation contact_form Contact Form */
