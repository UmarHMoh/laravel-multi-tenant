<?php

$checks = array (
  0 => 
  array (
    'type' => 'file_contains',
    'file' => 'resources/js/pages/tenant/website/Editor.vue',
    'needle' => 'function duplicateSectionId',
    'name' => 'duplicate section id helper exists',
  ),
  1 => 
  array (
    'type' => 'file_contains',
    'file' => 'resources/js/pages/tenant/website/Editor.vue',
    'needle' => 'function duplicateBlockId',
    'name' => 'duplicate block id helper exists',
  ),
  2 => 
  array (
    'type' => 'file_contains',
    'file' => 'resources/js/pages/tenant/website/Editor.vue',
    'needle' => 'duplicated section selected',
    'name' => 'duplicated section selected',
  ),
  3 => 
  array (
    'type' => 'file_contains',
    'file' => 'resources/js/pages/tenant/website/Editor.vue',
    'needle' => 'duplicated block selected',
    'name' => 'duplicated block selected',
  ),
  4 => 
  array (
    'type' => 'file_contains',
    'file' => 'resources/js/pages/tenant/website/Editor.vue',
    'needle' => 'duplicated blocks get new ids',
    'name' => 'duplicated blocks get new ids',
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