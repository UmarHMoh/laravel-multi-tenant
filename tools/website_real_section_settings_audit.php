<?php

$checks = [];
$failures = [];

function s72_check(string $name, bool $passed, string $message = ''): void
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
$controller = file_get_contents(__DIR__ . '/../app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php');
$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

s72_check('registry exposes editorSections', str_contains($registry, 'function editorSections') && str_contains($registry, 'normaliseEditorSettings'), 'editorSections normalization missing.');
s72_check('editorSections preserves real all registry source', str_contains($registry, '$this->all()'), 'editorSections must wrap real registry all().');
s72_check('controller uses normalized editor schemas', str_contains($controller, 'editorSections()'), 'WebsiteBuilderController is not using editorSections().');
s72_check('editor has section setting fallback normalizer', str_contains($editor, 's72SettingList') && str_contains($editor, 's72SettingCount'), 'Editor fallback normalizer missing.');
s72_check('S72 marker exists', str_contains($editor, 'data-s72-real-section-settings'), 'S72 marker missing.');

$summary = [
    'PASS' => count(array_filter($checks, fn ($check) => $check['status'] === 'PASS')),
    'FAIL' => count(array_filter($checks, fn ($check) => $check['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'script' => 'website_real_section_settings_audit',
    'summary' => $summary,
    'checks' => $checks,
    'failures' => $failures,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
