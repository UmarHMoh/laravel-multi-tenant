<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$findings = [];

function s110_add(string $section, string $name, bool $passed, string $message, array $data = []): void
{
    global $findings;

    $findings[] = [
        'section' => $section,
        'name' => $name,
        'status' => $passed ? 'PASS' : 'FAIL',
        'message' => $message,
        'data' => $data,
    ];
}

function s110_file_contains(string $path, string $needle): bool
{
    return file_exists(base_path($path)) && str_contains(file_get_contents(base_path($path)), $needle);
}

function s110_routes(): array
{
    $routes = [];

    foreach (Route::getRoutes() as $route) {
        $routes[] = [
            'uri' => '/' . trim($route->uri(), '/'),
            'name' => $route->getName(),
            'methods' => array_values(array_filter($route->methods(), fn ($method) => $method !== 'HEAD')),
            'middleware' => $route->gatherMiddleware(),
            'action' => $route->getActionName(),
        ];
    }

    return $routes;
}

$routes = s110_routes();

$routeByName = collect($routes)->keyBy('name');

$requiredRoutes = [
    'tenant.website.index',
    'tenant.website.homepage.editor',
    'tenant.website.homepage.update',
    'tenant.website.homepage.publish',
    'tenant.website.media.index',
    'tenant.website.media.upload',
    'tenant.website.pages.store',
    'tenant.website.pages.update',
    'tenant.website.pages.publish',
    'tenant.website.products.editor',
    'tenant.asset',
    'contact.store',
    'cart.add',
    'cart.update',
    'orders.store',
    'orders.customer.index',
];

foreach ($requiredRoutes as $name) {
    s110_add(
        'required_routes',
        $name,
        $routeByName->has($name),
        $routeByName->has($name) ? 'Required route exists.' : 'Required route is missing.',
        $routeByName->get($name, [])
    );
}

$protectedTenantAdminRoutes = collect($routes)->filter(function ($route) {
    return str_contains($route['uri'], '/manage');
});

foreach ($protectedTenantAdminRoutes as $route) {
    $middleware = $route['middleware'];

    $protected = in_array('auth', $middleware, true);
    $tenantScoped = collect($middleware)->contains(fn ($m) =>
        str_contains((string) $m, 'InitializeTenancy')
        || str_contains((string) $m, 'PreventAccessFromCentralDomains')
    ) || str_starts_with((string) $route['action'], 'App\\Http\\Controllers\\Tenant\\');

    s110_add(
        'tenant_admin_protection',
        $route['name'] ?: $route['uri'],
        $protected && $tenantScoped,
        $protected && $tenantScoped
            ? 'Tenant admin route is auth-protected and tenant-scoped by middleware or tenant controller context.'
            : 'Tenant admin route is missing auth or tenant scope/controller context.',
        $route
    );
}

$mediaUpload = $routeByName->get('tenant.website.media.upload');
if ($mediaUpload) {
    $middleware = $mediaUpload['middleware'];

    s110_add(
        'media_security',
        'media upload route middleware',
        in_array('auth', $middleware, true)
            && collect($middleware)->contains(fn ($m) => str_contains((string) $m, 'InitializeTenancy'))
            && collect($middleware)->contains(fn ($m) => str_contains((string) $m, 'PreventAccessFromCentralDomains')),
        'Media upload route should be authenticated and tenant-scoped.',
        $mediaUpload
    );
}

$contactStore = $routeByName->get('contact.store');
if ($contactStore) {
    s110_add(
        'public_write_routes',
        'contact form throttled',
        collect($contactStore['middleware'])->contains(fn ($m) => str_starts_with((string) $m, 'throttle:')),
        'Public contact form POST should be throttled.',
        $contactStore
    );
}

s110_add(
    'asset_security',
    'tenant asset path sanitizer',
    s110_file_contains('app/Http/Controllers/Tenant/Manage/TenantAssetController.php', 'safeTenantAssetPath')
        && s110_file_contains('app/Http/Controllers/Tenant/Manage/TenantAssetController.php', "str_contains(\$path, '..')")
        && s110_file_contains('app/Http/Controllers/Tenant/Manage/TenantAssetController.php', "'X-Content-Type-Options' => 'nosniff'"),
    'Tenant asset controller should sanitize paths and send nosniff header.'
);

s110_add(
    'builder_security',
    'builder payload limits',
    s110_file_contains('app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php', 'MAX_BUILDER_SECTIONS')
        && s110_file_contains('app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php', 'MAX_BUILDER_BLOCKS')
        && s110_file_contains('app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php', 'sanitizeBuilderSettingValue')
        && s110_file_contains('app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php', 'ensureRequiredProductPageSections'),
    'Website builder should limit and sanitize payloads.'
);

s110_add(
    'builder_security',
    'media upload safe filename',
    s110_file_contains('app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php', "storeAs('tenant-website'")
        && s110_file_contains('app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php', 'isSafeWebsiteMediaPath')
        && s110_file_contains('app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php', 'Str::uuid()'),
    'Website media upload should use safe generated filenames and safe path validation.'
);

s110_add(
    'theme_architecture',
    'blank homepage support',
    s110_file_contains('app/Services/Themes/SectionRegistry.php', 'return [];')
        && s110_file_contains('resources/js/pages/tenant/Homepage.vue', 'data-storefront-builder-homepage'),
    'Theme architecture should support blank homepage and real storefront rendering.'
);

s110_add(
    'product_pages',
    'editable product pages',
    s110_file_contains('app/Services/Themes/ThemeBootstrapper.php', 'ensureProductPage')
        && s110_file_contains('resources/js/pages/tenant/ProductDetail.vue', 'data-product-page-sections')
        && s110_file_contains('resources/js/pages/tenant/website/Editor.vue', 'product_details'),
    'Product pages should be individually editable and rendered through theme sections.'
);

s110_add(
    'customer_experience',
    'cart checkout order polish',
    s110_file_contains('resources/js/pages/tenant/ShoppingCartList.vue', 'data-s102-cart-page-polish')
        && s110_file_contains('resources/js/pages/tenant/Payment.vue', 'data-s103-checkout-polish')
        && s110_file_contains('resources/js/pages/tenant/OrderConfirmation.vue', 'data-s104-order-confirmation-polish')
        && s110_file_contains('resources/js/pages/tenant/orders/CustomerIndex.vue', 'data-s105-customer-orders-polish'),
    'Customer cart, checkout, confirmation, and order history polish should be present.'
);

s110_add(
    'contact',
    'contact messages backend',
    s110_file_contains('app/Models/ContactMessage.php', 'class ContactMessage')
        && s110_file_contains('app/Http/Controllers/Tenant/Manage/ContactMessageController.php', 'class ContactMessageController')
        && s110_file_contains('resources/js/pages/tenant/contact-messages/Index.vue', 'data-s98-contact-messages-admin'),
    'Contact form backend and contact messages admin should be present.'
);

s110_add(
    'runtime',
    'local debug noted',
    app()->environment('local') && config('app.debug') === true,
    'Local environment may keep debug enabled. Production should set APP_ENV=production and APP_DEBUG=false.',
    [
        'env' => app()->environment(),
        'debug' => config('app.debug'),
    ]
);

$failures = array_values(array_filter($findings, fn ($finding) => $finding['status'] === 'FAIL'));

$summary = [
    'PASS' => count(array_filter($findings, fn ($finding) => $finding['status'] === 'PASS')),
    'FAIL' => count($failures),
    'WARN' => 0,
    'INFO' => 1,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'failures' => $failures,
    'warnings' => [],
    'all_findings' => $findings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($summary['FAIL'] > 0 ? 1 : 0);
