<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Stancl\Tenancy\Database\Models\Domain;

class DomainSettingsController extends Controller
{
    public function index()
    {
        $tenant = Tenant::with(['domains', 'currentSubscription.plan'])->findOrFail(tenant('id'));
        $data = $tenant->data ?? [];
        $plan = $tenant->currentSubscription?->plan;

        $customDomainAllowed = (bool) ($plan?->custom_domain_enabled ?? false);

        return Inertia::render('tenant/domains/Index', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'domains' => $tenant->domains,
                'custom_domain' => $data['custom_domain'] ?? null,
                'custom_domain_status' => $data['custom_domain_status'] ?? null,
                'custom_domain_connected_at' => $data['custom_domain_connected_at'] ?? null,
            ],
            'plan' => $plan ? [
                'id' => $plan->id,
                'name' => $plan->name,
                'custom_domain_enabled' => $plan->custom_domain_enabled,
            ] : null,
            'customDomainAllowed' => $customDomainAllowed,
            'dnsTargets' => [
                'cname_target' => env('STOREFRONT_CNAME_TARGET', 'stores.yourdomain.com'),
                'a_record_ip' => env('STOREFRONT_A_RECORD_IP', 'YOUR_SERVER_IP'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $tenant = Tenant::with('currentSubscription.plan')->findOrFail(tenant('id'));
        $plan = $tenant->currentSubscription?->plan;

        if (! $plan || ! $plan->custom_domain_enabled) {
            return back()->withErrors([
                'custom_domain' => 'Your current plan does not include custom domains. Please upgrade your plan to use this feature.',
            ]);
        }

        $validated = $request->validate([
            'custom_domain' => ['required', 'string', 'max:255'],
        ]);

        $customDomain = strtolower(trim($validated['custom_domain']));
        $customDomain = preg_replace('#^https?://#', '', $customDomain);
        $customDomain = rtrim($customDomain, '/');

        if (str_contains($customDomain, '/')) {
            return back()->withErrors([
                'custom_domain' => 'Enter only the domain name. Example: www.yourstore.com',
            ])->withInput();
        }

        if (! preg_match('/^(?!-)([a-z0-9-]+\.)+[a-z]{2,}$/', $customDomain)) {
            return back()->withErrors([
                'custom_domain' => 'Enter a valid domain. Example: www.yourstore.com',
            ])->withInput();
        }

        $existingDomain = Domain::where('domain', $customDomain)->first();

        if ($existingDomain && $existingDomain->tenant_id !== $tenant->id) {
            return back()->withErrors([
                'custom_domain' => 'This domain is already connected to another store.',
            ])->withInput();
        }

        Domain::updateOrCreate(
            ['domain' => $customDomain],
            ['tenant_id' => $tenant->id]
        );

        $data = $tenant->data ?? [];

        $tenant->update([
            'data' => array_merge($data, [
                'custom_domain' => $customDomain,
                'custom_domain_status' => 'connected_dns_required',
                'custom_domain_connected_at' => now()->toDateTimeString(),
            ]),
        ]);

        return redirect()
            ->route('manage.domains.index')
            ->with('success', 'Custom domain connected. Complete the DNS steps at your domain provider.');
    }
}
