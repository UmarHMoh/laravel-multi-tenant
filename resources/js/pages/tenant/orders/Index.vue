<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'

const props = defineProps({
    orders: Object,
    stats: Object,
    filters: Object,
    statusOptions: Array,
    paymentStatusOptions: Array,
})

const form = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    payment_status: props.filters?.payment_status || '',
})

const orderRows = computed(() => props.orders?.data || [])

function money(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'TTD',
    }).format(Number(value || 0))
}

function applyFilters() {
    router.get('/manage/order', {
        search: form.search || undefined,
        status: form.status || undefined,
        payment_status: form.payment_status || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

function clearFilters() {
    form.search = ''
    form.status = ''
    form.payment_status = ''

    router.get('/manage/order', {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

function badgeClass(value) {
    const status = String(value || '').toLowerCase()

    if (['paid', 'completed'].includes(status)) return 'bg-emerald-100 text-emerald-800'
    if (['processing'].includes(status)) return 'bg-blue-100 text-blue-800'
    if (['cancelled', 'failed', 'refunded'].includes(status)) return 'bg-red-100 text-red-800'

    return 'bg-amber-100 text-amber-800'
}
</script>

<template>
    <Head title="Orders" />

    <AppLayout>
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Orders</h1>
                <p class="text-sm text-slate-500">Manage customer orders, fulfilment, and payment status.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Orders</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.total_orders || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Pending</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.pending_orders || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Paid Orders</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.paid_orders || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Paid Revenue</p>
                    <p class="mt-2 text-2xl font-bold">{{ money(stats?.revenue || 0) }}</p>
                </div>
            </div>

            <div class="rounded-xl border bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Search</label>
                        <input
                            v-model="form.search"
                            type="search"
                            placeholder="Order number, customer, email, phone"
                            class="mt-1 w-full rounded-lg border px-3 py-2"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Order Status</label>
                        <select v-model="form.status" class="mt-1 w-full rounded-lg border px-3 py-2" @change="applyFilters">
                            <option value="">All</option>
                            <option v-for="status in statusOptions" :key="status" :value="status">
                                {{ status }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Payment Status</label>
                        <select v-model="form.payment_status" class="mt-1 w-full rounded-lg border px-3 py-2" @change="applyFilters">
                            <option value="">All</option>
                            <option v-for="status in paymentStatusOptions" :key="status" :value="status">
                                {{ status }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex gap-3">
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" @click="applyFilters">
                        Apply Filters
                    </button>
                    <button class="rounded-lg border px-4 py-2 text-sm" @click="clearFilters">
                        Clear
                    </button>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr class="border-b">
                                <th class="px-4 py-3">Order</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3">Order Status</th>
                                <th class="px-4 py-3">Payment</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="order in orderRows" :key="order.id" class="border-b">
                                <td class="px-4 py-3 font-medium">
                                    {{ order.order_number }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">{{ order.billing_name }}</div>
                                    <div class="text-xs text-slate-500">{{ order.billing_email }}</div>
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ money(order.total) }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="badgeClass(order.status)">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="badgeClass(order.payment_status)">
                                        {{ order.payment_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">
                                    {{ order.created_at ? new Date(order.created_at).toLocaleString() : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <Link :href="`/manage/order/${order.id}`" class="rounded-lg border px-3 py-1.5 text-sm">
                                        View
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="!orderRows.length">
                                <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                    No orders found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="orders?.links?.length > 3" class="flex flex-wrap gap-2 p-4">
                    <Link
                        v-for="link in orders.links"
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
