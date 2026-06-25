<?php

$checks = [];
$failures = [];

function s63_check(string $section, string $name, bool $condition, string $message, array $data = []): void
{
    global $checks, $failures;

    $row = [
        'section' => $section,
        'name' => $name,
        'status' => $condition ? 'PASS' : 'FAIL',
        'message' => $message,
        'data' => $data,
    ];

    $checks[] = $row;

    if (! $condition) {
        $failures[] = $row;
    }
}

$registry = file_get_contents(__DIR__ . '/../app/Services/Themes/SectionRegistry.php');
$bootstrapper = file_get_contents(__DIR__ . '/../app/Services/Themes/ThemeBootstrapper.php');
$controller = file_get_contents(__DIR__ . '/../app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php');
$renderer = file_get_contents(__DIR__ . '/../app/Services/Themes/ThemePageRenderer.php');
$homepage = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/Homepage.vue');
$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

s63_check('website_builder_architecture_cleanup', 'stage marker exists', str_contains($homepage, 'S63 clean builder architecture') && str_contains($editor, 'S63 clean builder architecture'), 'S63 markers found.');
s63_check('website_builder_architecture_cleanup', 'homepage default sections blank', str_contains($registry, 'public function defaultSections(): array') && str_contains($registry, 'return [];'), 'Default homepage sections are blank.');
s63_check('website_builder_architecture_cleanup', 'publish does not force homepage sections', ! str_contains($controller, '$draftConfig = $this->ensureRequiredHomepageSections($draftConfig, $sectionRegistry);'), 'Publish does not force hero/product sections.');
s63_check('website_builder_architecture_cleanup', 'reset homepage creates blank draft', str_contains($controller, "'sections' => []") && str_contains($controller, 'blank page'), 'Homepage reset creates blank page.');
s63_check('website_builder_architecture_cleanup', 'theme settings contain header footer', str_contains($bootstrapper, "'header' =>") && str_contains($bootstrapper, "'footer' =>"), 'Header/footer stored in theme settings.');
s63_check('website_builder_architecture_cleanup', 'contact page ensured', str_contains($bootstrapper, 'ensureContactPage') && str_contains($bootstrapper, "'handle' => 'contact'"), 'Contact page ensured.');
s63_check('website_builder_architecture_cleanup', 'featured products use product card blocks', str_contains($registry, "'product_card'") && str_contains($registry, "'product_id'"), 'Featured products use product-card blocks.');
s63_check('website_builder_architecture_cleanup', 'reviews comments section exists', str_contains($registry, 'reviews_comments'), 'Reviews/comments placeholder section exists.');
s63_check('website_builder_architecture_cleanup', 'homepage renderer driven', str_contains($homepage, 'v-for="section in sections"') && str_contains($homepage, 'data-section-type="hero"'), 'Homepage renders real theme sections.');
s63_check('website_builder_architecture_cleanup', 'homepage keeps permanent header footer', str_contains($homepage, '<StorefrontHeader') && str_contains($homepage, 'data-storefront-footer'), 'Header and footer are always rendered.');
s63_check('website_builder_architecture_cleanup', 'editor right sidebar stays in grid', str_contains($editor, 'editorGridClass') && str_contains($editor, 'data-editor-right-sidebar-restored'), 'Editor right sidebar restored.');
s63_check('website_builder_architecture_cleanup', 'tenant isolation preserved in builder data', ! str_contains($controller, 'tenancy()->central') && ! str_contains($renderer, 'tenancy()->central'), 'Builder controller/renderer do not query central tenant data.');

echo json_encode([
    'summary' => [
        'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
        'FAIL' => count($failures),
        'WARN' => 0,
        'INFO' => 0,
    ],
    'generated_at' => date('Y-m-d H:i:s'),
    'failures' => $failures,
    'warnings' => [],
    'all_findings' => $checks,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
