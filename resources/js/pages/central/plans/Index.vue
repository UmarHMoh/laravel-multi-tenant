<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';

defineOptions({ layout: CentralLayout });

import { Head, Link } from '@inertiajs/vue3';

defineProps({
    plans: Array,
});

function money(value) {
    return '$' + Number(value || 0).toFixed(2);
}

function percent(value) {
    return Number(value || 0).toFixed(2) + '%';
}
</script>

<template>
    <Head title="Plans" />

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                <div>
                    <h1 class="text-2xl font-bold">Plans</h1>
                    <p class="text-sm text-slate-500">
                        Manage monthly plans and tenant-facing transaction fees.
                    </p>
                </div>

                <div class="flex gap-3">
                    <Link href="/central" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Dashboard
                    </Link>

                    <Link href="/central/payment-settings" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Payment Settings
                    </Link>

                    <Link href="/central/plans/create" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Create Plan
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                The transaction fee shown here is the total fee charged to the tenant. WiPay processing fee is configured separately in Payment Settings.
            </div>

            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold">All Plans</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Monthly Price</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Tenant Transaction Fee</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Limits</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Features</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="plan in plans" :key="plan.id">
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ plan.name }}</div>
                                    <div class="text-sm text-slate-500">{{ plan.slug }}</div>
                                    <span
                                        class="mt-2 inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                        :class="plan.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                    >
                                        {{ plan.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 font-medium">{{ money(plan.monthly_price) }}</td>

                                <td class="px-6 py-4">
                                    <div class="font-medium">
                                        {{ percent(plan.transaction_fee_percent ?? plan.commission_rate) }} + {{ money(plan.transaction_fee_fixed) }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        Total tenant-facing fee
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    Products:
                                    <span class="font-medium">
                                        {{ plan.max_products === null ? 'Unlimited' : plan.max_products }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <div>Custom Domain: {{ plan.custom_domain_enabled ? 'Yes' : 'No' }}</div>
                                    <div>API Payments: {{ plan.api_payment_enabled ? 'Yes' : 'No' }}</div>
                                    <div>Multiple Pages: {{ plan.allow_multiple_builder_pages ? 'Yes' : 'No' }}</div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <Link :href="'/central/plans/' + plan.id + '/edit'" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                        Edit
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="plans.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                    No plans yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</template>
