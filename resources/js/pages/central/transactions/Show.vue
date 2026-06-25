
<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';

defineOptions({ layout: CentralLayout });


import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({

    transaction: Object,

});

function money(value, currency = 'USD') {

    return currency + ' ' + Number(value || 0).toFixed(2);

}

function formatDate(date) {

    if (!date) return '—';

    return new Date(date).toLocaleString();

}

function prettyJson(value) {

    if (!value) return '{}';

    try {

        return JSON.stringify(value, null, 2);

    } catch (e) {

        return String(value);

    }

}

</script>

<template>

    <Head :title="'Transaction #' + transaction.id" />

    <div class="min-h-screen bg-slate-50 text-slate-900">

        <header class="border-b border-slate-200 bg-white">

            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">

                <div>

                    <h1 class="text-2xl font-bold">Transaction #{{ transaction.id }}</h1>

                    <p class="text-sm text-slate-500">

                        WiPay processing, platform profit, and tenant payout breakdown.

                    </p>

                </div>

                <div class="flex gap-3">

                    <Link href="/central/transactions" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">

                        Back to Transactions

                    </Link>

                    <Link

                        v-if="transaction.tenant"

                        :href="'/central/tenants/' + transaction.tenant_id"

                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"

                    >

                        Tenant Profile

                    </Link>

                    <Link

                        v-if="transaction.tenant"

                        :href="'/central/payouts/' + transaction.tenant_id"

                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"

                    >

                        Tenant Payouts

                    </Link>

                </div>

            </div>

        </header>

        <main class="mx-auto max-w-7xl px-6 py-8">

            <div class="grid grid-cols-1 gap-5 md:grid-cols-6">

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-slate-500">Customer Paid</p>

                    <p class="mt-2 text-2xl font-bold">

                        {{ money(transaction.gross_amount, transaction.currency) }}

                    </p>

                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-slate-500">WiPay Fee</p>

                    <p class="mt-2 text-2xl font-bold text-red-600">

                        {{ money(transaction.processor_fee, transaction.currency) }}

                    </p>

                    <p class="mt-1 text-xs text-slate-500">

                        {{ Number(transaction.processor_fee_percent || 0).toFixed(2) }}% + {{ money(transaction.processor_fee_fixed, transaction.currency) }}

                    </p>

                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-slate-500">Net After WiPay</p>

                    <p class="mt-2 text-2xl font-bold">

                        {{ money(transaction.net_amount, transaction.currency) }}

                    </p>

                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-slate-500">Tenant Plan Fee</p>

                    <p class="mt-2 text-2xl font-bold text-amber-600">

                        {{ money(transaction.total_tenant_fee_amount, transaction.currency) }}

                    </p>

                    <p class="mt-1 text-xs text-slate-500">

                        {{ Number(transaction.tenant_transaction_fee_percent || transaction.commission_rate || 0).toFixed(2) }}% + {{ money(transaction.tenant_transaction_fee_fixed, transaction.currency) }}

                    </p>

                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-slate-500">Your Platform Profit</p>

                    <p class="mt-2 text-2xl font-bold text-green-600">

                        {{ money(transaction.platform_fee_amount ?? transaction.commission_amount, transaction.currency) }}

                    </p>

                    <p class="mt-1 text-xs text-slate-500">

                        {{ Number(transaction.platform_fee_percent ?? transaction.commission_rate ?? 0).toFixed(2) }}% + {{ money(transaction.platform_fee_fixed, transaction.currency) }}

                    </p>

                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

                    <p class="text-sm text-slate-500">Tenant Payout</p>

                    <p class="mt-2 text-2xl font-bold text-indigo-600">

                        {{ money(transaction.tenant_payout_amount, transaction.currency) }}

                    </p>

                </div>

            </div>

            <div

                v-if="Number(transaction.platform_fee_amount ?? transaction.commission_amount ?? 0) < 0"

                class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800"

            >

                Warning: this transaction has negative platform profit. The tenant plan fee is lower than the WiPay processing fee.

            </div>

            <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">

                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">

                    <h2 class="text-lg font-semibold">Transaction Details</h2>

                    <dl class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">

                        <div>

                            <dt class="text-sm text-slate-500">Tenant</dt>

                            <dd class="mt-1 font-medium">

                                {{ transaction.tenant?.name || transaction.tenant_id }}

                            </dd>

                        </div>

                        <div>

                            <dt class="text-sm text-slate-500">Tenant ID</dt>

                            <dd class="mt-1 font-medium">{{ transaction.tenant_id }}</dd>

                        </div>

                        <div>

                            <dt class="text-sm text-slate-500">Tenant Order ID</dt>

                            <dd class="mt-1 font-medium">{{ transaction.tenant_order_id || '—' }}</dd>

                        </div>

                        <div>

                            <dt class="text-sm text-slate-500">Currency</dt>

                            <dd class="mt-1 font-medium">{{ transaction.currency }}</dd>

                        </div>

                        <div>

                            <dt class="text-sm text-slate-500">Provider</dt>

                            <dd class="mt-1 font-medium">{{ transaction.provider }}</dd>

                        </div>

                        <div>

                            <dt class="text-sm text-slate-500">Provider Transaction ID</dt>

                            <dd class="mt-1 font-medium">{{ transaction.provider_transaction_id || '—' }}</dd>

                        </div>

                        <div>

                            <dt class="text-sm text-slate-500">Payment Status</dt>

                            <dd class="mt-1">

                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">

                                    {{ transaction.payment_status }}

                                </span>

                            </dd>

                        </div>

                        <div>

                            <dt class="text-sm text-slate-500">Payout Status</dt>

                            <dd class="mt-1">

                                <span

                                    class="rounded-full px-2 py-1 text-xs font-medium"

                                    :class="transaction.payout_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"

                                >

                                    {{ transaction.payout_status }}

                                </span>

                            </dd>

                        </div>

                        <div>

                            <dt class="text-sm text-slate-500">Paid At</dt>

                            <dd class="mt-1 font-medium">{{ formatDate(transaction.paid_at) }}</dd>

                        </div>

                        <div>

                            <dt class="text-sm text-slate-500">Payout Marked At</dt>

                            <dd class="mt-1 font-medium">{{ formatDate(transaction.payout_marked_at) }}</dd>

                        </div>

                    </dl>

                </section>

                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

                    <h2 class="text-lg font-semibold">Correct Formula</h2>

                    <div class="mt-5 space-y-3 text-sm">

                        <div class="flex justify-between">

                            <span class="text-slate-500">Customer Paid</span>

                            <span class="font-medium">{{ money(transaction.gross_amount, transaction.currency) }}</span>

                        </div>

                        <div class="flex justify-between">

                            <span class="text-slate-500">Less WiPay Fee</span>

                            <span class="font-medium text-red-600">- {{ money(transaction.processor_fee, transaction.currency) }}</span>

                        </div>

                        <div class="border-t pt-3">

                            <div class="flex justify-between">

                                <span class="font-semibold">Net After WiPay</span>

                                <span class="font-semibold">{{ money(transaction.net_amount, transaction.currency) }}</span>

                            </div>

                        </div>

                        <div class="mt-5 rounded-lg bg-slate-50 p-4">

                            <p class="mb-3 font-semibold">Tenant-facing fee split</p>

                            <div class="flex justify-between">

                                <span class="text-slate-500">Tenant Plan Fee</span>

                                <span class="font-medium">{{ money(transaction.total_tenant_fee_amount, transaction.currency) }}</span>

                            </div>

                            <div class="mt-2 flex justify-between">

                                <span class="text-slate-500">Less WiPay Fee</span>

                                <span class="font-medium text-red-600">- {{ money(transaction.processor_fee, transaction.currency) }}</span>

                            </div>

                            <div class="mt-3 border-t pt-3 flex justify-between">

                                <span class="font-semibold">Your Profit</span>

                                <span class="font-semibold text-green-700">

                                    {{ money(transaction.platform_fee_amount ?? transaction.commission_amount, transaction.currency) }}

                                </span>

                            </div>

                        </div>

                        <div class="rounded-lg bg-indigo-50 p-4">

                            <p class="text-sm text-indigo-700">Tenant Payout</p>

                            <p class="mt-1 text-xl font-bold text-indigo-900">

                                {{ money(transaction.tenant_payout_amount, transaction.currency) }}

                            </p>

                        </div>

                    </div>

                </section>

            </div>

            <section class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-semibold">Metadata</h2>

                <pre class="mt-5 overflow-x-auto rounded-lg bg-slate-900 p-4 text-sm text-white">{{ prettyJson(transaction.metadata) }}</pre>

            </section>

        </main>

    </div>

</template>

