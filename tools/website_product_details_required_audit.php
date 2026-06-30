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
    'data-s81-product-details-required-protection',
    'isRequiredProductPageSection',
    'requiredProductPageSectionMessage',
    "section?.type === 'product_details'",
    'Product Details is required on product pages',
] as $needle) {
    addFinding(
        "Editor contains {$needle}",
        fileContains($editor, $needle),
        "{$needle} should exist in {$editor}."
    );
}

addFinding(
    'Remove section blocks required Product Details',
    str_contains($source, 'function removeSection(index)')
        && str_contains($source, 'if (isRequiredProductPageSection(section))')
        && str_contains($source, 'alert(requiredProductPageSectionMessage(section))'),
    'removeSection should block Product Details on product pages.'
);

addFinding(
    'Toggle section blocks required Product Details',
    str_contains($source, 'function toggleSection(index)')
        && str_contains($source, 'if (isRequiredProductPageSection(section))')
        && str_contains($source, 'form.sections[index].hidden = !form.sections[index].hidden'),
    'toggleSection should block hiding Product Details on product pages.'
);

addFinding(
    'Duplicate section blocks singleton product page sections',
    str_contains($source, 'function duplicateSection(index)')
        && str_contains($source, 'isProductPage.value && isSingletonProductPageSection(source)'),
    'duplicateSection should block duplicating Product Details and other singleton product page sections.'
);

addFinding(
    'Hide button is disabled for Product Details',
    str_contains($source, ':disabled="isRequiredProductPageSection(section)"')
        && str_contains($source, ':title="requiredProductPageSectionMessage(section)"'),
    'Hide/remove controls should be disabled for required Product Details.'
);

addFinding(
    'Remove button shows Required for Product Details',
    str_contains($source, "isRequiredProductPageSection(section) ? 'Required' : 'Remove'"),
    'Remove button should show Required for protected Product Details.'
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
