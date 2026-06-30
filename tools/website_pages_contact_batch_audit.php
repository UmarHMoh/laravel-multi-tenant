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

$files = [
    'contact_model' => 'app/Models/ContactMessage.php',
    'contact_controller' => 'app/Http/Controllers/Tenant/Manage/ContactMessageController.php',
    'page_controller' => 'app/Http/Controllers/Tenant/PageController.php',
    'website_controller' => 'app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php',
    'contact_vue' => 'resources/js/pages/tenant/Contact.vue',
    'messages_vue' => 'resources/js/pages/tenant/contact-messages/Index.vue',
    'website_index' => 'resources/js/pages/tenant/website/Index.vue',
    'tenant_routes' => 'routes/tenant.php',
    'web_routes' => 'routes/web.php',
];

foreach ($files as $label => $path) {
    addFinding("{$label} exists", file_exists($path), "{$path} should exist.");
}

foreach ([
    'contact_messages',
    'is_read',
    'read_at',
] as $needle) {
    addFinding("Contact migration contains {$needle}", count(glob('database/migrations/tenant/*create_contact_messages_table.php')) > 0, 'Contact messages migration should exist.');
}

foreach ([
    'storeContact',
    'ContactMessage::create',
    'contact_messages',
] as $needle) {
    addFinding("PageController contains {$needle}", fileContains($files['page_controller'], $needle), "{$needle} should exist in PageController.");
}

foreach ([
    'tenant.contact-messages.index',
    '/manage/contact-messages',
    '/manage/website/pages/{themePage}/meta',
    "->delete('/manage/website/pages/{themePage}'",
] as $needle) {
    addFinding("web routes contain {$needle}", fileContains($files['web_routes'], $needle), "{$needle} should exist in web routes.");
}

foreach ([
    "Route::post('/contact'",
    'contact.store',
] as $needle) {
    addFinding("tenant routes contain {$needle}", fileContains($files['tenant_routes'], $needle), "{$needle} should exist in tenant routes.");
}

foreach ([
    'updatePageMeta',
    'destroyPage',
    'can_delete',
    'live_url',
    'linkOptionsForTheme',
] as $needle) {
    addFinding("WebsiteBuilderController contains {$needle}", fileContains($files['website_controller'], $needle), "{$needle} should exist in WebsiteBuilderController.");
}

foreach ([
    'data-s97-contact-form-working-backend',
    'data-contact-message-form',
    'submitContactForm',
    "form.post('/contact'",
] as $needle) {
    addFinding("Contact.vue contains {$needle}", fileContains($files['contact_vue'], $needle), "{$needle} should exist in Contact.vue.");
}

foreach ([
    'data-s98-contact-messages-admin',
    'data-contact-message-card',
    'data-contact-message-mark-read',
    'data-contact-message-delete',
] as $needle) {
    addFinding("Contact messages admin contains {$needle}", fileContains($files['messages_vue'], $needle), "{$needle} should exist in contact messages admin.");
}

foreach ([
    'data-s95-s98-page-contact-manager-foundation',
    'data-s96-navigation-manager-foundation',
    'data-navigation-link-option',
    'data-delete-builder-page',
    'data-contact-messages-admin-link',
] as $needle) {
    addFinding("Website Index contains {$needle}", fileContains($files['website_index'], $needle), "{$needle} should exist in Website Index.");
}

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
