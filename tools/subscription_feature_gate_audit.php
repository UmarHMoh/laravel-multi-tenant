<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Plans\PlanFeatureGate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$results = [];

function add_feature_gate_result(string $tenantId, string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'tenant_id' => $tenantId,
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

foreach (Tenant::all() as $tenant) {
    DB::connection('mysql')->beginTransaction();

    try {
        tenancy()->initialize($tenant);
        DB::beginTransaction();

        $productCount = Schema::hasTable('products') ? DB::table('products')->count() : 0;

        tenancy()->end();

        $plan = Plan::create([
            'name' => 'S26 Locked Audit Plan ' . strtoupper(substr(md5($tenant->id . microtime()), 0, 6)),
            'slug' => 's26-locked-' . strtolower(substr(md5($tenant->id . microtime()), 0, 8)),
            'description' => 'Temporary locked plan for feature gate audit.',
            'monthly_price' => 0,
            'commission_rate' => 0,
            'transaction_fee_percent' => 0,
            'transaction_fee_fixed' => 0,
            'max_products' => $productCount,
            'custom_domain_enabled' => false,
            'api_payment_enabled' => false,
            'is_active' => true,
        ]);

        $subscriptionColumns = Schema::connection('mysql')->getColumnListing('tenant_subscriptions');

        $subscriptionData = [
            'tenant_id' => (string) $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'renews_at' => now()->addMonth(),
            'trial_ends_at' => null,
            'ends_at' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $subscriptionData = array_intersect_key($subscriptionData, array_flip($subscriptionColumns));

        DB::connection('mysql')->table('tenant_subscriptions')
            ->where('tenant_id', (string) $tenant->id)
            ->update(array_intersect_key([
                'is_active' => false,
                'status' => 'inactive',
                'updated_at' => now(),
            ], array_flip($subscriptionColumns)));

        DB::connection('mysql')->table('tenant_subscriptions')->insert($subscriptionData);

        tenancy()->initialize($tenant);

        $gate = app(PlanFeatureGate::class);

        $productLimitPassed = $gate->productLimitReached((string) $tenant->id) === true;

        add_feature_gate_result($tenant->id, 'max product limit enforced', $productLimitPassed ? 'PASS' : 'FAIL', $productLimitPassed
            ? 'Tenant at max product count is blocked by the feature gate.'
            : 'Tenant at max product count was not blocked by the feature gate.',
            [
                'product_count' => $gate->productCount(),
                'max_products' => $gate->maxProducts((string) $tenant->id),
            ]
        );

        $apiPaymentPassed = $gate->allowsApiPayments((string) $tenant->id) === false;

        add_feature_gate_result($tenant->id, 'api payment feature locked', $apiPaymentPassed ? 'PASS' : 'FAIL', $apiPaymentPassed
            ? 'API payment feature is locked for the restricted plan.'
            : 'API payment feature was not locked for the restricted plan.',
            [
                'api_payment_enabled' => $gate->allowsApiPayments((string) $tenant->id),
            ]
        );

        $customDomainPassed = $gate->allowsCustomDomains((string) $tenant->id) === false;

        add_feature_gate_result($tenant->id, 'custom domain feature locked', $customDomainPassed ? 'PASS' : 'FAIL', $customDomainPassed
            ? 'Custom domain feature is locked for the restricted plan.'
            : 'Custom domain feature was not locked for the restricted plan.',
            [
                'custom_domain_enabled' => $gate->allowsCustomDomains((string) $tenant->id),
            ]
        );
    } catch (Throwable $e) {
        add_feature_gate_result($tenant->id, 'feature gate exception', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    } finally {
        try { DB::rollBack(); } catch (Throwable $ignored) {}
        try { DB::connection('mysql')->rollBack(); } catch (Throwable $ignored) {}
        try { tenancy()->end(); } catch (Throwable $ignored) {}
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
