<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CentralLayout from '@/layouts/CentralLayout.vue';

defineProps({
    stats: Object,
    recentTenants: Array,
});

function firstDomain(tenant) {
    return tenant.domains && tenant.domains.length > 0 ? tenant.domains[0].domain : null;
}
</script>

<template>
    <Head title="Central Dashboard" />

    <CentralLayout
        title="Central Platform Dashboard"
        description="Manage tenants, stores, domains, plans, payments, transactions, and payouts."
    >
        <template #actions>
            <Link href="/central/tenants/create" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Create Tenant
            </Link>
        </template>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total Tenants</p>
                <p class="mt-2 text-3xl font-bold">{{ stats.totalTenants }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Active Tenants</p>
                <p class="mt-2 text-3xl font-bold text-green-600">{{ stats.activeTenants }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Inactive Tenants</p>
                <p class="mt-2 text-3xl font-bold text-red-600">{{ stats.inactiveTenants }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Total Domains</p>
                <p class="mt-2 text-3xl font-bold">{{ stats.totalDomains }}</p>
            </div>
        </div>

        <section class="mt-8 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold">Recent Tenants</h2>
                    <p class="text-sm text-slate-500">Newest tenant stores on the platform.</p>
                </div>

                <Link href="/central/tenants" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    View all →
                </Link>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Store</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Domain</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="tenant in recentTenants" :key="tenant.id">
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ tenant.name }}</div>
                                <div class="text-sm text-slate-500">{{ tenant.id }}</div>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">{{ tenant.email }}</td>

                            <td class="px-6 py-4 text-sm">
                                <a
                                    v-if="firstDomain(tenant)"
                                    :href="'http://' + firstDomain(tenant) + ':8000'"
                                    target="_blank"
                                    class="text-indigo-600 hover:text-indigo-800"
                                >
                                    {{ firstDomain(tenant) }}
                                </a>
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

                            <td class="px-6 py-4 text-right">
                                <Link :href="'/central/tenants/' + tenant.id" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    View
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="recentTenants.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                                No tenants yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </CentralLayout>
</template>
