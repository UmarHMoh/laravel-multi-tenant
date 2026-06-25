<?php

namespace App\Console\Commands;

use App\Models\PlatformSetting;
use App\Models\TenantSubscription;
use Illuminate\Console\Command;

class DeactivateOverdueTenants extends Command
{
    protected $signature = 'subscriptions:deactivate-overdue {--dry-run : Show what would happen without changing anything}';

    protected $description = 'Deactivate tenants whose subscription renewal date has passed without payment.';

    public function handle(): int
    {
        $settings = PlatformSetting::current();

        if (! $settings->auto_deactivate_overdue_tenants) {
            $this->info('Auto-deactivation is disabled in Platform Settings.');
            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $cutoffDate = now()->subDays((int) $settings->renewal_grace_days)->toDateString();

        $overdueSubscriptions = TenantSubscription::with(['tenant', 'plan'])
            ->where('status', 'active')
            ->whereNotNull('renews_at')
            ->whereDate('renews_at', '<', $cutoffDate)
            ->get();

        if ($overdueSubscriptions->isEmpty()) {
            $this->info('No overdue tenants found.');
            return self::SUCCESS;
        }

        foreach ($overdueSubscriptions as $subscription) {
            $tenant = $subscription->tenant;

            if (! $tenant) {
                continue;
            }

            $this->line("Overdue: {$tenant->name} ({$tenant->id}) - renewal date: {$subscription->renews_at}");

            if (! $dryRun) {
                $tenant->update([
                    'is_active' => false,
                ]);

                $subscription->update([
                    'status' => 'past_due',
                ]);
            }
        }

        $this->info($dryRun ? 'Dry run complete.' : 'Overdue tenants deactivated.');

        return self::SUCCESS;
    }
}
