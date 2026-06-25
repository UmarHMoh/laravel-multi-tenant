<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

$results = [];

function add_tenant_login_identity_result(string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

$authControllerPath = app_path('Http/Controllers/Auth/AuthenticatedSessionController.php');
$authController = file_get_contents($authControllerPath);

$badFragments = [
    'You do not have access to this store',
    "tenant('email')",
    'tenant()->email',
    'store_email',
];

foreach ($badFragments as $fragment) {
    $found = str_contains($authController, $fragment);

    add_tenant_login_identity_result(
        "auth controller does not restrict login by {$fragment}",
        $found ? 'FAIL' : 'PASS',
        $found
            ? "AuthenticatedSessionController still contains {$fragment}."
            : "AuthenticatedSessionController does not contain {$fragment}."
    );
}

foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);

    try {
        Auth::guard('web')->logout();

        $loginEmail = 'profile-login-audit@example.com';
        $storeEmail = $tenant->store_email ?: ($tenant->email ?: 'store-contact-audit@example.com');

        $user = User::updateOrCreate(
            ['email' => $loginEmail],
            [
                'name' => 'Profile Login Audit',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        $loginWorks = Auth::guard('web')->attempt([
            'email' => $loginEmail,
            'password' => 'password123',
        ]);

        add_tenant_login_identity_result(
            "{$tenant->id}: profile login email authenticates",
            $loginWorks ? 'PASS' : 'FAIL',
            $loginWorks
                ? 'Tenant user profile email/password authenticates successfully.'
                : 'Tenant user profile email/password did not authenticate.',
            [
                'tenant_id' => $tenant->id,
                'tenant_db' => DB::connection()->getDatabaseName(),
                'login_email' => $loginEmail,
                'store_email' => $storeEmail,
                'same_as_store_email' => $loginEmail === $storeEmail,
            ]
        );

        $storeEmailUserRequired = $loginEmail === $storeEmail;

        add_tenant_login_identity_result(
            "{$tenant->id}: store email is separate from login identity",
            ! $storeEmailUserRequired ? 'PASS' : 'WARN',
            ! $storeEmailUserRequired
                ? 'Store contact email is not required to be the login email.'
                : 'Store email and login email are currently the same value; this is allowed but not required.',
            [
                'tenant_id' => $tenant->id,
                'login_email' => $loginEmail,
                'store_email' => $storeEmail,
            ]
        );

        Auth::guard('web')->logout();
    } catch (Throwable $e) {
        add_tenant_login_identity_result(
            "{$tenant->id}: tenant login identity exception",
            'FAIL',
            $e->getMessage(),
            [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]
        );
    } finally {
        try { tenancy()->end(); } catch (Throwable $ignored) {}
    }
}

$summary = [
    'PASS' => count(array_filter($results, fn ($result) => $result['status'] === 'PASS')),
    'FAIL' => count(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'WARN' => count(array_filter($results, fn ($result) => $result['status'] === 'WARN')),
    'INFO' => 0,
];

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'failures' => array_values(array_filter($results, fn ($result) => $result['status'] === 'FAIL')),
    'warnings' => array_values(array_filter($results, fn ($result) => $result['status'] === 'WARN')),
    'all_findings' => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
