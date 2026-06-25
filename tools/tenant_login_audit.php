<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();

$results = [];

foreach (\App\Models\Tenant::all() as $tenant) {
    try {
        tenancy()->initialize($tenant);

        $users = \App\Models\User::query()
            ->get(["id", "name", "email", "created_at"])
            ->map(fn ($user) => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "created_at" => $user->created_at?->toDateTimeString(),
            ])
            ->values();

        tenancy()->end();

        $results[] = [
            "tenant_id" => $tenant->id,
            "tenant_name" => $tenant->name,
            "status" => $users->count() > 0 ? "PASS" : "FAIL",
            "message" => $users->count() > 0 ? "Tenant has login users." : "Tenant has no login users.",
            "users" => $users,
        ];
    } catch (Throwable $e) {
        try {
            tenancy()->end();
        } catch (Throwable $ignored) {
        }

        $results[] = [
            "tenant_id" => $tenant->id,
            "tenant_name" => $tenant->name,
            "status" => "FAIL",
            "message" => $e->getMessage(),
        ];
    }
}

echo json_encode([
    "generated_at" => now()->toDateTimeString(),
    "results" => $results,
], JSON_PRETTY_PRINT) . PHP_EOL;
