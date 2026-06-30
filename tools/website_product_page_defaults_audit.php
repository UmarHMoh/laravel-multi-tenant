<?php

$root = dirname(__DIR__);
$bootstrapper = file_get_contents($root . '/app/Services/Themes/ThemeBootstrapper.php');
$controller = file_get_contents($root . '/app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php');

$checks = [
    'Product page defaults helper exists' => str_contains($bootstrapper, 'function defaultProductPageSections')
        && str_contains($bootstrapper, 'product_details')
        && str_contains($bootstrapper, 'product_description')
        && str_contains($bootstrapper, 'product_reviews')
        && str_contains($bootstrapper, 'featured_products'),
    'Product pages are created with full default sections' => str_contains($bootstrapper, "\$this->defaultProductPageSections(\$productId)")
        && substr_count($bootstrapper, 'defaultProductPageSections($productId)') >= 2,
    'Product reset uses product page defaults' => str_contains($controller, "\$themePage->type === 'product'")
        && str_contains($controller, 'defaultProductPageSections($themePage->product_id)'),
    'Product reset redirects back to product editor' => str_contains($controller, '/manage/website/products/{$themePage->product_id}/editor'),
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
    'script' => 'website_product_page_defaults_audit',
    'summary' => [
        'PASS' => $pass,
        'FAIL' => count($failures),
    ],
    'failures' => $failures,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(count($failures) > 0 ? 1 : 0);
