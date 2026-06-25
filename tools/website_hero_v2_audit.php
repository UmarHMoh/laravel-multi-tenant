<?php

$checks = [];
$failures = [];

function s66_check(string $name, bool $passed, string $message = ''): void
{
    global $checks, $failures;

    $checks[] = [
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $message,
    ];

    if (! $passed) {
        $failures[] = [
            'name' => $name,
            'message' => $message,
        ];
    }
}

$registry = file_get_contents(__DIR__ . '/../app/Services/Themes/SectionRegistry.php');
$home = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/Homepage.vue');
$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

s66_check('hero schema exists', str_contains($registry, "'hero' =>"), 'Hero schema missing from SectionRegistry.');
s66_check('hero desktop image setting exists', str_contains($registry, 'desktop_image_url'), 'desktop_image_url setting missing.');
s66_check('hero tablet image setting exists', str_contains($registry, 'tablet_image_url'), 'tablet_image_url setting missing.');
s66_check('hero mobile image setting exists', str_contains($registry, 'mobile_image_url'), 'mobile_image_url setting missing.');
s66_check('hero overlay opacity setting exists', str_contains($registry, 'overlay_opacity'), 'overlay_opacity setting missing.');
s66_check('hero CTA setting exists', str_contains($registry, 'cta_url'), 'cta_url setting missing.');
s66_check('hero slides setting exists', str_contains($registry, 'slides'), 'slides setting missing.');
s66_check('storefront renders hero v2', str_contains($home, 'data-hero-v2') && str_contains($home, "section.type === 'hero'"), 'Homepage hero v2 renderer missing.');
s66_check('storefront responsive image helper exists', str_contains($home, 'heroSectionStyle') && str_contains($home, 'mobile_image_url'), 'Hero responsive image helper missing.');
s66_check('editor has hero v2 support marker', str_contains($editor, 'data-hero-v2-editor') && str_contains($editor, 'createHeroV2Section'), 'Editor hero v2 support missing.');

$summary = [
    'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
    'FAIL' => count(array_filter($checks, fn ($check) => $check['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'script' => 'website_hero_v2_audit',
    'summary' => $summary,
    'checks' => $checks,
    'failures' => $failures,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
