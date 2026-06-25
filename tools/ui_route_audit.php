<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$results = [];

function result_item(string $section, string $status, string $message, array $data = []): array
{
    return [
        'section' => $section,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

function normalize_uri(string $uri): string
{
    $uri = trim($uri);

    if ($uri === '') {
        return '/';
    }

    $uri = preg_replace('/\?.*$/', '', $uri);
    $uri = preg_replace('/#.*$/', '', $uri);

    if (! str_starts_with($uri, '/')) {
        $uri = '/' . $uri;
    }

    return rtrim($uri, '/') ?: '/';
}

function route_uri_to_pattern(string $uri): string
{
    $uri = normalize_uri($uri);

    $escaped = preg_quote($uri, '#');

    $escaped = preg_replace('#\\\\\{[^/]+\\\\\}#', '[^/]+', $escaped);

    return '#^' . $escaped . '$#';
}

function uri_matches_route(string $uiPath, array $routes): array
{
    $uiPath = normalize_uri($uiPath);

    $matches = [];

    foreach ($routes as $route) {
        foreach ($route['methods'] as $method) {
            $pattern = route_uri_to_pattern($route['uri']);

            if (preg_match($pattern, $uiPath)) {
                $matches[] = [
                    'method' => $method,
                    'uri' => $route['uri'],
                    'name' => $route['name'],
                    'action' => $route['action'],
                ];
            }
        }
    }

    return $matches;
}

function extract_static_strings(string $text, string $regex): array
{
    preg_match_all($regex, $text, $matches, PREG_SET_ORDER);

    $values = [];

    foreach ($matches as $match) {
        $values[] = $match[1] ?? null;
    }

    return array_values(array_filter(array_unique($values)));
}

function is_external_or_asset(string $value): bool
{
    return str_starts_with($value, 'http://')
        || str_starts_with($value, 'https://')
        || str_starts_with($value, 'mailto:')
        || str_starts_with($value, 'tel:')
        || str_starts_with($value, '#')
        || str_starts_with($value, 'javascript:')
        || str_starts_with($value, '/storage/')
        || str_starts_with($value, '/images/')
        || str_starts_with($value, '/assets/')
        || str_starts_with($value, 'data:');
}

$routes = [];

foreach (Route::getRoutes() as $route) {
    $methods = array_values(array_filter($route->methods(), fn ($method) => $method !== 'HEAD'));

    $routes[] = [
        'methods' => $methods,
        'uri' => normalize_uri($route->uri()),
        'name' => $route->getName(),
        'action' => $route->getActionName(),
    ];
}

$routeNames = collect($routes)
    ->pluck('name')
    ->filter()
    ->values()
    ->all();

$routeUris = collect($routes)
    ->pluck('uri')
    ->unique()
    ->values()
    ->all();

$vueFiles = collect([
    ...glob(base_path('resources/js/**/*.vue'), GLOB_BRACE),
    ...glob(base_path('resources/js/**/*.ts'), GLOB_BRACE),
    ...glob(base_path('resources/js/**/*.js'), GLOB_BRACE),
])->unique()->values();

$uiFindings = [];

foreach ($vueFiles as $file) {
    $relative = str_replace(base_path() . '/', '', $file);
    $text = file_get_contents($file);

    $found = [];

    /*
    |--------------------------------------------------------------------------
    | Static hrefs
    |--------------------------------------------------------------------------
    */

    foreach (extract_static_strings($text, '/href\s*:\s*[\'"]([^\'"]+)[\'"]/') as $href) {
        $found[] = [
            'type' => 'object_href',
            'value' => $href,
        ];
    }

    foreach (extract_static_strings($text, '/\bhref=["\']([^"\']+)["\']/') as $href) {
        $found[] = [
            'type' => 'html_href',
            'value' => $href,
        ];
    }

    foreach (extract_static_strings($text, '/:href=["\']([^"\']+)["\']/') as $href) {
        $found[] = [
            'type' => 'bound_href_raw',
            'value' => $href,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Inertia/router/form actions
    |--------------------------------------------------------------------------
    */

    $actionRegexes = [
        'router_get' => '/router\.get\(\s*[\'"]([^\'"]+)[\'"]/',
        'router_post' => '/router\.post\(\s*[\'"]([^\'"]+)[\'"]/',
        'router_put' => '/router\.put\(\s*[\'"]([^\'"]+)[\'"]/',
        'router_patch' => '/router\.patch\(\s*[\'"]([^\'"]+)[\'"]/',
        'router_delete' => '/router\.delete\(\s*[\'"]([^\'"]+)[\'"]/',

        'form_get' => '/\w+\.get\(\s*[\'"]([^\'"]+)[\'"]/',
        'form_post' => '/\w+\.post\(\s*[\'"]([^\'"]+)[\'"]/',
        'form_put' => '/\w+\.put\(\s*[\'"]([^\'"]+)[\'"]/',
        'form_patch' => '/\w+\.patch\(\s*[\'"]([^\'"]+)[\'"]/',
        'form_delete' => '/\w+\.delete\(\s*[\'"]([^\'"]+)[\'"]/',
    ];

    foreach ($actionRegexes as $type => $regex) {
        foreach (extract_static_strings($text, $regex) as $path) {
            $found[] = [
                'type' => $type,
                'value' => $path,
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Named route() calls
    |--------------------------------------------------------------------------
    */

    foreach (extract_static_strings($text, '/route\(\s*[\'"]([^\'"]+)[\'"]/') as $name) {
        $exists = in_array($name, $routeNames, true);

        $uiFindings[] = [
            'file' => $relative,
            'type' => 'named_route',
            'value' => $name,
            'status' => $exists ? 'PASS' : 'FAIL',
            'message' => $exists ? 'Named route exists.' : 'Named route is missing.',
            'matches' => $exists
                ? collect($routes)->where('name', $name)->values()->all()
                : [],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Check static path links
    |--------------------------------------------------------------------------
    */

    foreach ($found as $item) {
        $value = $item['value'];

        if (! is_string($value) || $value === '') {
            continue;
        }

        if (str_contains($value, '+') || str_contains($value, '${') || str_contains($value, 'route(')) {
            $uiFindings[] = [
                'file' => $relative,
                'type' => $item['type'],
                'value' => $value,
                'status' => 'INFO',
                'message' => 'Dynamic expression found. Manual review needed.',
                'matches' => [],
            ];

            continue;
        }

        if (is_external_or_asset($value)) {
            $uiFindings[] = [
                'file' => $relative,
                'type' => $item['type'],
                'value' => $value,
                'status' => 'INFO',
                'message' => 'External or asset link ignored.',
                'matches' => [],
            ];

            continue;
        }

        if (! str_starts_with($value, '/')) {
            continue;
        }

        $matches = uri_matches_route($value, $routes);

        $uiFindings[] = [
            'file' => $relative,
            'type' => $item['type'],
            'value' => $value,
            'status' => count($matches) > 0 ? 'PASS' : 'FAIL',
            'message' => count($matches) > 0 ? 'Static UI path matches Laravel route.' : 'Static UI path does not match any Laravel route.',
            'matches' => $matches,
        ];
    }
}

/*
|--------------------------------------------------------------------------
| Sidebar-specific audit
|--------------------------------------------------------------------------
*/

$sidebarFiles = [
    'resources/js/components/AppSidebar.vue',
    'resources/js/layouts/CentralLayout.vue',
];

foreach ($sidebarFiles as $sidebarFile) {
    $path = base_path($sidebarFile);

    if (! file_exists($path)) {
        $uiFindings[] = [
            'file' => $sidebarFile,
            'type' => 'sidebar_file',
            'value' => $sidebarFile,
            'status' => 'FAIL',
            'message' => 'Sidebar file missing.',
            'matches' => [],
        ];
        continue;
    }

    $text = file_get_contents($path);

    $mustHave = [];

    if ($sidebarFile === 'resources/js/components/AppSidebar.vue') {
        $mustHave = [
            '/dashboard',
            '/manage/product',
            '/manage/category',
            '/manage/order',
            '/manage/customer',
            '/manage/billing',
            '/manage/store-settings',
            '/manage/domains',
            '/manage/payout-account',
        ];
    }

    if ($sidebarFile === 'resources/js/layouts/CentralLayout.vue') {
        $mustHave = [
            '/central',
            '/central/tenants',
            '/central/plans',
            '/central/transactions',
            '/central/payouts',
            '/central/payment-settings',
            '/central/settings',
        ];
    }

    foreach ($mustHave as $link) {
        $uiFindings[] = [
            'file' => $sidebarFile,
            'type' => 'sidebar_required_link',
            'value' => $link,
            'status' => str_contains($text, $link) ? 'PASS' : 'FAIL',
            'message' => str_contains($text, $link) ? 'Required sidebar link exists.' : 'Required sidebar link missing.',
            'matches' => uri_matches_route($link, $routes),
        ];
    }

    if (str_contains($text, "ReceiptText,") && str_contains($text, "icon: CreditCard,\n    ReceiptText")) {
        $uiFindings[] = [
            'file' => $sidebarFile,
            'type' => 'syntax_risk',
            'value' => 'ReceiptText inside nav object',
            'status' => 'FAIL',
            'message' => 'ReceiptText appears incorrectly inside a nav object.',
            'matches' => [],
        ];
    }
}

/*
|--------------------------------------------------------------------------
| Summaries
|--------------------------------------------------------------------------
*/

$summary = [
    'PASS' => 0,
    'FAIL' => 0,
    'WARN' => 0,
    'INFO' => 0,
];

foreach ($uiFindings as $finding) {
    $status = $finding['status'];
    $summary[$status] = ($summary[$status] ?? 0) + 1;
}

$failed = array_values(array_filter($uiFindings, fn ($item) => $item['status'] === 'FAIL'));
$warned = array_values(array_filter($uiFindings, fn ($item) => $item['status'] === 'WARN'));

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'route_count' => count($routes),
    'vue_file_count' => $vueFiles->count(),
    'failures' => $failed,
    'warnings' => $warned,
    'all_findings' => $uiFindings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
