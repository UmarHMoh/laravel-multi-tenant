<?php

$root = dirname(__DIR__);
$specPath = $root . '/tests/browser-audits/website-product-page-editor-flow.spec.js';
$spec = file_exists($specPath) ? file_get_contents($specPath) : '';

$checks = [
    'Product page editor flow browser spec exists' => file_exists($specPath),
    'Spec opens tenant product inventory' => str_contains($spec, 'http://tenant1.localhost:8000/manage/product')
        && str_contains($spec, 'data-product-page-editor-link'),
    'Spec opens product page editor' => str_contains($spec, '/manage/website/products/')
        && str_contains($spec, '/editor'),
    'Spec changes product page section settings' => str_contains($spec, 'show_buy_now')
        && str_contains($spec, 'setChecked(false)'),
    'Spec saves and publishes product page draft' => str_contains($spec, 'save draft')
        && str_contains($spec, "name: /^publish$/i"),
    'Spec verifies live product page after publish' => str_contains($spec, 'http://tenant1.localhost:8000/products/')
        && str_contains($spec, 'data-product-page-sections'),
];

$pass = 0;
$failures = [];

foreach ($checks as $name => $ok) {
    if ($ok) {
        $pass++;
    } else {
        $failures[] = $name;
    }
}

$result = [
    'script' => 'website_product_page_editor_flow_audit',
    'summary' => [
        'PASS' => $pass,
        'FAIL' => count($failures),
    ],
    'failures' => $failures,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
