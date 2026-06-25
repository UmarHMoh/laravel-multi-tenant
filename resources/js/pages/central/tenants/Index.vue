<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';

defineOptions({ layout: CentralLayout });

import { Head, Link } from '@inertiajs/vue3';

defineProps({
    tenants: Array,
});

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
}

function formatMoney(value) {
    return '$' + Number(value || 0).toFixed(2);
}
</script>

<template>
    <Head title="Tenants" />

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                <div>
                    <h1 class="text-2xl font-bold">Tenants</h1>
                    <p class="text-sm text-slate-500">View and manage all stores on your platform.</p>
                </div>

                <div class="flex gap-3">
                    <Link href="/central" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Dashboard
                    </Link>

                    <Link href="/central/plans" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Plans
                    </Link>

                    <Link href="/central/tenants/create" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Create Tenant
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-8">
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold">All Tenants</h2>
                    <p class="text-sm text-slate-500">This list comes from the central tenants table.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tenant</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Current Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Domains</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Created</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200 bg-white">
                            <tr v-for="tenant in tenants" :key="tenant.id" :class="{ 'bg-red-50/50 opacity-75': !tenant.is_active }">
                                <td class="px-6 py-4">
                                    <div class="font-medium">{{ tenant.name }}</div>
                                    <div class="text-sm text-slate-500">{{ tenant.id }}</div>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">{{ tenant.email }}</td>

                                <td class="px-6 py-4 text-sm">
                                    <div v-if="tenant.current_subscription && tenant.current_subscription.plan">
                                        <div class="font-medium">{{ tenant.current_subscription.plan.name }}</div>
                                        <div class="text-slate-500">
                                            {{ formatMoney(tenant.current_subscription.plan.monthly_price) }} / month
                                            · {{ Number(tenant.current_subscription.plan.commission_rate).toFixed(2) }}%
                                        </div>
                                    </div>
                                    <span v-else class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">
                                        No Plan
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    <div v-if="tenant.domains && tenant.domains.length > 0" class="space-y-1">
                                        <div v-for="domain in tenant.domains" :key="domain.id">
                                            <a
                                                :href="'http://' + domain.domain + ':8000/home'"
                                                target="_blank"
                                                class="text-indigo-600 hover:text-indigo-800"
                                            >
                                                {{ domain.domain }}
                                            </a>
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-400">No domain</span>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="tenant.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                    >
                                        {{ tenant.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ formatDate(tenant.created_at) }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <Link :href="'/central/tenants/' + tenant.id" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
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
            </div>
        </main>
    </div>
</template>
