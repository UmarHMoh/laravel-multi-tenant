<?php

$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

$checks = [
    'active category state exists' => "const activeSectionCategory = ref('all')",
    'category options computed exists' => 'const sectionCategoryOptions = computed',
    'visible section schemas computed exists' => 'const visibleSectionSchemas = computed',
    'category label helper exists' => 'function sectionCategoryLabel',
    'category count helper exists' => 'function sectionCategoryCount',
    'category filter UI exists' => 'data-section-category-filters',
    'category heading exists' => 'Section categories',
    'category button loop exists' => 'v-for="category in sectionCategoryOptions"',
    'active category class exists' => 'activeSectionCategory === category',
    'category click action exists' => '@click="activeSectionCategory = category"',
    'section list uses visible schemas' => 'visibleSectionSchemas',
    'empty state uses category or search' => "activeSectionCategory !== 'all'",
    'stage marker exists' => 'S53 section categories grouping foundation',
];

$results = [];

foreach ($checks as $name => $needle) {
    $passed = str_contains($editor, $needle);

    $results[] = [
        'section' => 'website_editor_section_categories',
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
