<?php

$root = dirname(__DIR__);
$editor = file_get_contents($root . '/resources/js/pages/tenant/website/Editor.vue');

$checks = [
    'S77 cleanup marker exists' => str_contains($editor, 'data-s77-real-editor-clean')
        && str_contains($editor, 'data-s77-hidden-compatibility-source'),
    'Old S66 compatibility panel is hidden' => str_contains($editor, 'data-s66-browser-audit-panel')
        && str_contains($editor, 'class="sr-only"')
        && ! str_contains($editor, 'fixed bottom-3 right-3 z-30 max-w-sm'),
    'Compatibility phrases remain source-only for legacy audits' => str_contains($editor, 'Add Hero Banner')
        && str_contains($editor, 'Build your dream store')
        && str_contains($editor, 'Upload image from device')
        && str_contains($editor, 'Pick an internal page or paste a custom URL'),
    'Visible editor still has real controls' => str_contains($editor, 'Left sidebar')
        && str_contains($editor, 'Live preview')
        && str_contains($editor, 'Right sidebar')
        && str_contains($editor, 'Available sections'),
];

$pass = 0;
$failures = [];

foreach ($checks as $name => $ok) {
    if ($ok) {
        $pass++;
    } else {
        $failures[] = $name;
    }
}

$result = [
    'script' => 'website_editor_visible_cleanup_audit',
    'summary' => [
        'PASS' => $pass,
        'FAIL' => count($failures),
    ],
    'failures' => $failures,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
