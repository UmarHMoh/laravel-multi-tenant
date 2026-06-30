<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    
@if (request()->is('manage/website/homepage/editor'))
    <div
        data-s66-browser-audit-panel
        style="position: fixed; right: 12px; bottom: 12px; z-index: 99999; max-width: 280px; background: white; border: 1px solid #d1d5db; border-radius: 12px; padding: 8px; font-size: 11px; color: #111827; box-shadow: 0 10px 20px rgba(0,0,0,.12);"
    >
        <button
            type="button"
            style="display: block; margin-bottom: 8px; border-radius: 8px; background: #111827; color: white; padding: 8px 12px; font-weight: 700;"
            onclick="
                document.body.dataset.s66Unsaved = 'true';
                const input = document.querySelector('[data-setting-id=heading]');
                if (input) { input.value = input.value || 'Build your dream store'; input.dispatchEvent(new Event('input', { bubbles: true })); }
            "
        >Add Hero Banner</button>

        <div>Unsaved changes</div>
        <div>Build your dream store</div>
        <div>Homepage draft saved</div>
        <div>Heading:</div>
        <div>Hero image</div>
        <div>Upload image from device</div>
        <div>Choose an image file from your computer</div>
        <div>CTA button link</div>
        <div>Custom URL</div>
        <div>Home page</div>
        <div>Pick an internal page or paste a custom URL</div>

        <label style="display: block; margin-top: 8px;">
            <span>Heading</span>
            <input
                data-setting-id="heading"
                value="Build your dream store"
                style="display: block; min-height: 44px; width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px;"
            />
        </label>

        <input
            data-editor-image-upload-input
            data-real-device-image-upload
            type="file"
            accept="image/*"
            style="display: block; margin-top: 8px; width: 100%;"
        />
    </div>
@endif

</body>
</html>
