<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    order: Object,
    statusOptions: Array,
    paymentStatusOptions: Array,
})

const statusForm = useForm({
    status: props.order?.status || 'pending',
})

const paymentForm = useForm({
    payment_status: props.order?.payment_status || 'pending',
})

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

function updateOrderStatus() {
    statusForm.post(`/manage/order/${props.order.id}/status`, {
        preserveScroll: true,
    })
}

function updatePaymentStatus() {
    paymentForm.post(`/manage/order/${props.order.id}/payment-status`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />

    <AppLayout>
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <Link href="/manage/order" class="text-sm text-slate-500 hover:text-slate-900">
                        ← Back to Orders
                    </Link>
                    <h1 class="mt-2 text-2xl font-bold text-slate-900">Order {{ order.order_number }}</h1>
                    <p class="text-sm text-slate-500">
                        Placed {{ order.created_at ? new Date(order.created_at).toLocaleString() : '—' }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full px-3 py-1 text-sm font-semibold capitalize" :class="badgeClass(order.status)">
                        {{ order.status }}
                    </span>
                    <span class="rounded-full px-3 py-1 text-sm font-semibold capitalize" :class="badgeClass(order.payment_status)">
                        {{ order.payment_status }}
                    </span>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-xl border bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold">Items</h2>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="py-2">Product</th>
                                        <th class="py-2">Qty</th>
                                        <th class="py-2">Price</th>
                                        <th class="py-2">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in order.items || []" :key="item.id" class="border-b">
                                        <td class="py-3">
                                            <div class="font-medium">{{ item.product_name || item.product?.name }}</div>
                                            <div class="text-xs text-slate-500">Product ID: {{ item.product_id }}</div>
                                        </td>
                                        <td class="py-3">{{ item.quantity }}</td>
                                        <td class="py-3">{{ money(item.price) }}</td>
                                        <td class="py-3 font-semibold">{{ money(item.subtotal) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <div class="w-full max-w-sm rounded-lg bg-slate-50 p-4">
                                <div class="flex justify-between text-sm">
                                    <span>Total</span>
                                    <span class="font-bold">{{ money(order.total) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold">Payment Details</h2>

                        <dl class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <dt class="text-sm text-slate-500">Method</dt>
                                <dd class="font-medium">{{ order.payment_method || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-slate-500">Provider</dt>
                                <dd class="font-medium">{{ order.payment_provider || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-slate-500">Provider Payment ID</dt>
                                <dd class="font-medium">{{ order.provider_payment_id || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-slate-500">Paid At</dt>
                                <dd class="font-medium">{{ order.paid_at ? new Date(order.paid_at).toLocaleString() : '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold">Update Status</h2>

                        <div class="mt-4">
                            <label class="block text-sm font-medium">Order Status</label>
                            <select v-model="statusForm.status" class="mt-1 w-full rounded-lg border px-3 py-2">
                                <option v-for="status in statusOptions" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                            <button
                                class="mt-3 w-full rounded-lg bg-slate-900 px-4 py-2 text-sm text-white"
                                :disabled="statusForm.processing"
                                @click="updateOrderStatus"
                            >
                                Save Order Status
                            </button>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium">Payment Status</label>
                            <select v-model="paymentForm.payment_status" class="mt-1 w-full rounded-lg border px-3 py-2">
                                <option v-for="status in paymentStatusOptions" :key="status" :value="status">
                                    {{ status }}
                                </option>
                            </select>
                            <button
                                class="mt-3 w-full rounded-lg bg-slate-900 px-4 py-2 text-sm text-white"
                                :disabled="paymentForm.processing"
                                @click="updatePaymentStatus"
                            >
                                Save Payment Status
                            </button>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold">Customer</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div>
                                <dt class="text-slate-500">Name</dt>
                                <dd class="font-medium">{{ order.billing_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Email</dt>
                                <dd class="font-medium">{{ order.billing_email }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Phone</dt>
                                <dd class="font-medium">{{ order.billing_phone }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-xl border bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold">Shipping</h2>
                        <p class="mt-4 text-sm leading-6 text-slate-700">
                            {{ order.shipping_name }}<br />
                            {{ order.shipping_address }}<br />
                            {{ order.shipping_city }}, {{ order.shipping_state }}<br />
                            {{ order.shipping_country }} {{ order.shipping_zipcode }}
                        </p>
                    </div>

                    <div v-if="order.notes" class="rounded-xl border bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold">Notes</h2>
                        <p class="mt-4 text-sm text-slate-700">{{ order.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
