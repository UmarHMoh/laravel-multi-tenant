<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Services\Plans\PlanFeatureGate;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Stancl\Tenancy\Database\Models\Domain;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with(['domains', 'currentSubscription.plan', 'payoutAccounts'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('central/tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load([
            'domains',
            'currentSubscription.plan',
            'subscriptions.plan',
            'payoutAccounts',
            'subscriptionPayments.plan',
            'latestSubscriptionPayment.plan',
        ]);

        $subscriptionPayments = $tenant->subscriptionPayments
            ->sortByDesc('paid_at')
            ->values();

        return Inertia::render('central/tenants/Show', [
            'tenant' => $tenant,
            'databaseName' => 'tenant_' . $tenant->id,
            'plans' => Plan::where('is_active', true)->orderBy('monthly_price')->get(),
            'storeSettings' => $this->tenantData($tenant),
            'subscriptionPayments' => $subscriptionPayments,
            'latestSubscriptionPayment' => $tenant->latestSubscriptionPayment,
        ]);
    }

    public function create()
    {
        return Inertia::render('central/tenants/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:tenants,id'],
            'store_name' => ['required', 'string', 'max:255'],
            'store_email' => ['required', 'email', 'max:255', 'unique:tenants,email'],
            'subdomain' => ['required', 'string', 'alpha_dash', 'max:50'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:6'],
        ]);

        $tenantId = Str::slug($validated['tenant_id']);
        $subdomain = Str::slug($validated['subdomain']);
        $domain = $subdomain . '.localhost';

        if (Domain::where('domain', $domain)->exists()) {
            return back()->withErrors([
                'subdomain' => 'This subdomain is already in use.',
            ])->withInput();
        }

        $tenant = Tenant::create([
            'id' => $tenantId,
            'name' => $validated['store_name'],
            'email' => $validated['store_email'],
            'is_active' => true,
            'data' => [
                'type' => 'custom',
                'description' => 'Tenant created from central dashboard',
                'primary_subdomain' => $subdomain,
                'default_domain' => $domain,
                'custom_domain' => null,
                'custom_domain_status' => null,
            ],
        ]);

        $tenant->domains()->create([
            'domain' => $domain,
        ]);

        tenancy()->initialize($tenant);

        Artisan::call('migrate', [
            '--path' => database_path('migrations/tenant'),
            '--force' => true,
        ]);

        User::create([
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'email_verified_at' => now(),
            'password' => Hash::make($validated['admin_password']),
            'role' => 'admin',
            'remember_token' => Str::random(10),
        ]);

        tenancy()->end();

        return redirect()
            ->route('central.tenants.show', $tenant->id)
            ->with('success', 'Tenant created successfully.');
    }

    public function assignPlan(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'renews_at' => ['nullable', 'date'],
            'trial_ends_at' => ['nullable', 'date'],
        ]);

        TenantSubscription::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->update([
                'status' => 'ended',
                'ends_at' => now(),
            ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $validated['plan_id'],
            'status' => 'active',
            'starts_at' => now(),
            'renews_at' => $validated['renews_at'] ?? now()->addMonth(),
            'trial_ends_at' => $validated['trial_ends_at'] ?? null,
        ]);

        return redirect()
            ->route('central.tenants.show', $tenant->id)
            ->with('success', 'Plan assigned successfully.');
    }

    public function activate(Tenant $tenant)

    {

        $tenant->update([

            'is_active' => true,

        ]);

        return redirect()

            ->route('central.tenants.show', $tenant->id)

            ->with('success', 'Tenant activated successfully.');

    }

    public function deactivate(Tenant $tenant)

    {

        $tenant->update([

            'is_active' => false,

        ]);

        return redirect()

            ->route('central.tenants.show', $tenant->id)

            ->with('success', 'Tenant deactivated successfully.');

    }



    public function approveCustomDomain(Tenant $tenant)

    {

        if (! app(PlanFeatureGate::class)->allowsCustomDomains((string) $tenant->id)) {
            return back()->with('error', app(PlanFeatureGate::class)->customDomainMessage());
        }



        $data = $tenant->data ?? [];

        $customDomain = $data['custom_domain'] ?? null;

        if (!$customDomain) {

            return back()->withErrors([

                'custom_domain' => 'This tenant has not requested a custom domain.',

            ]);

        }

        if (\Stancl\Tenancy\Database\Models\Domain::where('domain', $customDomain)

            ->where('tenant_id', '!=', $tenant->id)

            ->exists()) {

            return back()->withErrors([

                'custom_domain' => 'This domain is already connected to another tenant.',

            ]);

        }

        \Stancl\Tenancy\Database\Models\Domain::updateOrCreate(

            ['domain' => $customDomain],

            ['tenant_id' => $tenant->id]

        );

        $tenant->update([

            'data' => array_merge($data, [

                'custom_domain_status' => 'active',

                'custom_domain_approved_at' => now()->toDateTimeString(),

                'custom_domain_rejected_at' => null,

                'custom_domain_rejection_reason' => null,

            ]),

        ]);

        return redirect()

            ->route('central.tenants.show', $tenant->id)

            ->with('success', 'Custom domain approved successfully.');

    }

    public function rejectCustomDomain(\Illuminate\Http\Request $request, Tenant $tenant)

    {

        $validated = $request->validate([

            'reason' => ['nullable', 'string', 'max:1000'],

        ]);

        $data = $tenant->data ?? [];

        $tenant->update([

            'data' => array_merge($data, [

                'custom_domain_status' => 'rejected',

                'custom_domain_rejected_at' => now()->toDateTimeString(),

                'custom_domain_rejection_reason' => $validated['reason'] ?? null,

            ]),

        ]);

        return redirect()

            ->route('central.tenants.show', $tenant->id)

            ->with('success', 'Custom domain rejected.');

    }



    private function tenantData(Tenant $tenant): array
    {
        $raw = DB::connection(config('tenancy.database.central_connection') ?: config('database.default'))
            ->table('tenants')
            ->where('id', $tenant->id)
            ->value('data');

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

}
