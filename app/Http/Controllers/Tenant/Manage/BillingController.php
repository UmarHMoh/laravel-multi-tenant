<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantSubscriptionPayment;
use App\Models\PaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function index()
    {
        $tenantId = tenant('id');

        $data = tenancy()->central(function () use ($tenantId) {
            $tenant = Tenant::with([
                'currentSubscription.plan',
                'subscriptionPayments.plan',
            ])->findOrFail($tenantId);

            $subscription = $tenant->currentSubscription;
            $payments = $tenant->subscriptionPayments()
                ->with('plan')
                ->latest()
                ->take(20)
                ->get();

            $paymentSetting = PaymentSetting::active() ?? PaymentSetting::latest()->first();

            return [
                'paymentProcessor' => [
                    'provider' => $paymentSetting?->provider ?? 'wipay',
                    'currency' => $paymentSetting?->currency ?? 'TTD',
                    'processor_fee_percent' => (float) ($paymentSetting?->processor_fee_percent ?? 0),
                    'processor_fee_fixed' => (float) ($paymentSetting?->processor_fee_fixed ?? 0),
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'is_active' => $tenant->is_active,
                ],
                'subscription' => $subscription ? [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'starts_at' => optional($subscription->starts_at)->toDateTimeString(),
                    'renews_at' => optional($subscription->renews_at)->toDateTimeString(),
                    'trial_ends_at' => optional($subscription->trial_ends_at)->toDateTimeString(),
                    'ends_at' => optional($subscription->ends_at)->toDateTimeString(),
                    'plan' => $subscription->plan ? [
                        'id' => $subscription->plan->id,
                        'name' => $subscription->plan->name,
                        'monthly_price' => $subscription->plan->monthly_price,
                        'currency' => $subscription->plan->currency ?? 'TTD',
                        'description' => $subscription->plan->description ?? '',
                        'platform_fee_percent' => (float) ($subscription->plan->transaction_fee_percent ?? $subscription->plan->commission_rate ?? 0),
                        'platform_fee_fixed' => (float) ($subscription->plan->transaction_fee_fixed ?? 0),
                    ] : null,
                ] : null,
                'payments' => $payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'plan' => $payment->plan?->name,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'source' => $payment->source,
                    'paid_at' => optional($payment->paid_at)->toDateTimeString(),
                    'previous_renews_at' => optional($payment->previous_renews_at)->toDateTimeString(),
                    'new_renews_at' => optional($payment->new_renews_at)->toDateTimeString(),
                    'payment_method' => $payment->payment_method,
                    'provider' => $payment->provider,
                    'provider_transaction_id' => $payment->provider_transaction_id,
                    'reference' => $payment->reference,
                    'notes' => $payment->notes,
                ])->values(),
                'latestPayment' => $payments->first() ? [
                    'id' => $payments->first()->id,
                    'amount' => $payments->first()->amount,
                    'currency' => $payments->first()->currency,
                    'status' => $payments->first()->status,
                    'source' => $payments->first()->source,
                    'paid_at' => optional($payments->first()->paid_at)->toDateTimeString(),
                    'new_renews_at' => optional($payments->first()->new_renews_at)->toDateTimeString(),
                    'payment_method' => $payments->first()->payment_method,
                    'reference' => $payments->first()->reference,
                    'notes' => $payments->first()->notes,
                ] : null,
            ];
        });

        return Inertia::render('tenant/billing/Index', $data);
    }

    public function testPayment(Request $request)
    {
        $tenantId = tenant('id');

        tenancy()->central(function () use ($tenantId) {
            DB::connection(config('tenancy.database.central_connection') ?: config('database.default'))
                ->transaction(function () use ($tenantId) {
                    $tenant = Tenant::with('currentSubscription.plan')->findOrFail($tenantId);
                    $subscription = $tenant->currentSubscription;

                    if (! $subscription || ! $subscription->plan) {
                        throw new \RuntimeException('This tenant does not have an active subscription plan.');
                    }

                    $previousRenewsAt = $subscription->renews_at;

                    $baseDate = $subscription->renews_at && $subscription->renews_at->isFuture()
                        ? $subscription->renews_at->copy()
                        : now();

                    $newRenewsAt = $baseDate->copy()->addMonth();

                    TenantSubscriptionPayment::create([
                        'tenant_id' => $tenant->id,
                        'tenant_subscription_id' => $subscription->id,
                        'plan_id' => $subscription->plan_id,

                        'amount' => $subscription->plan->monthly_price,
                        'currency' => $subscription->plan->currency ?? 'TTD',
                        'status' => 'paid',
                        'source' => 'tenant_portal_test',

                        'paid_at' => now(),
                        'previous_renews_at' => $previousRenewsAt,
                        'new_renews_at' => $newRenewsAt,

                        'payment_method' => 'tenant_test_payment',
                        'provider' => 'test',
                        'provider_transaction_id' => 'TENANT-TEST-' . now()->format('YmdHis'),

                        'payer_name' => $tenant->name,
                        'payer_email' => $tenant->email,

                        'recorded_by' => 'tenant_portal',
                        'reference' => 'TENANT-TEST-' . now()->format('YmdHis'),
                        'notes' => 'Tenant test subscription payment from billing page.',
                        'metadata' => [
                            'recorded_from' => 'tenant_billing_page',
                            'mode' => 'test',
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
                });
        });

        return redirect()
            ->route('manage.billing.index')
            ->with('success', 'Test subscription payment recorded successfully. Renewal date has been extended.');
    }
}
