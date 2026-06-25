<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    customer: Object,
    orders: Object,
    stats: Object,
})

const orderRows = computed(() => props.orders?.data || [])

function money(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'TTD',
    }).format(Number(value || 0))
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
    <Head :title="customer?.name || 'Customer'" />

    <AppLayout>
        <div class="space-y-6 p-6">
            <div>
                <Link href="/manage/customer" class="text-sm text-slate-500 hover:text-slate-900">
                    ← Back to Customers
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-slate-900">
                    {{ customer?.name || 'Unnamed Customer' }}
                </h1>
                <p class="text-sm text-slate-500">Customer profile and order history.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Orders</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.order_count || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Paid Orders</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.paid_order_count || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Spent</p>
                    <p class="mt-2 text-2xl font-bold">{{ money(stats?.total_spent || 0) }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Latest Order</p>
                    <p class="mt-2 text-lg font-bold">
                        {{ stats?.latest_order_at ? new Date(stats.latest_order_at).toLocaleDateString() : '—' }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-xl border bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Contact Details</h2>

                    <dl class="mt-4 space-y-4 text-sm">
                        <div>
                            <dt class="text-slate-500">Name</dt>
                            <dd class="font-medium">{{ customer?.name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Email</dt>
                            <dd class="font-medium">{{ customer?.email || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Phone</dt>
                            <dd class="font-medium">{{ customer?.phone || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Address</dt>
                            <dd class="font-medium">
                                {{ customer?.address || '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Joined</dt>
                            <dd class="font-medium">
                                {{ customer?.created_at ? new Date(customer.created_at).toLocaleString() : '—' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border bg-white p-6 shadow-sm lg:col-span-2">
                    <h2 class="text-lg font-semibold">Order History</h2>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="py-2">Order</th>
                                    <th class="py-2">Total</th>
                                    <th class="py-2">Status</th>
                                    <th class="py-2">Payment</th>
                                    <th class="py-2">Date</th>
                                    <th class="py-2">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="order in orderRows" :key="order.id" class="border-b">
                                    <td class="py-3 font-medium">{{ order.order_number }}</td>
                                    <td class="py-3 font-semibold">{{ money(order.total) }}</td>
                                    <td class="py-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="badgeClass(order.status)">
                                            {{ order.status }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold capitalize" :class="badgeClass(order.payment_status)">
                                            {{ order.payment_status }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-slate-500">
                                        {{ order.created_at ? new Date(order.created_at).toLocaleDateString() : '—' }}
                                    </td>
                                    <td class="py-3">
                                        <Link :href="`/manage/order/${order.id}`" class="rounded-lg border px-3 py-1.5 text-sm">
                                            View
                                        </Link>
                                    </td>
                                </tr>

                                <tr v-if="!orderRows.length">
                                    <td colspan="6" class="py-10 text-center text-slate-500">
                                        No orders found for this customer.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="orders?.links?.length > 3" class="mt-4 flex flex-wrap gap-2">
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
        </div>
    </AppLayout>
</template>
