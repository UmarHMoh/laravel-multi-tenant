<?php

$findings = [];

function addFinding(string $name, bool $passed, string $message, array $data = []): void
{
    global $findings;

    $findings[] = [
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $message,
        'data' => $data,
    ];
}

function fileContains(string $path, string $needle): bool
{
    return file_exists($path) && str_contains(file_get_contents($path), $needle);
}

$controller = 'app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php';
$routes = 'routes/web.php';
$registry = 'app/Services/Themes/SectionRegistry.php';
$editor = 'resources/js/pages/tenant/website/Editor.vue';
$homepage = 'resources/js/pages/tenant/Homepage.vue';

foreach ([
    'listMedia',
    "files('tenant-website')",
    "'media' =>",
    'basename(',
] as $needle) {
    addFinding("WebsiteBuilderController contains {$needle}", fileContains($controller, $needle), "{$needle} should exist in WebsiteBuilderController.");
}

foreach ([
    'tenant.website.media.index',
    "->get('/manage/website/media'",
    "->post('/manage/website/media'",
] as $needle) {
    addFinding("web routes contain {$needle}", fileContains($routes, $needle), "{$needle} should exist in web routes.");
}

foreach ([
    'autoplay',
    'slide_interval',
    'show_slide_dots',
    'slides',
    'Slide interval seconds',
] as $needle) {
    addFinding("SectionRegistry hero contains {$needle}", fileContains($registry, $needle), "{$needle} should exist in SectionRegistry.");
}

foreach ([
    'data-s99-hero-slides-editor',
    'data-add-hero-slide',
    'data-duplicate-hero-slide',
    'data-remove-hero-slide',
    'data-hero-slide-editor-card',
    'data-hero-slide-desktop-image',
    'addHeroSlide',
    'duplicateHeroSlide',
    'removeHeroSlide',
    'updateHeroSlideSetting',
    'activeHeroSlide',
    'heroPreviewImage',
    'data-s100-media-library-foundation',
    'data-s100-media-library-picker',
    'editorMediaLibrary',
    'loadEditorMediaLibrary',
    'rememberUploadedMedia',
    'applyMediaToHero',
] as $needle) {
    addFinding("Editor contains {$needle}", fileContains($editor, $needle), "{$needle} should exist in Editor.");
}

foreach ([
    'data-s99-live-hero-slides',
    'heroSettings',
    'heroSlides',
    'heroSectionStyle',
    'slide_interval',
    'show_slide_dots',
    'autoplay',
    'transition',
] as $needle) {
    addFinding("Homepage contains {$needle}", fileContains($homepage, $needle), "{$needle} should exist in Homepage.");
}

$failures = array_values(array_filter(
    $findings,
    fn ($finding) => ($finding['status'] ?? null) === 'FAIL'
));

$summary = [
    'PASS' => count(array_filter($findings, fn ($finding) => ($finding['status'] ?? null) === 'PASS')),
    'FAIL' => count($failures),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => date('Y-m-d H:i:s'),
    'failures' => $failures,
    'warnings' => [],
    'all_findings' => $findings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
