<?php

$root = dirname(__DIR__);
$specPath = $root . '/tests/browser-audits/website-product-page-setting-persistence.spec.js';
$spec = file_exists($specPath) ? file_get_contents($specPath) : '';
$productDetail = file_get_contents($root . '/resources/js/pages/tenant/ProductDetail.vue');
$editor = file_get_contents($root . '/resources/js/pages/tenant/website/Editor.vue');

$checks = [
    'Product setting persistence browser spec exists' => file_exists($specPath),
    'Spec toggles buy now setting off and on' => str_contains($spec, 'show_buy_now')
        && str_contains($spec, 'setChecked(false)')
        && str_contains($spec, 'setChecked(true)'),
    'Spec saves and publishes both changes' => substr_count($spec, 'saveAndPublish(page)') >= 2,
    'Spec verifies live storefront buy now removal and return' => str_contains($spec, 'not.toContainText(/Buy now/i)')
        && str_contains($spec, 'toContainText(/Buy now/i)'),
    'Product detail renders buy now from section setting' => str_contains($productDetail, 'show_buy_now')
        && str_contains($productDetail, 'data-product-details-section'),
    'Editor exposes setting rows with data-setting-id' => str_contains($editor, 'data-setting-id')
        && str_contains($editor, 'show_buy_now'),
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
    'script' => 'website_product_page_setting_persistence_audit',
    'summary' => [
        'PASS' => $pass,
        'FAIL' => count($failures),
    ],
    'failures' => $failures,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
