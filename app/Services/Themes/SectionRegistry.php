<?php

namespace App\Services\Themes;

class SectionRegistry
{
    public function all(): array
    {
        return [
            'hero' => [
                'type' => 'hero',
                'label' => 'Hero',
                'description' => 'Large visual banner with responsive images, overlay text, and CTA buttons.',
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
                        'label' => 'Desktop image',
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
                        'type' => 'range',
                        'label' => 'Overlay opacity',
                        'default' => 45,
                        'min' => 0,
                        'max' => 90,
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
                        'type' => 'text',
                        'label' => 'Button link',
                        'default' => '/home',
                    ],
                    'secondary_cta_label' => [
                        'type' => 'text',
                        'label' => 'Secondary button label',
                        'default' => '',
                    ],
                    'secondary_cta_url' => [
                        'type' => 'text',
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
                    'slides' => [
                        'type' => 'array',
                        'label' => 'Slides',
                        'default' => [],
                    ],
                ],
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
                'category' => 'Products',
                'settings' => [
                    ['type' => 'text', 'id' => 'heading', 'label' => 'Heading', 'default' => 'Featured products'],
                    ['type' => 'select', 'id' => 'alignment', 'label' => 'Alignment', 'default' => 'center', 'options' => [
                        ['value' => 'left', 'label' => 'Left'],
                        ['value' => 'center', 'label' => 'Center'],
                        ['value' => 'right', 'label' => 'Right'],
                    ]],
                ],
                'blocks' => [
                    [
                        'type' => 'product_card',
                        'name' => 'Product Card',
                        'settings' => [
                            ['type' => 'product', 'id' => 'product_id', 'label' => 'Product', 'default' => ''],
                            ['type' => 'checkbox', 'id' => 'show_price', 'label' => 'Show price', 'default' => true],
                            ['type' => 'checkbox', 'id' => 'show_category', 'label' => 'Show category', 'default' => true],
                        ],
                    ],
                ],
                'max_blocks' => 12,
            ],

            'product_grid' => [
                'type' => 'product_grid',
                'name' => 'Product Grid',
                'category' => 'Products',
                'settings' => [
                    ['type' => 'text', 'id' => 'heading', 'label' => 'Heading', 'default' => 'Shop products'],
                    ['type' => 'checkbox', 'id' => 'show_filters', 'label' => 'Show filters', 'default' => true],
                    ['type' => 'checkbox', 'id' => 'show_price', 'label' => 'Show price', 'default' => true],
                    ['type' => 'checkbox', 'id' => 'show_category', 'label' => 'Show category', 'default' => true],
                    ['type' => 'number', 'id' => 'per_page', 'label' => 'Products per page', 'default' => 12],
                    ['type' => 'select', 'id' => 'alignment', 'label' => 'Alignment', 'default' => 'left', 'options' => [
                        ['value' => 'left', 'label' => 'Left'],
                        ['value' => 'center', 'label' => 'Center'],
                    ]],
                    ['type' => 'select', 'id' => 'columns_desktop', 'label' => 'Desktop columns', 'default' => '4', 'options' => [
                        ['value' => '2', 'label' => '2'],
                        ['value' => '3', 'label' => '3'],
                        ['value' => '4', 'label' => '4'],
                    ]],
                    ['type' => 'select', 'id' => 'columns_tablet', 'label' => 'Tablet columns', 'default' => '2', 'options' => [
                        ['value' => '1', 'label' => '1'],
                        ['value' => '2', 'label' => '2'],
                    ]],
                    ['type' => 'select', 'id' => 'columns_mobile', 'label' => 'Mobile columns', 'default' => '1', 'options' => [
                        ['value' => '1', 'label' => '1'],
                        ['value' => '2', 'label' => '2'],
                    ]],
                ],
                'blocks' => [
                    [
                        'type' => 'info_note',
                        'name' => 'Info Note',
                        'settings' => [
                            ['type' => 'text', 'id' => 'heading', 'label' => 'Note heading', 'default' => 'Need help choosing?'],
                            ['type' => 'textarea', 'id' => 'text', 'label' => 'Note text', 'default' => 'Add a short message above the product grid.'],
                        ],
                    ],
                ],
                'max_blocks' => 2,
            ],

            'reviews_comments' => [
                'type' => 'reviews_comments',
                'name' => 'Reviews & Comments',
                'category' => 'Customer',
                'settings' => [
                    ['type' => 'text', 'id' => 'heading', 'label' => 'Heading', 'default' => 'Customer reviews'],
                    ['type' => 'textarea', 'id' => 'text', 'label' => 'Intro text', 'default' => 'Reviews and comments will be enabled in a later stage.'],
                ],
                'blocks' => [],
                'max_blocks' => 0,
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
        ];
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
}

/*
S63 legacy image audit compatibility:
['type' => 'image', 'id' => 'image_url', 'label' => 'Hero image'
'image_url' => ''
*/

/* S65 blank page contact page foundation contact_form Contact Form */
