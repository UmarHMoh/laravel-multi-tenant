<?php

namespace App\Services\Themes;

use App\Models\Theme;
use App\Models\ThemePage;

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

/* S65 blank contact defaults: contact page uses contact_form and homepage may remain sections => [] */
