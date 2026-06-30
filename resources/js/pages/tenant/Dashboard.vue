<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    CreditCard,
    ExternalLink,
    Package,
    Settings,
    ShoppingCart,
    Store,
    Wallet,
} from 'lucide-vue-next';

const props = defineProps({
    payoutBalance: Object,
    tenantId: String,
    tenantName: String,
    store: Object,
    currentPlan: Object,
    currentSubscription: Object,
    payoutAccount: Object,
    payoutSummary: Object,
});

function money(value) {
    const currency = props.store?.currency || 'USD';
    return currency + ' ' + Number(value || 0).toFixed(2);
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
}

function transactionFeeLabel() {
    const plan = props.currentPlan || {};
    const percent = Number(plan.public_transaction_fee_percent ?? plan.transaction_fee_percent ?? plan.commission_rate ?? 0);
    const fixed = Number(plan.public_transaction_fee_fixed ?? plan.transaction_fee_fixed ?? 0);
    const currency = plan.currency || props.store?.currency || 'TTD';

    return fixed > 0
        ? `${percent.toFixed(2)}% + ${currency} ${fixed.toFixed(2)}`
        : `${percent.toFixed(2)}%`;
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 px-6 py-8 text-slate-900">
            <div class="mx-auto max-w-7xl">
                <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">
                            Tenant Dashboard
                        </p>
                        <h1 class="mt-1 text-3xl font-bold">
                            {{ store?.name || tenantName }}
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm text-slate-500">
                            Manage your store, products, orders, payout account, and business settings.
                        </p>
                    </div>

                    <a
                        :href="store?.storefront_url || '/home'"
                        target="_blank"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                    >
                        <ExternalLink class="h-4 w-4" />
                        Open Storefront
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-500">Current Plan</p>
                            <Package class="h-5 w-5 text-slate-400" />
                        </div>

                        <p class="mt-3 text-2xl font-bold">
                            {{ currentPlan?.name || 'No Plan' }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            <template v-if="currentPlan">
                                {{ money(currentPlan.monthly_price) }} / month
                            </template>
                            <template v-else>
                                Contact platform admin.
                            </template>
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-500">Transaction Fee</p>
                            <Store class="h-5 w-5 text-slate-400" />
                        </div>

                        <p class="mt-3 text-2xl font-bold">
                            {{ transactionFeeLabel() }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Applied to online payments.
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-500">Pending Payout</p>
                            <Wallet class="h-5 w-5 text-slate-400" />
                        </div>

                        <p class="mt-3 text-2xl font-bold text-indigo-600">
                            {{ money(payoutSummary?.pending_total) }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ payoutSummary?.pending_transactions || 0 }} pending transactions
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-slate-500">Payout Account</p>
                            <CreditCard class="h-5 w-5 text-slate-400" />
                        </div>

                        <p class="mt-3 text-2xl font-bold capitalize">
                            {{ payoutAccount?.status || 'Missing' }}
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            <template v-if="payoutAccount">
                                {{ payoutAccount.type }} account submitted
                            </template>
                            <template v-else>
                                Add payout account.
                            </template>
                        </p>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                        <h2 class="text-lg font-semibold">Store Overview</h2>

                        <dl class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <dt class="text-sm text-slate-500">Store Name</dt>
                                <dd class="mt-1 font-medium">{{ store?.name || tenantName }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Store Email</dt>
                                <dd class="mt-1 font-medium">{{ store?.email || '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Store Domain</dt>
                                <dd class="mt-1 font-medium">{{ store?.domain || '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Currency</dt>
                                <dd class="mt-1 font-medium">{{ store?.currency || 'USD' }}</dd>
                            </div>

                            <div class="md:col-span-2">
                                <dt class="text-sm text-slate-500">Description</dt>
                                <dd class="mt-1 font-medium">{{ store?.description || 'No description added yet.' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <Link href="/manage/store-settings" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                Edit Store Settings
                            </Link>

                            <a
                                :href="store?.storefront_url || '/home'"
                                target="_blank"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"
                            >
                                View Storefront
                            </a>
                        </div>
                    </section>

                    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold">Subscription</h2>

                        <div v-if="currentPlan" class="mt-5 space-y-4">
                            <div class="rounded-lg bg-indigo-50 p-4">
                                <p class="text-sm text-indigo-700">Plan</p>
                                <p class="mt-1 text-xl font-bold text-indigo-900">
                                    {{ currentPlan.name }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-slate-50 p-4 text-sm">
                                <p>
                                    <span class="text-slate-500">Monthly Price:</span>
                                    <span class="font-semibold">{{ money(currentPlan.monthly_price) }}</span>
                                </p>

                                <p class="mt-2">
                                    <span class="text-slate-500">Transaction Fee:</span>
                                    <span class="font-semibold">{{ transactionFeeLabel() }}</span>
                                </p>

                                <p class="mt-2">
                                    <span class="text-slate-500">Renews:</span>
                                    <span class="font-semibold">{{ formatDate(currentSubscription?.renews_at) }}</span>
                                </p>
                            </div>
                        </div>

                        <div v-else class="mt-5 rounded-lg bg-yellow-50 p-4 text-sm text-yellow-800">
                            No plan has been assigned to this store yet.
                        </div>
                    </section>
                </div>

                <section class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Quick Actions</h2>

                    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-4">
                        <Link href="/manage/product" class="rounded-xl border border-slate-200 p-5 transition hover:border-indigo-300 hover:bg-indigo-50">
                            <Package class="h-6 w-6 text-indigo-600" />
                            <p class="mt-3 font-semibold">Products</p>
                            <p class="mt-1 text-sm text-slate-500">Manage store products.</p>
                        </Link>

                        <Link href="/manage/order" class="rounded-xl border border-slate-200 p-5 transition hover:border-indigo-300 hover:bg-indigo-50">
                            <ShoppingCart class="h-6 w-6 text-indigo-600" />
                            <p class="mt-3 font-semibold">Orders</p>
                            <p class="mt-1 text-sm text-slate-500">View customer orders.</p>
                        </Link>

                        <Link href="/manage/payout-account" class="rounded-xl border border-slate-200 p-5 transition hover:border-indigo-300 hover:bg-indigo-50">
                            <CreditCard class="h-6 w-6 text-indigo-600" />
                            <p class="mt-3 font-semibold">Payout Account</p>
                            <p class="mt-1 text-sm text-slate-500">Update bank or WiPay info.</p>
                        </Link>

                        <Link href="/manage/store-settings" class="rounded-xl border border-slate-200 p-5 transition hover:border-indigo-300 hover:bg-indigo-50">
                            <Settings class="h-6 w-6 text-indigo-600" />
                            <p class="mt-3 font-semibold">Store Settings</p>
                            <p class="mt-1 text-sm text-slate-500">Update business details.</p>
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
