<?php

$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

$checks = [
    'required homepage section list exists' => 'requiredHomepageSectionTypes',
    'required homepage section helper exists' => 'function isRequiredHomepageSection',
    'required homepage label helper exists' => 'function requiredHomepageSectionLabel',
    'required removal alert exists' => 'This section is required on the homepage. Hide it instead of removing it.',
    'required badge exists' => 'Required homepage section',
    'protected button text exists' => 'Protected',
    'remove button disabled for required sections' => ':disabled="isRequiredHomepageSection(section)"',
    'section removal has stronger confirmation' => 'Remove this section from the draft? This cannot be undone unless you reset the page.',
    'block removal has stronger confirmation' => 'Remove this block from the section? This cannot be undone unless you reset the page.',
    'stage marker exists' => 'S50 remove safety polish',
];

$results = [];

foreach ($checks as $name => $needle) {
    $passed = str_contains($editor, $needle);

    $results[] = [
        'section' => 'website_editor_remove_safety',
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
