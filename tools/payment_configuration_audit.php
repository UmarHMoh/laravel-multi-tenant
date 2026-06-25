<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$report = [];

function result(string $section, string $name, string $status, string $message, array $data = []): void
{
    global $report;

    $report[] = compact('section', 'name', 'status', 'message', 'data');
}

try {
    $activeCount = \App\Models\PaymentSetting::where('is_active', true)->count();
    $active = \App\Models\PaymentSetting::active();

    result(
        'payment_config',
        'only one active payment configuration',
        $activeCount <= 1 ? 'PASS' : 'FAIL',
        $activeCount <= 1
            ? 'There is no more than one active payment configuration.'
            : 'More than one payment configuration is active.',
        [
            'active_count' => $activeCount,
            'active_id' => $active?->id,
            'active_provider' => $active?->provider,
        ]
    );

    result(
        'payment_config',
        'active configuration has fee rules',
        $active && $active->provider && $active->currency && $active->processor_fee_percent !== null && $active->processor_fee_fixed !== null
            ? 'PASS'
            : 'FAIL',
        $active
            ? 'Active payment configuration has provider, currency, and fee rules.'
            : 'No active payment configuration found.',
        [
            'active_id' => $active?->id,
            'provider' => $active?->provider,
            'currency' => $active?->currency,
            'processor_fee_percent' => $active?->processor_fee_percent,
            'processor_fee_fixed' => $active?->processor_fee_fixed,
        ]
    );

    if ($active) {
        $oldActiveIds = \App\Models\PaymentSetting::where('is_active', true)->pluck('id')->all();

        $candidate = \App\Models\PaymentSetting::where('id', '!=', $active->id)->latest()->first();

        if (! $candidate) {
            $candidate = \App\Models\PaymentSetting::create([
                'name' => 'Audit Alternate Processor',
                'config_type' => 'custom',
                'provider' => 'audit_processor',
                'environment' => 'sandbox',
                'country_code' => 'TT',
                'currency' => 'TTD',
                'fee_structure' => 'merchant_absorb',
                'auth_type' => 'none',
                'processor_fee_percent' => 2.5,
                'processor_fee_fixed' => 0.50,
                'is_active' => false,
            ]);
        }

        \Illuminate\Support\Facades\DB::connection(config('tenancy.database.central_connection') ?: config('database.default'))
            ->transaction(function () use ($candidate, $active, $oldActiveIds) {
                $candidate->activate();

                $newActiveCount = \App\Models\PaymentSetting::where('is_active', true)->count();
                $newActive = \App\Models\PaymentSetting::active();

                result(
                    'payment_config',
                    'activating one configuration disables others',
                    $newActiveCount === 1 && $newActive?->id === $candidate->id
                        ? 'PASS'
                        : 'FAIL',
                    $newActiveCount === 1 && $newActive?->id === $candidate->id
                        ? 'Activating a payment configuration disables all others.'
                        : 'Activating a payment configuration did not enforce single-active rule.',
                    [
                        'new_active_count' => $newActiveCount,
                        'new_active_id' => $newActive?->id,
                        'candidate_id' => $candidate->id,
                    ]
                );

                throw new RuntimeException('ROLLBACK_PAYMENT_CONFIG_AUDIT');
            });
    }
} catch (RuntimeException $e) {
    if ($e->getMessage() !== 'ROLLBACK_PAYMENT_CONFIG_AUDIT') {
        result('payment_config', 'payment configuration audit runtime', 'FAIL', $e->getMessage(), [
            'exception' => get_class($e),
        ]);
    }
} catch (Throwable $e) {
    result('payment_config', 'payment configuration audit runtime', 'FAIL', $e->getMessage(), [
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}

$summary = [
    'PASS' => 0,
    'FAIL' => 0,
    'WARN' => 0,
    'INFO' => 0,
];

foreach ($report as $item) {
    $summary[$item['status']] = ($summary[$item['status']] ?? 0) + 1;
}

echo json_encode([
    'summary' => $summary,
    'generated_at' => now()->toDateTimeString(),
    'failures' => array_values(array_filter($report, fn ($item) => $item['status'] === 'FAIL')),
    'warnings' => array_values(array_filter($report, fn ($item) => $item['status'] === 'WARN')),
    'all_findings' => $report,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
