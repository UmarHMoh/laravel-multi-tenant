<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    AlertTriangle,
    CheckCircle,
    CreditCard,
    ExternalLink,
    History,
} from 'lucide-vue-next';

defineOptions({ layout: CentralLayout });

const props = defineProps({
    tenant: Object,
    databaseName: String,
    plans: Array,
    storeSettings: Object,
    subscriptionPayments: Array,
    latestSubscriptionPayment: Object,
});

const isEditingPlan = ref(false);
const isRecordingPayment = ref(false);

const currentSubscription = computed(() => props.tenant.current_subscription || null);
const currentPlan = computed(() => currentSubscription.value?.plan || null);
const payments = computed(() => props.subscriptionPayments || props.tenant.subscription_payments || []);
const latestPayment = computed(() => props.latestSubscriptionPayment || payments.value?.[0] || null);

const form = useForm({
    plan_id: props.tenant.current_subscription?.plan_id || '',
    renews_at: '',
    trial_ends_at: '',
});

const paymentForm = useForm({
    amount: props.tenant.current_subscription?.plan?.monthly_price || 0,
    currency: props.storeSettings?.store_currency || 'TTD',
    paid_at: new Date().toISOString().slice(0, 10),
    months: 1,
    payment_method: 'manual',
    reference: '',
    payer_name: props.tenant.name || '',
    payer_email: props.tenant.email || '',
    card_brand: '',
    card_last4: '',
    notes: '',
});

watch(() => props.tenant.current_subscription, (subscription) => {
    if (!subscription) return;

    form.plan_id = subscription.plan_id || '';
    paymentForm.amount = subscription.plan?.monthly_price || paymentForm.amount || 0;
}, { deep: true });

function resetPaymentForm() {
    paymentForm.amount = props.tenant.current_subscription?.plan?.monthly_price || 0;
    paymentForm.currency = props.storeSettings?.store_currency || 'TTD';
    paymentForm.paid_at = new Date().toISOString().slice(0, 10);
    paymentForm.months = 1;
    paymentForm.payment_method = 'manual';
    paymentForm.reference = '';
    paymentForm.payer_name = props.tenant.name || '';
    paymentForm.payer_email = props.tenant.email || '';
    paymentForm.card_brand = '';
    paymentForm.card_last4 = '';
    paymentForm.notes = '';
    paymentForm.clearErrors();
}

function openPaymentForm() {
    resetPaymentForm();
    isRecordingPayment.value = true;
}

function cancelPaymentForm() {
    resetPaymentForm();
    isRecordingPayment.value = false;
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString();
}

function formatShortDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
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

const renewalDays = computed(() => daysUntil(currentSubscription.value?.renews_at));

const subscriptionHealth = computed(() => {
    if (!currentSubscription.value) {
        return {
            label: 'No Plan',
            classes: 'bg-yellow-100 text-yellow-800',
            message: 'No subscription plan assigned.',
        };
    }

    if (!props.tenant.is_active) {
        return {
            label: 'Deactivated',
            classes: 'bg-red-100 text-red-800',
            message: 'Tenant site is currently deactivated.',
        };
    }

    if (renewalDays.value !== null && renewalDays.value < 0) {
        return {
            label: 'Past Due',
            classes: 'bg-red-100 text-red-800',
            message: `Renewal is overdue by ${Math.abs(renewalDays.value)} day(s).`,
        };
    }

    if (renewalDays.value !== null && renewalDays.value <= 7) {
        return {
            label: 'Renewal Soon',
            classes: 'bg-yellow-100 text-yellow-800',
            message: `Renewal is due in ${renewalDays.value} day(s).`,
        };
    }

    return {
        label: 'Healthy',
        classes: 'bg-green-100 text-green-800',
        message: `Renewal is due in ${renewalDays.value ?? '—'} day(s).`,
    };
});

function sourceLabel(source) {
    const labels = {
        manual_admin: 'Manual Admin',
        tenant_portal_test: 'Tenant Portal Test',
        tenant_portal_wipay: 'Tenant WiPay',
        wipay: 'WiPay',
    };

    return labels[source] || source || '—';
}

function cardText(payment) {
    if (!payment) return '—';

    if (payment.card_brand && payment.card_last4) {
        return `${payment.card_brand} ending ${payment.card_last4}`;
    }

    if (payment.card_last4) {
        return `Card ending ${payment.card_last4}`;
    }

    if (payment.card_brand) {
        return payment.card_brand;
    }

    return '—';
}

function submitPlan() {
    form.post('/central/tenants/' + props.tenant.id + '/plan', {
        preserveScroll: true,
        onSuccess: () => {
            isEditingPlan.value = false;
        },
    });
}

function submitSubscriptionPayment() {
    paymentForm.post('/central/tenants/' + props.tenant.id + '/subscription-payment', {
        preserveScroll: true,
        onSuccess: () => {
            isRecordingPayment.value = false;
            resetPaymentForm();
        },
    });
}

function firstDomain() {
    return props.tenant.domains && props.tenant.domains.length > 0 ? props.tenant.domains[0].domain : null;
}

function storefrontUrl() {
    const domain = firstDomain();
    if (!domain) return null;
    return 'http://' + domain + ':8000/home';
}

function changeTenantStatus(action) {
    const message = action === 'activate'
        ? 'Activate this tenant?'
        : 'Deactivate this tenant? This will mark the tenant as inactive and close the storefront.';

    if (!confirm(message)) return;

    router.post('/central/tenants/' + props.tenant.id + '/' + action, {}, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="tenant.name" />

    <div class="mx-auto max-w-7xl space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold">{{ tenant.name }}</h1>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-medium"
                            :class="tenant.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                        >
                            {{ tenant.is_active ? 'Active Tenant' : 'Inactive Tenant' }}
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Tenant ID: {{ tenant.id }} · Database: {{ databaseName }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a
                        v-if="storefrontUrl()"
                        :href="storefrontUrl()"
                        target="_blank"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"
                    >
                        Open Storefront
                        <ExternalLink class="h-4 w-4" />
                    </a>

                    <Link
                        href="/central/tenants"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"
                    >
                        Back to Tenants
                    </Link>

                    <button
                        v-if="tenant.is_active"
                        @click="changeTenantStatus('deactivate')"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Deactivate
                    </button>

                    <button
                        v-else
                        @click="changeTenantStatus('activate')"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                    >
                        Activate
                    </button>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500">Subscription Health</p>
                    <CheckCircle v-if="tenant.is_active" class="h-5 w-5 text-green-600" />
                    <AlertTriangle v-else class="h-5 w-5 text-red-600" />
                </div>

                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-medium" :class="subscriptionHealth.classes">
                    {{ subscriptionHealth.label }}
                </span>

                <p class="mt-3 text-sm text-slate-600">{{ subscriptionHealth.message }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Current Plan</p>
                <p class="mt-2 text-2xl font-bold">{{ currentPlan?.name || 'No Plan' }}</p>
                <p class="mt-1 text-sm text-slate-500">
                    {{ currentPlan ? formatMoney(currentPlan.monthly_price, paymentForm.currency) + ' / month' : 'Assign a plan below' }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500">Renewal / Expiry</p>
                    <History class="h-5 w-5 text-slate-400" />
                </div>

                <p class="mt-2 text-2xl font-bold">{{ formatShortDate(currentSubscription?.renews_at) }}</p>
                <p class="mt-1 text-sm text-slate-500">
                    {{ renewalDays === null ? 'No renewal date' : renewalDays >= 0 ? renewalDays + ' day(s) remaining' : Math.abs(renewalDays) + ' day(s) overdue' }}
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500">Last Payment</p>
                    <CreditCard class="h-5 w-5 text-slate-400" />
                </div>

                <p class="mt-2 text-2xl font-bold">
                    {{ latestPayment ? formatMoney(latestPayment.amount, latestPayment.currency) : '—' }}
                </p>
                <p class="mt-1 text-sm text-slate-500">
                    {{ latestPayment ? formatShortDate(latestPayment.paid_at) + ' · ' + sourceLabel(latestPayment.source) : 'No payment history yet' }}
                </p>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-lg font-semibold">Tenant Profile</h2>

                <dl class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <dt class="text-sm text-slate-500">Store Name</dt>
                        <dd class="mt-1 font-medium">{{ tenant.name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Store Email</dt>
                        <dd class="mt-1 font-medium">{{ tenant.email }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Phone</dt>
                        <dd class="mt-1 font-medium">{{ storeSettings?.store_phone || '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Currency</dt>
                        <dd class="mt-1 font-medium">{{ storeSettings?.store_currency || '—' }}</dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-sm text-slate-500">Business Address</dt>
                        <dd class="mt-1 font-medium">{{ storeSettings?.business_address || '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Last Payment Details</h2>

                <div v-if="latestPayment" class="mt-5 space-y-3 text-sm">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-slate-500">Paid By</p>
                        <p class="font-semibold">{{ latestPayment.payer_name || '—' }}</p>
                        <p class="text-slate-500">{{ latestPayment.payer_email || '—' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-slate-50 p-3">
                            <p class="text-slate-500">Method</p>
                            <p class="font-semibold">{{ latestPayment.payment_method || '—' }}</p>
                        </div>

                        <div class="rounded-lg bg-slate-50 p-3">
                            <p class="text-slate-500">Source</p>
                            <p class="font-semibold">{{ sourceLabel(latestPayment.source) }}</p>
                        </div>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-slate-500">Card</p>
                        <p class="font-semibold">{{ cardText(latestPayment) }}</p>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-slate-500">Reference / Transaction ID</p>
                        <p class="break-all font-semibold">{{ latestPayment.reference || latestPayment.provider_transaction_id || '—' }}</p>
                    </div>
                </div>

                <div v-else class="mt-5 rounded-lg bg-yellow-50 p-4 text-sm text-yellow-800">
                    No subscription payment has been recorded yet.
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                <div>
                    <h2 class="text-lg font-semibold">Manual Subscription Payment</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Record a tenant subscription payment made outside the app.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button
                        v-if="!isRecordingPayment"
                        @click="openPaymentForm"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                    >
                        Record New Payment
                    </button>

                    <button
                        v-if="isRecordingPayment"
                        type="button"
                        @click="cancelPaymentForm"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"
                    >
                        Cancel
                    </button>
                </div>
            </div>

            <div v-if="!isRecordingPayment" class="mt-6 rounded-lg bg-slate-50 p-5">
                <div v-if="latestPayment" class="grid grid-cols-1 gap-5 md:grid-cols-4">
                    <div>
                        <p class="text-sm text-slate-500">Last Payment</p>
                        <p class="mt-1 text-xl font-bold">{{ formatMoney(latestPayment.amount, latestPayment.currency) }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Paid Date</p>
                        <p class="mt-1 font-semibold">{{ formatShortDate(latestPayment.paid_at) }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Method</p>
                        <p class="mt-1 font-semibold">{{ latestPayment.payment_method || '—' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">New Renewal</p>
                        <p class="mt-1 font-semibold">{{ formatShortDate(latestPayment.new_renews_at) }}</p>
                    </div>
                </div>

                <div v-else class="text-sm text-slate-600">
                    No manual or tenant subscription payments have been recorded yet.
                </div>
            </div>

            <form v-if="isRecordingPayment" @submit.prevent="submitSubscriptionPayment" class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Amount Paid</label>
                    <input v-model="paymentForm.amount" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                    <p v-if="paymentForm.errors.amount" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.amount }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Currency</label>
                    <input v-model="paymentForm.currency" maxlength="3" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 uppercase" />
                    <p v-if="paymentForm.errors.currency" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.currency }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Paid At</label>
                    <input v-model="paymentForm.paid_at" type="date" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                    <p v-if="paymentForm.errors.paid_at" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.paid_at }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Months Paid</label>
                    <input v-model="paymentForm.months" type="number" min="1" max="24" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                    <p v-if="paymentForm.errors.months" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.months }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Payment Method</label>
                    <select v-model="paymentForm.payment_method" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                        <option value="manual">Manual</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="wipay">WiPay</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Reference</label>
                    <input v-model="paymentForm.reference" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Receipt / transaction ID" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Payer Name</label>
                    <input v-model="paymentForm.payer_name" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Payer Email</label>
                    <input v-model="paymentForm.payer_email" type="email" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Card Brand</label>
                    <input v-model="paymentForm.card_brand" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Visa, Mastercard, etc." />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Card Last 4</label>
                    <input v-model="paymentForm.card_last4" maxlength="4" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="4242" />
                    <p v-if="paymentForm.errors.card_last4" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.card_last4 }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Notes</label>
                    <input v-model="paymentForm.notes" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Optional admin notes" />
                </div>

                <div class="md:col-span-4 flex justify-end">
                    <button type="submit" :disabled="paymentForm.processing" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-60">
                        {{ paymentForm.processing ? 'Saving...' : 'Save Payment & Extend Renewal' }}
                    </button>
                </div>

                <p v-if="paymentForm.errors.subscription" class="md:col-span-4 text-sm text-red-600">
                    {{ paymentForm.errors.subscription }}
                </p>
            </form>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <h2 class="text-lg font-semibold">Subscription Payment History</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Complete accounting log of all monthly subscription payments.
                </p>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Paid</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Payer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Method / Card</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Source</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Renewal Moved</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Notes</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="payment in payments" :key="payment.id">
                            <td class="px-4 py-3 text-sm">{{ formatShortDate(payment.paid_at) }}</td>

                            <td class="px-4 py-3 text-sm font-semibold">
                                {{ formatMoney(payment.amount, payment.currency) }}
                            </td>

                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium">{{ payment.payer_name || '—' }}</div>
                                <div class="text-xs text-slate-500">{{ payment.payer_email || '—' }}</div>
                            </td>

                            <td class="px-4 py-3 text-sm">
                                <div>{{ payment.payment_method || '—' }}</div>
                                <div v-if="payment.card_brand || payment.card_last4" class="text-xs text-slate-500">
                                    {{ cardText(payment) }}
                                </div>
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
                                <div>{{ formatShortDate(payment.previous_renews_at) }}</div>
                                <div class="text-xs text-slate-500">to</div>
                                <div class="font-medium">{{ formatShortDate(payment.new_renews_at) }}</div>
                            </td>

                            <td class="max-w-[220px] px-4 py-3 text-sm text-slate-600">
                                {{ payment.notes || '—' }}
                            </td>
                        </tr>

                        <tr v-if="payments.length === 0">
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                No subscription payments recorded yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Plan Management</h2>

                <div v-if="currentSubscription && currentPlan && !isEditingPlan" class="mt-6 rounded-lg bg-slate-50 p-5">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        <div>
                            <p class="text-sm text-slate-500">Current Plan</p>
                            <p class="mt-1 text-xl font-bold">{{ currentPlan.name }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Monthly Price</p>
                            <p class="mt-1 font-semibold">{{ formatMoney(currentPlan.monthly_price, paymentForm.currency) }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Renews</p>
                            <p class="mt-1 font-semibold">{{ formatShortDate(currentSubscription.renews_at) }}</p>
                        </div>
                    </div>

                    <button @click="isEditingPlan = true" class="mt-5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Edit Plan
                    </button>
                </div>

                <form v-if="isEditingPlan || !currentSubscription" @submit.prevent="submitPlan" class="mt-6 grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Plan</label>
                        <select v-model="form.plan_id" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                            <option value="">Select a plan</option>
                            <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                {{ plan.name }} — {{ formatMoney(plan.monthly_price, paymentForm.currency) }} / month
                            </option>
                        </select>
                        <p v-if="form.errors.plan_id" class="mt-1 text-sm text-red-600">{{ form.errors.plan_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Renews At</label>
                        <input v-model="form.renews_at" type="date" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                        <p class="mt-1 text-xs text-slate-500">Leave blank for one month from now.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Trial Ends At</label>
                        <input v-model="form.trial_ends_at" type="date" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <button v-if="isEditingPlan" type="button" @click="isEditingPlan = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                            Cancel
                        </button>

                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                            {{ form.processing ? 'Saving...' : 'Save Plan' }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Payout Account Status</h2>

                <div v-if="tenant.payout_accounts && tenant.payout_accounts.length > 0" class="mt-5 rounded-lg bg-slate-50 p-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-slate-500">Method</p>
                            <p class="mt-1 font-semibold capitalize">{{ tenant.payout_accounts[0].type }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">Status</p>
                            <p class="mt-1 font-semibold capitalize">{{ tenant.payout_accounts[0].status }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-sm text-slate-500">Account Holder</p>
                            <p class="mt-1 font-semibold">{{ tenant.payout_accounts[0].account_holder_name || '—' }}</p>
                        </div>
                    </div>

                    <Link :href="'/central/payouts/' + tenant.id" class="mt-5 inline-flex rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-100">
                        View Payouts
                    </Link>
                </div>

                <div v-else class="mt-5 rounded-lg bg-yellow-50 p-4 text-sm text-yellow-800">
                    This tenant has not submitted a payout account yet.
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Storefront Domains</h2>

            <div class="mt-5 divide-y divide-slate-200 rounded-lg border border-slate-200">
                <div v-for="domain in tenant.domains" :key="domain.id" class="flex items-center justify-between px-4 py-3">
                    <div>
                        <p class="font-medium">{{ domain.domain }}</p>
                        <p class="text-sm text-slate-500">Domain ID: {{ domain.id }}</p>
                    </div>

                    <a :href="'http://' + domain.domain + ':8000/home'" target="_blank" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-100">
                        Open Store
                    </a>
                </div>

                <div v-if="!tenant.domains || tenant.domains.length === 0" class="px-4 py-6 text-center text-slate-500">
                    No domains found.
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Subscription Plan History</h2>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Started</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Renews</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">Ended</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="subscription in tenant.subscriptions" :key="subscription.id">
                            <td class="px-4 py-3 text-sm font-medium">{{ subscription.plan?.name || 'Unknown Plan' }}</td>
                            <td class="px-4 py-3 text-sm capitalize">{{ subscription.status }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(subscription.starts_at) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(subscription.renews_at) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(subscription.ends_at) }}</td>
                        </tr>

                        <tr v-if="!tenant.subscriptions || tenant.subscriptions.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                No subscription history yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>