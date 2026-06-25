<?php

$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

$checks = [
    'editor id helper exists' => 'function makeEditorId',
    'duplicate section id helper exists' => 'function duplicateSectionId',
    'duplicate block id helper exists' => 'function duplicateBlockId',
    'duplicate section action exists' => 'function duplicateSection(index)',
    'duplicate block action exists' => 'function duplicateBlock(index)',
    'duplicate section button exists' => '@click="duplicateSection(index)"',
    'duplicate block button exists' => '@click="duplicateBlock(blockIndex)"',
    'duplicated section selected' => 'selectedSectionId.value = copy.id',
    'duplicated block selected' => 'selectedBlockId.value = copy.id',
    'duplicated section unhides copy' => 'copy.hidden = false',
    'duplicated blocks get new ids' => 'id: duplicateBlockId(block)',
    'stage marker exists' => 'S51 duplicate section and block foundation',
];

$results = [];

foreach ($checks as $name => $needle) {
    $passed = str_contains($editor, $needle);

    $results[] = [
        'section' => 'website_editor_duplicate',
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
