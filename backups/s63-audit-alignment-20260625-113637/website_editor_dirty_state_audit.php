<?php

$path = __DIR__ . '/../resources/js/pages/tenant/website/Editor.vue';
$editor = file_get_contents($path);

$checks = [
    'imports lifecycle hooks' => "onBeforeUnmount, onMounted",
    'tracks clean snapshot' => "const cleanSectionsSnapshot = ref",
    'computes unsaved state' => "const hasUnsavedChanges = computed",
    'can mark editor clean' => "function markEditorClean",
    'warns before unload' => "function warnBeforeUnload",
    'registers beforeunload listener' => "window.addEventListener('beforeunload'",
    'removes beforeunload listener' => "window.removeEventListener('beforeunload'",
    'shows unsaved badge' => "Unsaved changes",
    'shows saved badge' => "Saved",
    'save clears dirty state' => "onSuccess: () => {\n      markEditorClean()",
    'stage marker exists' => "S49 dirty-state protection",
];

$results = [];

foreach ($checks as $name => $needle) {
    $passed = str_contains($editor, $needle);

    $results[] = [
        'section' => 'website_editor_dirty_state',
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $passed ? "{$name} found." : "{$name} missing.",
        'data' => [],
    ];
}

$summary = [
    'PASS' => count(array_filter($results, fn ($result) => $result['status'] === 'PASS')),
    'FAIL' => count(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => date('Y-m-d H:i:s'),
    'failures' => array_values(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'warnings' => [],
    'all_findings' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
