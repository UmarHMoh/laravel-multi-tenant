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
    'data-s82-product-page-ui-polish',
    'productPageSectionStatusLabel',
    'productPageSectionStatusClass',
    'productPageSectionHelpText',
    'data-product-page-section-status',
    'data-product-page-section-help',
    'data-selected-product-page-section-status',
    'data-selected-product-page-section-help',
    'Optional · Placeholder',
    'Core product layout',
    'Working reviews can be connected later',
] as $needle) {
    addFinding(
        "Editor contains {$needle}",
        fileContains($editor, $needle),
        "{$needle} should exist in {$editor}."
    );
}

addFinding(
    'Product Details is labelled Required',
    str_contains($source, "section?.type === 'product_details'")
        && str_contains($source, "return 'Required'"),
    'Product Details should show Required status.'
);

addFinding(
    'Product Reviews is labelled Optional Placeholder',
    str_contains($source, "section?.type === 'product_reviews'")
        && str_contains($source, "return 'Optional · Placeholder'"),
    'Product Reviews should show Optional Placeholder status.'
);

addFinding(
    'Product Description is labelled Optional by fallback',
    str_contains($source, "return 'Optional'")
        && str_contains($source, 'Optional supporting section'),
    'Product Description should show Optional status/help.'
);

addFinding(
    'Left sidebar displays section status and help',
    str_contains($source, 'data-product-page-section-status')
        && str_contains($source, 'data-product-page-section-help'),
    'Left sidebar section list should display product page status/help.'
);

addFinding(
    'Inspector displays selected section status and help',
    str_contains($source, 'data-selected-product-page-section-status')
        && str_contains($source, 'data-selected-product-page-section-help'),
    'Inspector should display selected product page status/help.'
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
