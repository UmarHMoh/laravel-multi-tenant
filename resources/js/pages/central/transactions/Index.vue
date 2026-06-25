<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CentralLayout from '@/layouts/CentralLayout.vue';

defineProps({
    transactions: Array,
    summary: Object,
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
    <Head title="Platform Transactions" />

    <CentralLayout
        title="Platform Transactions"
        description="Track gross sales, processor fees, commissions, and tenant payouts."
    >
        <template #actions>
            <Link href="/central/transactions/create" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Add Test Transaction
            </Link>
        </template>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Customer Paid Sales</p>
                <p class="mt-2 text-2xl font-bold">{{ money(summary.gross) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Processor Fees</p>
                <p class="mt-2 text-2xl font-bold">{{ money(summary.processorFees) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Net Received</p>
                <p class="mt-2 text-2xl font-bold">{{ money(summary.net) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Your Platform Profit</p>
                <p class="mt-2 text-2xl font-bold text-green-600">{{ money(summary.commission) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Pending Payouts</p>
                <p class="mt-2 text-2xl font-bold text-indigo-600">{{ money(summary.pendingPayouts) }}</p>
            </div>
        </div>

        <section class="mt-8 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold">All Transactions</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Customer Paid</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Processor Fee</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Net</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Platform Profit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Tenant Payout</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Paid At</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="transaction in transactions" :key="transaction.id">
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ transaction.tenant?.name || transaction.tenant_id }}</div>
                                <div class="text-sm text-slate-500">{{ transaction.tenant_id }}</div>
                            </td>

                            <td class="px-6 py-4 text-sm">{{ money(transaction.gross_amount, transaction.currency) }}</td>
                            <td class="px-6 py-4 text-sm">{{ money(transaction.processor_fee, transaction.currency) }}</td>
                            <td class="px-6 py-4 text-sm">{{ money(transaction.net_amount, transaction.currency) }}</td>

                            <td class="px-6 py-4 text-sm">
                                <div>{{ money(transaction.commission_amount, transaction.currency) }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ Number(transaction.commission_rate || 0).toFixed(2) }}%
                                    <span v-if="Number(transaction.platform_fee_fixed || 0) > 0">
                                        + {{ money(transaction.platform_fee_fixed, transaction.currency) }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm font-medium">
                                {{ money(transaction.tenant_payout_amount, transaction.currency) }}
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="transaction.payout_status === 'paid'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-yellow-100 text-yellow-700'"
                                >
                                    {{ transaction.payout_status }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">{{ formatDate(transaction.paid_at) }}</td>

                            <td class="px-6 py-4 text-right">
                                <Link :href="'/central/transactions/' + transaction.id" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    View
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="transactions.length === 0">
                            <td colspan="9" class="px-6 py-8 text-center text-slate-500">
                                No transactions yet. Add a test transaction to verify calculations.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </CentralLayout>
</template>
