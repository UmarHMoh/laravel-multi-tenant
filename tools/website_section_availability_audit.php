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

$editor = 'resources/js/pages/tenant/website/Editor.vue';

foreach ([
    'data-s79-section-availability',
    'availableSectionSchemas',
    'isProductPageOnlySection',
    'isProductPage',
    'productPageSectionTypes',
    'product_details',
    'product_description',
    'product_reviews',
] as $needle) {
    addFinding(
        "Editor contains {$needle}",
        fileContains($editor, $needle),
        "{$needle} should exist in {$editor}."
    );
}

$editorSource = file_get_contents($editor);

addFinding(
    'Homepage editors filter out Product page category from available sections',
    str_contains($editorSource, "return schemas.filter((schema) => !isProductPageOnlySection(schema))"),
    'Homepage/static/contact editors should not show product-page-only sections in the add-section list.'
);

addFinding(
    'Product page editors prioritize product page sections',
    str_contains($editorSource, 'return [...schemas].sort')
        && str_contains($editorSource, 'leftPriority')
        && str_contains($editorSource, 'rightPriority'),
    'Product page editors should keep all sections but prioritize product page sections.'
);

addFinding(
    'Section categories use availableSectionSchemas',
    str_contains($editorSource, 'availableSectionSchemas.value.map((schema) => schema?.category'),
    'Category filters should be based on page-aware available sections.'
);

addFinding(
    'Pinned sections use availableSectionSchemas',
    str_contains($editorSource, 'favoriteSectionSchemas = computed(() => availableSectionSchemas.value.filter'),
    'Favorite/pinned add-section options should respect page-aware section availability.'
);

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
