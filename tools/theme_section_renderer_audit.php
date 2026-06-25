<?php

$checks = [
    [
        'name' => 'renderer exists',
        'file' => 'app/Services/Themes/ThemePageRenderer.php',
        'needle' => 'renderableSections',
    ],
    [
        'name' => 'blank homepage supported',
        'file' => 'app/Services/Themes/ThemeBootstrapper.php',
        'needle' => "'sections' => []",
    ],
    [
        'name' => 'storefront still renders builder sections',
        'file' => 'resources/js/pages/tenant/Homepage.vue',
        'needle' => 'section.type ===',
    ],
];

$pass = 0;
$fail = 0;
$failures = [];

foreach ($checks as $check) {
    $ok = is_file($check['file']) && str_contains(file_get_contents($check['file']), $check['needle']);

    if ($ok) {
        $pass++;
    } else {
        $fail++;
        $failures[] = [
            'name' => $check['name'],
            'status' => 'FAIL',
            'message' => "{$check['needle']} missing from {$check['file']}.",
            'data' => [],
        ];
    }
}

echo json_encode([
    'summary' => [
        'PASS' => $pass,
        'FAIL' => $fail,
        'WARN' => 0,
        'INFO' => 0,
    ],
    'failures' => $failures,
    'warnings' => [],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
