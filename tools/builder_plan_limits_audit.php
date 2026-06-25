<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\Plans\PlanFeatureGate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

$results = [];

function add_builder_plan_result(string $name, string $status, string $message, array $data = []): void
{
    global $results;

    $results[] = [
        'name' => $name,
        'status' => $status,
        'message' => $message,
        'data' => $data,
    ];
}

add_builder_plan_result(
    'plans allow_multiple_builder_pages column exists',
    Schema::connection(config('tenancy.database.central_connection'))->hasColumn('plans', 'allow_multiple_builder_pages') ? 'PASS' : 'FAIL',
    'plans.allow_multiple_builder_pages should exist.'
);

$plan = Plan::query()->first();

$planHasBuilderPageColumn = $plan
    ? array_key_exists('allow_multiple_builder_pages', $plan->getAttributes())
    : Schema::connection(config('tenancy.database.central_connection'))->hasColumn('plans', 'allow_multiple_builder_pages');

add_builder_plan_result(
    'plan model exposes allow_multiple_builder_pages',
    $planHasBuilderPageColumn ? 'PASS' : 'FAIL',
    $plan ? 'Plan model can read allow_multiple_builder_pages.' : 'No plans found, column existence checked instead.'
);

$gate = app(PlanFeatureGate::class);

add_builder_plan_result(
    'PlanFeatureGate has allowsMultipleBuilderPages',
    method_exists($gate, 'allowsMultipleBuilderPages') ? 'PASS' : 'FAIL',
    'PlanFeatureGate should expose allowsMultipleBuilderPages.'
);

add_builder_plan_result(
    'PlanFeatureGate has maxBuilderPages',
    method_exists($gate, 'maxBuilderPages') ? 'PASS' : 'FAIL',
    'PlanFeatureGate should expose maxBuilderPages.'
);

add_builder_plan_result(
    'PlanFeatureGate has builderPageLimitMessage',
    method_exists($gate, 'builderPageLimitMessage') ? 'PASS' : 'FAIL',
    'PlanFeatureGate should expose builderPageLimitMessage.'
);

$files = [
    'app/Http/Controllers/Central/PlanController.php' => [
        'allow_multiple_builder_pages',
    ],
    'app/Models/Plan.php' => [
        'allow_multiple_builder_pages',
    ],
    'app/Services/Plans/PlanFeatureGate.php' => [
        'allowsMultipleBuilderPages',
        'maxBuilderPages',
        'builderPageLimitMessage',
        'allow_multiple_builder_pages',
    ],
    'app/Http/Controllers/Tenant/Manage/WebsiteBuilderController.php' => [
        'builderLimits',
        'allowsMultipleBuilderPages',
        'maxBuilderPages',
    ],
    'resources/js/pages/central/plans/Create.vue' => [
        'allow_multiple_builder_pages',
        'Allow multiple website builder pages',
    ],
    'resources/js/pages/central/plans/Edit.vue' => [
        'allow_multiple_builder_pages',
        'Allow multiple website builder pages',
    ],
    'resources/js/pages/central/plans/Index.vue' => [
        'Multiple Pages',
        'allow_multiple_builder_pages',
    ],
    'resources/js/pages/tenant/website/Index.vue' => [
        'builderLimits',
        'Page limit',
        'builderLimits.max_builder_pages',
    ],
];

foreach ($files as $file => $needles) {
    $content = File::exists(base_path($file)) ? File::get(base_path($file)) : '';

    foreach ($needles as $needle) {
        add_builder_plan_result(
            "{$file} contains {$needle}",
            str_contains($content, $needle) ? 'PASS' : 'FAIL',
            str_contains($content, $needle) ? "{$needle} found in {$file}." : "{$needle} missing from {$file}."
        );
    }
}

foreach (Tenant::all() as $tenant) {
    $max = $gate->maxBuilderPages($tenant->id);

    add_builder_plan_result(
        "tenant {$tenant->id} builder page max resolves",
        is_int($max) && $max >= 1 ? 'PASS' : 'FAIL',
        "Builder page max resolves for {$tenant->id}.",
        ['max_builder_pages' => $max]
    );
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
