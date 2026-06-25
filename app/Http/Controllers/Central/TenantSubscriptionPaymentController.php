<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscriptionPayment;
use Illuminate\Http\Request;

class TenantSubscriptionPaymentController extends Controller
{
    public function store(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'paid_at' => ['required', 'date'],
            'months' => ['required', 'integer', 'min:1', 'max:24'],

            'payment_method' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'payer_name' => ['nullable', 'string', 'max:255'],
            'payer_email' => ['nullable', 'email', 'max:255'],
            'card_brand' => ['nullable', 'string', 'max:255'],
            'card_last4' => ['nullable', 'string', 'size:4'],
            'notes' => ['nullable', 'string'],
        ]);

        $subscription = $tenant->currentSubscription()->with('plan')->first();

        if (! $subscription) {
            return back()->withErrors([
                'subscription' => 'This tenant does not have an active or past-due subscription to record payment against.',
            ]);
        }

        $previousRenewsAt = $subscription->renews_at;

        $baseDate = $subscription->renews_at && $subscription->renews_at->isFuture()
            ? $subscription->renews_at->copy()
            : now();

        $newRenewsAt = $baseDate->copy()->addMonths((int) $validated['months']);

        TenantSubscriptionPayment::create([
            'tenant_id' => $tenant->id,
            'tenant_subscription_id' => $subscription->id,
            'plan_id' => $subscription->plan_id,

            'amount' => $validated['amount'],
            'currency' => strtoupper($validated['currency']),
            'status' => 'paid',
            'source' => 'manual_admin',

            'paid_at' => $validated['paid_at'],
            'previous_renews_at' => $previousRenewsAt,
            'new_renews_at' => $newRenewsAt,

            'payment_method' => $validated['payment_method'] ?? 'manual',
            'provider' => 'manual',
            'provider_transaction_id' => $validated['reference'] ?? null,

            'payer_name' => $validated['payer_name'] ?? $tenant->name,
            'payer_email' => $validated['payer_email'] ?? $tenant->email,
            'card_brand' => $validated['card_brand'] ?? null,
            'card_last4' => $validated['card_last4'] ?? null,

            'recorded_by' => 'central_admin',
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'metadata' => [
                'recorded_from' => 'central_tenant_profile',
                'months_paid' => (int) $validated['months'],
                'old_renewal_date' => optional($previousRenewsAt)->toDateTimeString(),
                'new_renewal_date' => $newRenewsAt->toDateTimeString(),
            ],
        ]);

        $subscription->update([
            'status' => 'active',
            'renews_at' => $newRenewsAt,
            'ends_at' => null,
        ]);

        $tenant->update([
            'is_active' => true,
        ]);

        return back()->with('success', 'Monthly subscription payment recorded and renewal date updated.');
    }
}
