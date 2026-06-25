<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';

defineOptions({ layout: CentralLayout });

import { Head, Link } from '@inertiajs/vue3';

defineProps({
    tenants: Array,
    summary: Object,
});

function money(value, currency = 'USD') {
    return currency + ' ' + Number(value || 0).toFixed(2);
}
</script>

<template>
    <Head title="Payouts" />

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                <div>
                    <h1 class="text-2xl font-bold">Payouts</h1>
                    <p class="text-sm text-slate-500">Review pending tenant payouts and mark payout batches as paid.</p>
                </div>

                <div class="flex gap-3">
                    <Link href="/central" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Dashboard
                    </Link>
                    <Link href="/central/transactions" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Transactions
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-8">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Pending Gross</p>
                    <p class="mt-2 text-2xl font-bold">{{ money(summary.pendingGrossTotal) }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Pending Platform Profit</p>
                    <p class="mt-2 text-2xl font-bold text-green-600">{{ money(summary.pendingCommissionTotal) }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Pending Tenant Payouts</p>
                    <p class="mt-2 text-2xl font-bold text-indigo-600">{{ money(summary.pendingPayoutTotal) }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Pending Transactions</p>
                    <p class="mt-2 text-2xl font-bold">{{ summary.pendingTransactionCount }}</p>
                </div>
            </div>

            <section class="mt-8 rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold">Tenant Payout Summary</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Tenant</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Transactions</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Gross</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Platform Profit</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Payout Due</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Payout Account</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="tenant in tenants" :key="tenant.id">
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ tenant.name }}</div>
                                    <div class="text-sm text-slate-500">{{ tenant.id }}</div>
                                </td>

                                <td class="px-6 py-4 text-sm">{{ tenant.pending_transaction_count || 0 }}</td>
                                <td class="px-6 py-4 text-sm">{{ money(tenant.pending_gross_total) }}</td>
                                <td class="px-6 py-4 text-sm text-green-700">{{ money(tenant.pending_commission_total) }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-indigo-700">{{ money(tenant.pending_payout_total) }}</td>

                                <td class="px-6 py-4 text-sm">
                                    <span v-if="tenant.payout_accounts && tenant.payout_accounts.length > 0" class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                        {{ tenant.payout_accounts[0].type }} / {{ tenant.payout_accounts[0].status }}
                                    </span>
                                    <span v-else class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">
                                        Missing
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <Link :href="'/central/payouts/' + tenant.id" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                        View
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="tenants.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                    No tenants found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</template>
