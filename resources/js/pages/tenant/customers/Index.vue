<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'

const props = defineProps({
    customers: Object,
    stats: Object,
    filters: Object,
})

const form = reactive({
    search: props.filters?.search || '',
})

const customerRows = computed(() => props.customers?.data || [])

function money(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'TTD',
    }).format(Number(value || 0))
}

function applyFilters() {
    router.get('/manage/customer', {
        search: form.search || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

function clearFilters() {
    form.search = ''

    router.get('/manage/customer', {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}
</script>

<template>
    <Head title="Customers" />

    <AppLayout>
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Customers</h1>
                <p class="text-sm text-slate-500">View customer profiles, contact details, and order history.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Customers</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.total_customers || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">With Orders</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.customers_with_orders || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Orders</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.total_orders || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Paid Revenue</p>
                    <p class="mt-2 text-2xl font-bold">{{ money(stats?.total_customer_revenue || 0) }}</p>
                </div>
            </div>

            <div class="rounded-xl border bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Search Customers</label>
                        <input
                            v-model="form.search"
                            type="search"
                            placeholder="Name, email, or phone"
                            class="mt-1 w-full rounded-lg border px-3 py-2"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <div class="flex items-end gap-3">
                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" @click="applyFilters">
                            Search
                        </button>
                        <button class="rounded-lg border px-4 py-2 text-sm" @click="clearFilters">
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr class="border-b">
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Joined</th>
                                <th class="px-4 py-3">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="customer in customerRows" :key="customer.id" class="border-b">
                                <td class="px-4 py-3 font-medium text-slate-900">
                                    {{ customer.name || 'Unnamed Customer' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ customer.email || '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ customer.phone || '—' }}</td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ customer.created_at ? new Date(customer.created_at).toLocaleDateString() : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <Link :href="`/manage/customer/${customer.id}`" class="rounded-lg border px-3 py-1.5 text-sm">
                                        View
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="!customerRows.length">
                                <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                    No customers found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="customers?.links?.length > 3" class="flex flex-wrap gap-2 p-4">
                    <Link
                        v-for="link in customers.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="rounded-lg border px-3 py-2 text-sm"
                        :class="{
                            'bg-slate-900 text-white': link.active,
                            'pointer-events-none opacity-50': !link.url,
                        }"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
