<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Central\AccountingExportController;
use App\Models\PlatformTransaction;
use App\Models\Tenant;
use App\Models\TenantPayoutBatch;
use App\Models\TenantPayoutLedgerEntry;
use App\Models\TenantPayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$results = [];

function add_accounting_export_result(string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

$expectedRoutes = [
    'central.accounting.exports.index',
    'central.accounting.exports.payout-requests',
    'central.accounting.exports.payout-batches',
    'central.accounting.exports.payout-ledger',
    'central.accounting.exports.platform-transactions',
];

foreach ($expectedRoutes as $routeName) {
    $route = Route::getRoutes()->getByName($routeName);

    add_accounting_export_result("route exists: {$routeName}", $route ? 'PASS' : 'FAIL', $route
        ? 'Accounting export route exists.'
        : 'Accounting export route is missing.',
        [
            'route_name' => $routeName,
            'uri' => $route?->uri(),
            'middleware' => $route?->gatherMiddleware(),
        ]
    );

    if ($route && $routeName !== 'central.accounting.exports.index') {
        $middleware = $route->gatherMiddleware();

        $protected = collect($middleware)->contains(fn ($item) => str_contains((string) $item, 'auth') || str_contains((string) $item, 'central'));

        add_accounting_export_result("route protected: {$routeName}", $protected ? 'PASS' : 'FAIL', $protected
            ? 'CSV export route has auth/central protection.'
            : 'CSV export route may not be protected.',
            [
                'route_name' => $routeName,
                'middleware' => $middleware,
            ]
        );
    }
}

$controller = app(AccountingExportController::class);

$exports = [
    'payout requests csv' => ['method' => 'payoutRequests', 'expected_header' => ['ID', 'Tenant ID', 'Tenant Name', 'Reference', 'Amount', 'Currency', 'Status']],
    'payout batches csv' => ['method' => 'payoutBatches', 'expected_header' => ['ID', 'Batch Number', 'Status', 'Request Count', 'Total Amount', 'Currency']],
    'payout ledger csv' => ['method' => 'payoutLedger', 'expected_header' => ['ID', 'Tenant ID', 'Platform Transaction ID', 'Entry Type', 'Source', 'Status']],
    'platform transactions csv' => ['method' => 'platformTransactions', 'expected_header' => ['ID', 'Tenant ID', 'Gross Amount', 'Processor Fee Amount', 'Platform Fee Amount', 'Tenant Payout Amount']],
];

foreach ($exports as $name => $config) {
    try {
        $request = Request::create('/central/accounting/exports/test', 'GET', []);
        $response = $controller->{$config['method']}($request);

        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();

        $firstLine = strtok($csv, "\n") ?: '';
        $parsedHeader = str_getcsv(trim($firstLine));
        $expectedHeader = $config['expected_header'];

        $passed = array_slice($parsedHeader, 0, count($expectedHeader)) === $expectedHeader;

        add_accounting_export_result($name, $passed ? 'PASS' : 'FAIL', $passed
            ? 'CSV export returned the expected header row.'
            : 'CSV export did not return expected header row.',
            [
                'first_line' => trim($firstLine),
                'parsed_header' => $parsedHeader,
                'expected_header' => $expectedHeader,
            ]
        );
    } catch (Throwable $e) {
        add_accounting_export_result($name, 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}

$summary = [
    'PASS' => count(array_filter($results, fn ($result) => $result['status'] === 'PASS')),
    'FAIL' => count(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'WARN' => 0,
    'INFO' => 0,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'failures' => array_values(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'warnings' => [],
    'all_findings' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
