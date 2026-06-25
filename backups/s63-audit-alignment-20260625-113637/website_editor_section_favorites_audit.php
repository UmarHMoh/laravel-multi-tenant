<?php

$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

$checks = [
    'favorite state exists' => 'const favoriteSectionTypes = ref(loadFavoriteSectionTypes())',
    'load favorites helper exists' => 'function loadFavoriteSectionTypes',
    'save favorites helper exists' => 'function saveFavoriteSectionTypes',
    'favorite lookup helper exists' => 'function isFavoriteSectionType',
    'favorite toggle helper exists' => 'function toggleFavoriteSectionType',
    'local storage key exists' => 'website_editor_favorite_section_types',
    'favorites watcher exists' => 'watch(favoriteSectionTypes',
    'favorite schemas computed exists' => 'const favoriteSectionSchemas = computed',
    'favorites panel exists' => 'data-section-favorites',
    'favorites heading exists' => 'Favorite sections',
    'empty favorites helper text exists' => 'Pin sections you use often for quick access.',
    'pin button exists' => '@click.stop="toggleFavoriteSectionType(schema.type)"',
    'pinned add button exists' => 'Add pinned {{ schema.name }}',
    'stage marker exists' => 'S54 section favorites foundation',
];

$results = [];

foreach ($checks as $name => $needle) {
    $passed = str_contains($editor, $needle);

    $results[] = [
        'section' => 'website_editor_section_favorites',
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
