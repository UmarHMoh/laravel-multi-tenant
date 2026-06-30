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
$source = file_get_contents($editor);

foreach ([
    'data-s80-product-page-singleton-guard',
    'singletonProductPageSectionTypes',
    'canAddSectionSchema',
    'isSingletonProductPageSection',
    'singletonSectionDisabledMessage',
    'hasSectionType',
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

addFinding(
    'Product page singleton types are defined',
    str_contains($source, "const singletonProductPageSectionTypes = ['product_details', 'product_description', 'product_reviews']"),
    'Product page singleton section types should be explicitly defined.'
);

addFinding(
    'Add section function blocks unavailable singleton product sections',
    str_contains($source, 'if (!canAddSectionSchema(schema))')
        && str_contains($source, 'return'),
    'addSection should not add duplicate singleton product page sections.'
);

addFinding(
    'Main add-section buttons use singleton guard',
    str_contains($source, ':disabled="!canAddSectionSchema(section)"')
        && str_contains($source, 'already added'),
    'Main add-section buttons should disable duplicate singleton product page sections.'
);

addFinding(
    'Pinned add-section buttons use singleton guard',
    str_contains($source, ':disabled="!canAddSectionSchema(schema)"')
        && str_contains($source, ':title="singletonSectionDisabledMessage(schema)"'),
    'Pinned add-section buttons should disable duplicate singleton product page sections.'
);

addFinding(
    'Section presets use singleton guard',
    str_contains($source, ':disabled="!schemaForPreset(preset) || !canAddSectionSchema(schemaForPreset(preset))"'),
    'Section presets should respect singleton product section availability.'
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
