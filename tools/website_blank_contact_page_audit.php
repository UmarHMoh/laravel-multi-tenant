<?php

$checks = [
    ['file' => 'app/Http/Controllers/Tenant/PageController.php', 'needle' => 'class PageController', 'name' => 'tenant page controller exists'],
    ['file' => 'app/Http/Controllers/Tenant/PageController.php', 'needle' => 'ensureContactPage', 'name' => 'contact page is ensured'],
    ['file' => 'routes/tenant.php', 'needle' => "/contact", 'name' => 'contact route exists'],
    ['file' => 'routes/tenant.php', 'needle' => "/pages/{handle}", 'name' => 'static page route exists'],
    ['file' => 'app/Services/Themes/SectionRegistry.php', 'needle' => "'contact_form'", 'name' => 'contact form section registered'],
    ['file' => 'app/Services/Themes/ThemeBootstrapper.php', 'needle' => "'type' => 'contact'", 'name' => 'contact page type exists'],
    ['file' => 'resources/js/pages/tenant/Homepage.vue', 'needle' => "section.type === 'contact_form'", 'name' => 'storefront renders contact form'],
    ['file' => 'resources/js/pages/tenant/Homepage.vue', 'needle' => 'data-contact-form-foundation', 'name' => 'contact form foundation marker exists'],
    ['file' => 'resources/js/pages/tenant/website/Editor.vue', 'needle' => "section.type === 'contact_form'", 'name' => 'editor previews contact form'],
    ['file' => 'resources/js/pages/tenant/website/Editor.vue', 'needle' => 'S65 blank page contact page foundation', 'name' => 'S65 editor marker exists'],
];

$pass = 0;
$failures = [];

foreach ($checks as $check) {
    $ok = is_file($check['file']) && str_contains(file_get_contents($check['file']), $check['needle']);

    if ($ok) {
        $pass++;
    } else {
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
        'FAIL' => count($failures),
        'WARN' => 0,
        'INFO' => 0,
    ],
    'failures' => $failures,
    'warnings' => [],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;

exit(count($failures) ? 1 : 0);
