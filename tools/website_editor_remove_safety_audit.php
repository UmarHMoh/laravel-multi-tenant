<?php

$checks = array (
  0 => 
  array (
    'type' => 'file_contains',
    'file' => 'resources/js/pages/tenant/website/Editor.vue',
    'needle' => 'Header · locked',
    'name' => 'header locked exists',
  ),
  1 => 
  array (
    'type' => 'file_contains',
    'file' => 'resources/js/pages/tenant/website/Editor.vue',
    'needle' => 'Footer · locked',
    'name' => 'footer locked exists',
  ),
  2 => 
  array (
    'type' => 'file_contains',
    'file' => 'resources/js/pages/tenant/website/Editor.vue',
    'needle' => 'remove button disabled for required sections',
    'name' => 'legacy remove marker exists',
  ),
  3 => 
  array (
    'type' => 'file_contains',
    'file' => 'resources/js/pages/tenant/website/Editor.vue',
    'needle' => 'S50 remove safety polish',
    'name' => 'stage marker exists',
  ),
);

$pass = 0;
$fail = 0;
$failures = [];

foreach ($checks as $check) {
    $ok = true;

    if (($check['type'] ?? '') === 'file_contains') {
        $ok = is_file($check['file']) && str_contains(file_get_contents($check['file']), $check['needle']);
    } elseif (($check['type'] ?? '') === 'file_not_contains') {
        $ok = is_file($check['file']) && ! str_contains(file_get_contents($check['file']), $check['needle']);
    } elseif (($check['type'] ?? '') === 'file_exists') {
        $ok = is_file($check['file']);
    }

    if ($ok) {
        $pass++;
    } else {
        $fail++;
        $failures[] = [
            'name' => $check['name'] ?? 'check',
            'status' => 'FAIL',
            'message' => $check['message'] ?? 'S63 audit check failed.',
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