<?php

namespace App\Services\Plans;

use App\Models\Plan;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlanFeatureGate
{
    public function currentTenantId(?string $tenantId = null): ?string
    {
        if ($tenantId) {
            return $tenantId;
        }

        try {
            return tenancy()->tenant?->id ? (string) tenancy()->tenant->id : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function currentPlan(?string $tenantId = null): ?Plan
    {
        $tenantId = $this->currentTenantId($tenantId);

        if (! $tenantId) {
            return null;
        }

        $query = TenantSubscription::query()
            ->where('tenant_id', $tenantId);

        $columns = Schema::connection(config('tenancy.database.central_connection'))->getColumnListing('tenant_subscriptions');

        $hasIsActive = in_array('is_active', $columns, true);
        $hasStatus = in_array('status', $columns, true);

        if ($hasIsActive || $hasStatus) {
            $query->where(function ($innerQuery) use ($hasIsActive, $hasStatus) {
                if ($hasIsActive) {
                    $innerQuery->where('is_active', true);
                }

                if ($hasStatus) {
                    $method = $hasIsActive ? 'orWhere' : 'where';
                    $innerQuery->{$method}('status', 'active');
                }
            });
        }

        if (in_array('ends_at', $columns, true)) {
            $query->where(function ($innerQuery) {
                $innerQuery->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
        }

        if (in_array('renews_at', $columns, true)) {
            $query->orderByDesc('renews_at');
        }

        $subscription = $query->latest()->first();

        if (! $subscription?->plan_id) {
            return null;
        }

        return Plan::find($subscription->plan_id);
    }

    public function maxProducts(?string $tenantId = null): ?int
    {
        $plan = $this->currentPlan($tenantId);

        if (! $plan) {
            return null;
        }

        $value = $plan->max_products;

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    public function productCount(): int
    {
        if (! Schema::hasTable('products')) {
            return 0;
        }

        return (int) DB::table('products')->count();
    }

    public function productLimitReached(?string $tenantId = null): bool
    {
        $maxProducts = $this->maxProducts($tenantId);

        if ($maxProducts === null) {
            return false;
        }

        return $this->productCount() >= $maxProducts;
    }

    public function productLimitMessage(?string $tenantId = null): string
    {
        $maxProducts = $this->maxProducts($tenantId);

        return $maxProducts
            ? "Your current plan allows up to {$maxProducts} products. Upgrade your plan to add more products."
            : 'Your current plan does not allow more products. Upgrade your plan to continue.';
    }

    public function allowsApiPayments(?string $tenantId = null): bool
    {
        $plan = $this->currentPlan($tenantId);

        if (! $plan) {
            return true;
        }

        return (bool) $plan->api_payment_enabled;
    }

    public function apiPaymentMessage(): string
    {
        return 'Online/API payments are not available on your current plan. Upgrade your plan to enable online payment checkout.';
    }

    public function allowsCustomDomains(?string $tenantId = null): bool
    {
        $plan = $this->currentPlan($tenantId);

        if (! $plan) {
            return true;
        }

        return (bool) $plan->custom_domain_enabled;
    }

    public function customDomainMessage(): string
    {
        return 'Custom domains are not available on this tenant’s current plan. Upgrade the plan before approving a custom domain.';
    }


    public function allowsMultipleBuilderPages(?string $tenantId = null): bool
    {
        $plan = $this->currentPlan($tenantId);

        if (! $plan) {
            return true;
        }

        return (bool) $plan->allow_multiple_builder_pages;
    }

    public function maxBuilderPages(?string $tenantId = null): int
    {
        return $this->allowsMultipleBuilderPages($tenantId) ? 999999 : 1;
    }

    public function builderPageLimitMessage(?string $tenantId = null): string
    {
        return 'Your current plan allows only one website builder page. Upgrade the plan to create additional pages.';
    }

    public function featurePayload(?string $tenantId = null): array
    {
        $plan = $this->currentPlan($tenantId);

        return [
            'plan_name' => $plan?->name,
            'max_products' => $this->maxProducts($tenantId),
            'product_count' => $this->productCount(),
            'product_limit_reached' => $this->productLimitReached($tenantId),
            'api_payment_enabled' => $this->allowsApiPayments($tenantId),
            'custom_domain_enabled' => $this->allowsCustomDomains($tenantId),
            'allow_multiple_builder_pages' => $this->allowsMultipleBuilderPages($tenantId),
            'max_builder_pages' => $this->maxBuilderPages($tenantId),
        ];
    }
}
