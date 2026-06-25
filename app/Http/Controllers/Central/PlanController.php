<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PlanController extends Controller
{
    public function index()
    {
        return Inertia::render('central/plans/Index', [
            'plans' => Plan::orderBy('monthly_price')->get(),
            'paymentSetting' => $this->paymentSettingPayload(),
        ]);
    }

    public function create()
    {
        return Inertia::render('central/plans/Create', [
            'paymentSetting' => $this->paymentSettingPayload(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);

        $validated['slug'] = Str::slug($validated['slug'] ?: $validated['name']);
        $validated['commission_rate'] = $validated['transaction_fee_percent'];

        Plan::create($validated);

        return redirect()
            ->route('central.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return Inertia::render('central/plans/Edit', [
            'plan' => $plan,
            'paymentSetting' => $this->paymentSettingPayload(),
        ]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $this->validatePlan($request, $plan->id);

        $validated['slug'] = Str::slug($validated['slug'] ?: $validated['name']);
        $validated['commission_rate'] = $validated['transaction_fee_percent'];

        $plan->update($validated);

        return redirect()
            ->route('central.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    private function paymentSettingPayload(): array
    {
        $setting = PaymentSetting::active() ?? PaymentSetting::latest()->first();

        return [
            'provider' => $setting?->provider ?? 'wipay',
            'currency' => $setting?->currency ?? 'TTD',
            'processor_fee_percent' => (float) ($setting?->processor_fee_percent ?? 0),
            'processor_fee_fixed' => (float) ($setting?->processor_fee_fixed ?? 0),
        ];
    }

    private function validatePlan(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'unique:plans,slug';

        if ($ignoreId) {
            $slugRule .= ',' . $ignoreId;
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $slugRule],
            'description' => ['nullable', 'string'],

            'monthly_price' => ['required', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'transaction_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'transaction_fee_fixed' => ['required', 'numeric', 'min:0'],

            'max_products' => ['nullable', 'integer', 'min:0'],
            'custom_domain_enabled' => ['boolean'],
            'api_payment_enabled' => ['boolean'],
            'allow_multiple_builder_pages' => ['boolean'],
            'is_active' => ['boolean'],
        ]);
    }
}
