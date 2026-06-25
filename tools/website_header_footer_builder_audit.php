<?php

$files = [
    'editor' => 'resources/js/pages/tenant/website/Editor.vue',
    'header' => 'resources/js/components/tenant/StorefrontHeader.vue',
    'homepage' => 'resources/js/pages/tenant/Homepage.vue',
    'controller' => 'app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php',
    'bootstrapper' => 'app/Services/Themes/ThemeBootstrapper.php',
];

$checks = [
    ['file' => 'editor', 'needle' => 'S64 header footer builder foundation', 'name' => 'S64 marker exists'],
    ['file' => 'editor', 'needle' => 'theme_settings', 'name' => 'editor saves theme settings'],
    ['file' => 'editor', 'needle' => 'Header settings', 'name' => 'header settings inspector exists'],
    ['file' => 'editor', 'needle' => 'Footer settings', 'name' => 'footer settings inspector exists'],
    ['file' => 'editor', 'needle' => 'addHeaderLink', 'name' => 'header link add helper exists'],
    ['file' => 'editor', 'needle' => 'addFooterLink', 'name' => 'footer link add helper exists'],
    ['file' => 'header', 'needle' => 'data-storefront-header="true"', 'name' => 'storefront header marker exists'],
    ['file' => 'header', 'needle' => 'mobileOpen', 'name' => 'mobile hamburger state exists'],
    ['file' => 'header', 'needle' => 'headerLinks', 'name' => 'header links render from settings'],
    ['file' => 'homepage', 'needle' => ':header="themeSettings.header || {}"', 'name' => 'homepage passes header settings'],
    ['file' => 'controller', 'needle' => 'persistThemeSettingsFromRequest', 'name' => 'controller persists theme settings'],
    ['file' => 'bootstrapper', 'needle' => "'header' =>", 'name' => 'bootstrapper header defaults exist'],
    ['file' => 'bootstrapper', 'needle' => "'footer' =>", 'name' => 'bootstrapper footer defaults exist'],
];

$pass = 0;
$failures = [];

foreach ($checks as $check) {
    $path = $files[$check['file']];
    $ok = is_file($path) && str_contains(file_get_contents($path), $check['needle']);

    if ($ok) {
        $pass++;
    } else {
        $failures[] = [
            'name' => $check['name'],
            'status' => 'FAIL',
            'message' => "{$check['needle']} missing from {$path}.",
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
