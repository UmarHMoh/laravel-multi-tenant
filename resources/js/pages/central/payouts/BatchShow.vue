<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';

defineOptions({ layout: CentralLayout });

import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    payout: Object,
});

function money(value, currency = 'USD') {
    return currency + ' ' + Number(value || 0).toFixed(2);
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString();
}
</script>

<template>
    <Head :title="'Payout Batch #' + payout.id" />

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                <div>
                    <h1 class="text-2xl font-bold">Payout Batch #{{ payout.id }}</h1>
                    <p class="text-sm text-slate-500">
                        Transactions included in this payout batch.
                    </p>
                </div>

                <div class="flex gap-3">
                    <Link
                        :href="'/central/payouts/' + payout.tenant_id"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"
                    >
                        Back to Tenant Payouts
                    </Link>

                    <Link
                        :href="'/central/tenants/' + payout.tenant_id"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                    >
                        Tenant Profile
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-8">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-5">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Gross</p>
                    <p class="mt-2 text-2xl font-bold">{{ money(payout.total_gross, payout.currency) }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Processor Fees</p>
                    <p class="mt-2 text-2xl font-bold">{{ money(payout.total_processor_fees, payout.currency) }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Net</p>
                    <p class="mt-2 text-2xl font-bold">{{ money(payout.total_net, payout.currency) }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Platform Profit</p>
                    <p class="mt-2 text-2xl font-bold text-green-600">{{ money(payout.total_commission, payout.currency) }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Payout</p>
                    <p class="mt-2 text-2xl font-bold text-indigo-600">{{ money(payout.total_payout, payout.currency) }}</p>
                </div>
            </div>

            <section class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Payout Details</h2>

                <dl class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div>
                        <dt class="text-sm text-slate-500">Tenant</dt>
                        <dd class="mt-1 font-medium">{{ payout.tenant?.name || payout.tenant_id }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Tenant ID</dt>
                        <dd class="mt-1 font-medium">{{ payout.tenant_id }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Status</dt>
                        <dd class="mt-1">
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                {{ payout.status }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Reference</dt>
                        <dd class="mt-1 font-medium">{{ payout.reference || '—' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Paid At</dt>
                        <dd class="mt-1 font-medium">{{ formatDate(payout.paid_at) }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm text-slate-500">Created</dt>
                        <dd class="mt-1 font-medium">{{ formatDate(payout.created_at) }}</dd>
                    </div>

                    <div class="md:col-span-3">
                        <dt class="text-sm text-slate-500">Notes</dt>
                        <dd class="mt-1 font-medium">{{ payout.notes || '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="mt-8 rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold">Transactions in this Payout</h2>
                    <p class="text-sm text-slate-500">
                        These platform transactions were marked as paid in this payout batch.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Transaction</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Gross</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fee</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Platform Profit</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Payout</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="transaction in payout.transactions" :key="transaction.id">
                                <td class="px-6 py-4 text-sm font-medium">#{{ transaction.id }}</td>
                                <td class="px-6 py-4 text-sm">{{ transaction.tenant_order_id || '—' }}</td>
                                <td class="px-6 py-4 text-sm">{{ money(transaction.gross_amount, transaction.currency) }}</td>
                                <td class="px-6 py-4 text-sm">{{ money(transaction.processor_fee, transaction.currency) }}</td>
                                <td class="px-6 py-4 text-sm text-green-700">{{ money(transaction.commission_amount, transaction.currency) }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-indigo-700">{{ money(transaction.tenant_payout_amount, transaction.currency) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="'/central/transactions/' + transaction.id"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="!payout.transactions || payout.transactions.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                    No transactions found in this payout batch.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</template>
