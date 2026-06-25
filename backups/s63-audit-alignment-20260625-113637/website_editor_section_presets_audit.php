<?php

$editor = file_get_contents(__DIR__ . '/../resources/js/pages/tenant/website/Editor.vue');

$checks = [
    'preset options computed exists' => 'const sectionPresetOptions = computed',
    'hero cta preset exists' => "id: 'hero-cta'",
    'hero minimal preset exists' => "id: 'hero-minimal'",
    'product feature preset exists' => "id: 'product-feature'",
    'text block preset exists' => "id: 'text-block'",
    'schema for preset helper exists' => 'function schemaForPreset',
    'add preset helper exists' => 'function addSectionPreset',
    'preset uses add section' => 'addSection(schema)',
    'preset merges settings' => '...cloneConfig(preset.settings || {})',
    'presets panel exists' => 'data-section-presets',
    'presets heading exists' => 'Section presets',
    'presets help text exists' => 'Add ready-made section layouts with starter content.',
    'preset button exists' => 'Add preset {{ preset.label }}',
    'preset click exists' => '@click="addSectionPreset(preset)"',
    'stage marker exists' => 'S55 section templates presets foundation',
];

$results = [];

foreach ($checks as $name => $needle) {
    $passed = str_contains($editor, $needle);

    $results[] = [
        'section' => 'website_editor_section_presets',
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
