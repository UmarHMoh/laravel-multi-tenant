<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    AlertTriangle,
    CheckCircle,
    CreditCard,
    History,
    ShieldCheck,
} from 'lucide-vue-next';

const props = defineProps({
    tenant: Object,
    subscription: Object,
    payments: Array,
    latestPayment: Object,
    paymentProcessor: Object,
    payoutBalance: Object,
});

const processing = ref(false);

const plan = computed(() => props.subscription?.plan || null);

const processorFeePercent = computed(() => Number(props.transactionFee?.percent ?? props.paymentProcessor?.transaction_fee_percent ?? 0));
const processorFeeFixed = computed(() => Number(props.transactionFee?.fixed ?? props.paymentProcessor?.transaction_fee_fixed ?? 0));
const platformFeePercent = computed(() => 0);
const platformFeeFixed = computed(() => 0);

const totalTenantFeePercent = computed(() => processorFeePercent.value + platformFeePercent.value);
const totalTenantFeeFixed = computed(() => processorFeeFixed.value + platformFeeFixed.value);

function formatPercent(value) {
    return Number(value || 0).toFixed(2) + '%';
}


function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
}

function formatDateTime(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString();
}

function formatMoney(value, currency = 'TTD') {
    return currency + ' ' + Number(value || 0).toFixed(2);
}

function daysUntil(date) {
    if (!date) return null;

    const today = new Date();
    const target = new Date(date);

    today.setHours(0, 0, 0, 0);
    target.setHours(0, 0, 0, 0);

    return Math.ceil((target - today) / (1000 * 60 * 60 * 24));
}

const renewalDays = computed(() => daysUntil(props.subscription?.renews_at));

const billingHealth = computed(() => {
    if (!props.subscription) {
        return {
            label: 'No Subscription',
            message: 'No subscription is assigned to this store yet.',
            classes: 'bg-yellow-100 text-yellow-800',
            icon: AlertTriangle,
        };
    }

    if (!props.tenant?.is_active) {
        return {
            label: 'Inactive',
            message: 'Your storefront is currently inactive.',
            classes: 'bg-red-100 text-red-800',
            icon: AlertTriangle,
        };
    }

    if (renewalDays.value !== null && renewalDays.value < 0) {
        return {
            label: 'Past Due',
            message: `Your subscription is overdue by ${Math.abs(renewalDays.value)} day(s).`,
            classes: 'bg-red-100 text-red-800',
            icon: AlertTriangle,
        };
    }

    if (renewalDays.value !== null && renewalDays.value <= 7) {
        return {
            label: 'Renewal Soon',
            message: `Your subscription renews in ${renewalDays.value} day(s).`,
            classes: 'bg-yellow-100 text-yellow-800',
            icon: AlertTriangle,
        };
    }

    return {
        label: 'Active',
        message: `Your subscription renews in ${renewalDays.value ?? '—'} day(s).`,
        classes: 'bg-green-100 text-green-800',
        icon: CheckCircle,
    };
});

function sourceLabel(source) {
    const labels = {
        manual_admin: 'Manual Admin Payment',
        tenant_portal_test: 'Tenant Test Payment',
        tenant_portal_online_payment: 'Tenant Portal Payment',
        online_payment: 'Online Payment',
    };

    return labels[source] || source || '—';
}

function methodLabel(method) {
    const labels = {
        manual: 'Manual',
        tenant_test_payment: 'Test Payment',
        bank_transfer: 'Bank Transfer',
        cash: 'Cash',
        card: 'Card',
        online_payment: 'Online Payment',
    };

    return labels[method] || method || '—';
}

function payTestSubscription() {
    if (!confirm('Record a test subscription payment and extend this tenant subscription by one month?')) {
        return;
    }

    processing.value = true;

    router.post('/manage/billing/test-payment', {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <Head title="Billing" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 px-6 py-8 text-slate-900">
            <div class="mx-auto max-w-6xl space-y-6">
                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col justify-between gap-5 md:flex-row md:items-center">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="text-2xl font-bold">Billing</h1>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-medium"
                                    :class="billingHealth.classes"
                                >
                                    {{ billingHealth.label }}
                                </span>

                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                    Test Mode
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-slate-500">
                                View your subscription plan, renewal date, and payment history.
                            </p>
                        </div>

                        <button
                            v-if="subscription && plan"
                            @click="payTestSubscription"
                            :disabled="processing"
                            class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"
                        >
                            {{ processing ? 'Processing...' : 'Pay Subscription - Test Mode' }}
                        </button>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-500">Status</p>
                            <component :is="billingHealth.icon" class="h-5 w-5 text-slate-400" />
                        </div>

                        <p class="mt-3 text-xl font-bold">{{ billingHealth.label }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ billingHealth.message }}</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-500">Current Plan</p>
                            <ShieldCheck class="h-5 w-5 text-slate-400" />
                        </div>

                        <p class="mt-3 text-xl font-bold">{{ plan?.name || 'No Plan' }}</p>
                        <p class="mt-2 text-sm text-slate-500">
                            {{ plan ? formatMoney(plan.monthly_price, plan.currency || 'TTD') + ' / month' : 'No monthly fee available' }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-500">Renewal Date</p>
                            <History class="h-5 w-5 text-slate-400" />
                        </div>

                        <p class="mt-3 text-xl font-bold">{{ formatDate(subscription?.renews_at) }}</p>
                        <p class="mt-2 text-sm text-slate-500">
                            {{ renewalDays === null ? 'No renewal date' : renewalDays >= 0 ? renewalDays + ' day(s) remaining' : Math.abs(renewalDays) + ' day(s) overdue' }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-500">Last Payment</p>
                            <CreditCard class="h-5 w-5 text-slate-400" />
                        </div>

                        <p class="mt-3 text-xl font-bold">
                            {{ latestPayment ? formatMoney(latestPayment.amount, latestPayment.currency) : '—' }}
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ latestPayment ? formatDate(latestPayment.paid_at) : 'No payment yet' }}
                        </p>
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                        <h2 class="text-lg font-semibold">Subscription Details</h2>

                        <dl class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <dt class="text-sm text-slate-500">Store</dt>
                                <dd class="mt-1 font-medium">{{ tenant?.name || '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Email</dt>
                                <dd class="mt-1 font-medium">{{ tenant?.email || '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Subscription Status</dt>
                                <dd class="mt-1 font-medium capitalize">{{ subscription?.status || '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Plan</dt>
                                <dd class="mt-1 font-medium">{{ plan?.name || '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Processing Fee</dt>
                                <dd class="mt-1 font-medium">
                                    {{ formatPercent(processorFeePercent) }} + {{ formatMoney(processorFeeFixed, paymentProcessor?.currency || 'TTD') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Transaction Fee</dt>
                                <dd class="mt-1 font-medium">
                                    {{ formatPercent(platformFeePercent) }} + {{ formatMoney(platformFeeFixed, paymentProcessor?.currency || 'TTD') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Processing Fee</dt>
                                <dd class="mt-1 font-medium">
                                    {{ formatPercent(totalTenantFeePercent) }} + {{ formatMoney(totalTenantFeeFixed, paymentProcessor?.currency || 'TTD') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Started At</dt>
                                <dd class="mt-1 font-medium">{{ formatDateTime(subscription?.starts_at) }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Renews At</dt>
                                <dd class="mt-1 font-medium">{{ formatDateTime(subscription?.renews_at) }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Trial Ends At</dt>
                                <dd class="mt-1 font-medium">{{ formatDateTime(subscription?.trial_ends_at) }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Ends At</dt>
                                <dd class="mt-1 font-medium">{{ formatDateTime(subscription?.ends_at) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-xl border border-blue-200 bg-blue-50 p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-blue-900">Payment</h2>

                        <p class="mt-3 text-sm leading-6 text-blue-800">
                            This page is currently using test subscription payments. Clicking the payment button records a successful test payment and extends the renewal date by one month.
                        </p>

                        <p class="mt-4 text-sm leading-6 text-blue-800">
                            Payment method:
                            <strong>{{ paymentProcessor?.provider || 'Not configured' }}</strong>.
                            Processing fee:
                            <strong>{{ formatPercent(processorFeePercent) }} + {{ formatMoney(processorFeeFixed, paymentProcessor?.currency || 'TTD') }}</strong>.
                        </p>
                    </div>
                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                        <div>
                            <h2 class="text-lg font-semibold">Payment History</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                All subscription payments recorded for this store.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Paid</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Plan</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Method</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Source</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Reference</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Renewal Moved</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Notes</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr v-for="payment in payments" :key="payment.id">
                                    <td class="px-4 py-3 text-sm">{{ formatDate(payment.paid_at) }}</td>

                                    <td class="px-4 py-3 text-sm font-medium">{{ payment.plan || '—' }}</td>

                                    <td class="px-4 py-3 text-sm font-semibold">
                                        {{ formatMoney(payment.amount, payment.currency) }}
                                    </td>

                                    <td class="px-4 py-3 text-sm">
                                        {{ methodLabel(payment.payment_method) }}
                                    </td>

                                    <td class="px-4 py-3 text-sm">
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                                            {{ sourceLabel(payment.source) }}
                                        </span>
                                    </td>

                                    <td class="max-w-[180px] break-all px-4 py-3 text-sm">
                                        {{ payment.reference || payment.provider_transaction_id || '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-sm">
                                        <div>{{ formatDate(payment.previous_renews_at) }}</div>
                                        <div class="text-xs text-slate-500">to</div>
                                        <div class="font-medium">{{ formatDate(payment.new_renews_at) }}</div>
                                    </td>

                                    <td class="max-w-[220px] px-4 py-3 text-sm text-slate-600">
                                        {{ payment.notes || '—' }}
                                    </td>
                                </tr>

                                <tr v-if="!payments || payments.length === 0">
                                    <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                        No subscription payments found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<!-- Phase 1 privacy: tenant billing only exposes public processing fee. data-tenant-public-transaction-fee -->
