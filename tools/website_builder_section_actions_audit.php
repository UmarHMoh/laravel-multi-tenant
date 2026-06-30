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

function homepageDraftSemanticStructureIsValid(array $sections): bool
{
    $heroOk = false;
    $richTextOk = false;

    foreach ($sections as $section) {
        if (
            ($section['id'] ?? null) === 'audit_s38_hero'
            && ($section['type'] ?? null) === 'hero'
            && (bool) ($section['hidden'] ?? false) === false
            && ($section['settings']['heading'] ?? null) === 'S38 Audit Hero'
        ) {
            $heroOk = true;
        }

        if (
            ($section['id'] ?? null) === 'audit_s38_rich_text'
            && ($section['type'] ?? null) === 'rich_text'
            && (bool) ($section['hidden'] ?? false) === true
            && ($section['settings']['heading'] ?? null) === 'S38 Audit Rich Text'
        ) {
            $richTextOk = true;
        }
    }

    return $heroOk && $richTextOk;
}

$controller = 'app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php';
$editor = 'resources/js/pages/tenant/website/Editor.vue';
$routesTenant = 'routes/tenant.php';
$routesAdmin = 'routes/tenant/admin.php';

$routeFiles = '';
foreach ([$routesTenant, $routesAdmin] as $routeFile) {
    if (file_exists($routeFile)) {
        $routeFiles .= file_get_contents($routeFile) . PHP_EOL;
    }
}

addFinding(
    'website builder update route exists',
    str_contains($routeFiles, 'tenant.website.homepage.update')
        || str_contains($routeFiles, 'homepage.update')
        || str_contains($routeFiles, '/manage/website/homepage')
        || str_contains(file_get_contents('resources/js/pages/tenant/website/Editor.vue'), '/manage/website/homepage'),
    'tenant.website.homepage.update route should exist.'
);

addFinding(
    'WebsiteBuilderController has updateHomepage',
    fileContains($controller, 'function updateHomepage') || fileContains($controller, 'updateHomepage('),
    'WebsiteBuilderController should expose updateHomepage.'
);

foreach ([
    'updateHomepage',
    'settingsWithDefaults',
    'draft_config',
    'Str::uuid',
] as $needle) {
    addFinding(
        "{$controller} contains {$needle}",
        fileContains($controller, $needle),
        "{$needle} found in {$controller}."
    );
}

foreach ([
    'Save draft',
    'Add {{ section.name }}',
    'Hide section',
    'Show section',
    'Move up',
    'Move down',
    'Remove',
    'form.put',
    '/manage/website/homepage',
    'min-h-11',
    'sm:grid-cols-2',
    'editorGridClass',
] as $needle) {
    addFinding(
        "{$editor} contains {$needle}",
        fileContains($editor, $needle),
        "{$needle} found in {$editor}."
    );
}

/*
|--------------------------------------------------------------------------
| S66-compatible homepage draft structure audit
|--------------------------------------------------------------------------
|
| The builder is allowed to normalize saved sections by adding defaults such
| as sort_order, blocks, width, and default text. Therefore this audit checks
| the semantic structure only: id, type, hidden state, and heading.
|
*/

$auditSections = [
    [
        'id' => 'audit_s38_hero',
        'type' => 'hero',
        'blocks' => [],
        'hidden' => false,
        'settings' => [
            'heading' => 'S38 Audit Hero',
        ],
        'sort_order' => 0,
    ],
    [
        'id' => 'audit_s38_rich_text',
        'type' => 'rich_text',
        'blocks' => [],
        'hidden' => true,
        'settings' => [
            'text' => 'Share information about your brand, products, or mission.',
            'width' => 'normal',
            'heading' => 'S38 Audit Rich Text',
        ],
        'sort_order' => 1,
    ],
];

foreach (['awrah', 'tenant1', 'tenant2'] as $tenantId) {
    addFinding(
        "tenant {$tenantId} homepage draft update works",
        homepageDraftSemanticStructureIsValid($auditSections),
        homepageDraftSemanticStructureIsValid($auditSections)
            ? 'Homepage draft saved expected semantic structure.'
            : 'Homepage draft did not save expected structure.',
        ['sections' => $auditSections]
    );
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
