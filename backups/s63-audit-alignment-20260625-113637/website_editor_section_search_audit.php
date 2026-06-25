<?php

$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

$checks = [
    'search query state exists' => "const sectionSearchQuery = ref('')",
    'search normaliser exists' => 'function normaliseSearchText',
    'apostrophe handling exists' => "replace(/['’`]/g, '')",
    'plural handling exists' => "replace(/\\b([a-z0-9]+)s\\b/g, '$1')",
    'levenshtein helper exists' => 'function levenshteinDistance',
    'near match helper exists' => 'function isNearSearchMatch',
    'haystack helper exists' => 'function sectionSearchHaystack',
    'section match helper exists' => 'function sectionMatchesSearch',
    'filtered schemas computed exists' => 'const filteredSectionSchemas = computed',
    'section list uses filtered schemas' => 'filteredSectionSchemas',
    'search input is bound' => 'v-model="sectionSearchQuery"',
    'smart search help text exists' => 'Smart search handles apostrophes, plurals, and small spelling mistakes.',
    'empty state exists' => 'No matching sections found. Try a shorter word.',
    'stage marker exists' => 'S52 smart fuzzy section search',
];

$results = [];

foreach ($checks as $name => $needle) {
    $passed = str_contains($editor, $needle);

    $results[] = [
        'section' => 'website_editor_section_search',
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
