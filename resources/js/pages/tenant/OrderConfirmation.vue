<script setup>
import StorefrontHeader from '@/components/tenant/StorefrontHeader.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
  order: { type: Object, required: true },
  orderItems: { type: Array, default: () => [] },
  store: { type: Object, default: () => ({}) },
  platformTransaction: { type: Object, default: null },
})

const currency = props.store?.currency || props.order?.payment_metadata?.currency || 'USD'

const money = (value) => {
  const number = Number(value || 0)
  return `${currency} ${number.toFixed(2)}`
}

const orderTimelineSteps = [
  { key: 'received', label: 'Order received' },
  { key: 'payment', label: 'Payment review' },
  { key: 'processing', label: 'Processing' },
  { key: 'delivery', label: 'Delivery' },
]

const statusClass = (status) => {
  const value = String(status || '').toLowerCase()

  if (['paid', 'completed', 'processing', 'delivered'].includes(value)) {
    return 'bg-green-100 text-green-800'
  }

  if (['pending', 'requested'].includes(value)) {
    return 'bg-yellow-100 text-yellow-800'
  }

  if (['failed', 'cancelled', 'canceled'].includes(value)) {
    return 'bg-red-100 text-red-800'
  }

  return 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <Head :title="`Order ${order.order_number || order.id}`" />

  <main class="min-h-screen bg-gray-50" data-s104-order-confirmation-polish>
    <StorefrontHeader :store="$page.props.store || {}" :cart-items="$page.props.cartItems || $page.props.orderItems || []" />
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-6">
        <p class="text-sm font-semibold uppercase tracking-wide text-green-700">Order placed</p>
        <h1 class="mt-2 text-3xl font-bold text-gray-900">
          Thank you for your order.
        </h1>
        <p class="mt-2 text-gray-700">
          Your order
          <span class="font-semibold">#{{ order.order_number || order.id }}</span>
          was received by {{ store.name || 'the store' }}.
        </p>
      </div>

      <section class="mb-6 rounded-2xl border bg-white p-6 shadow-sm" data-order-timeline>
        <h2 class="text-lg font-semibold text-gray-900">Order timeline</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-4">
          <div v-for="step in orderTimelineSteps" :key="step.key" class="rounded-xl border bg-gray-50 p-3 text-sm">
            <p class="font-semibold text-gray-900">{{ step.label }}</p>
            <p class="mt-1 text-xs text-gray-500">Status: {{ order.status || 'pending' }}</p>
          </div>
        </div>
      </section>

      <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
          <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-xl font-semibold text-gray-900">Order items</h2>
                <p class="text-sm text-gray-500">{{ orderItems.length }} item(s)</p>
              </div>

              <div class="flex flex-wrap gap-2">
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(order.status)">
                  Order: {{ order.status || 'pending' }}
                </span>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(order.payment_status)">
                  Payment: {{ order.payment_status || 'pending' }}
                </span>
              </div>
            </div>

            <div class="divide-y">
              <div v-for="item in orderItems" :key="item.id" class="flex items-start justify-between gap-4 py-4">
                <div>
                  <p class="font-medium text-gray-900">
                    {{ item.product_name || item.product?.name || 'Product' }}
                  </p>
                  <p class="text-sm text-gray-500">Qty {{ item.quantity }} × {{ money(item.price) }}</p>
                </div>
                <p class="font-semibold text-gray-900">{{ money(item.subtotal) }}</p>
              </div>
            </div>
          </section>

          <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xl font-semibold text-gray-900">Delivery details</h2>

            <div class="grid gap-6 sm:grid-cols-2">
              <div>
                <p class="text-sm font-semibold text-gray-700">Billing</p>
                <div class="mt-2 text-sm text-gray-600">
                  <p>{{ order.billing_name }}</p>
                  <p>{{ order.billing_email }}</p>
                  <p>{{ order.billing_phone }}</p>
                  <p>{{ order.billing_address }}</p>
                  <p>{{ order.billing_city }}, {{ order.billing_state }}</p>
                  <p>{{ order.billing_country }} {{ order.billing_zipcode }}</p>
                </div>
              </div>

              <div>
                <p class="text-sm font-semibold text-gray-700">Shipping</p>
                <div class="mt-2 text-sm text-gray-600">
                  <p>{{ order.shipping_name || order.billing_name }}</p>
                  <p>{{ order.shipping_address }}</p>
                  <p>{{ order.shipping_city }}, {{ order.shipping_state }}</p>
                  <p>{{ order.shipping_country }} {{ order.shipping_zipcode }}</p>
                </div>
              </div>
            </div>
          </section>
        </div>

        <aside class="space-y-6">
          <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xl font-semibold text-gray-900">Summary</h2>

            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Subtotal</span>
                <span class="font-medium">{{ money(order.subtotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Shipping</span>
                <span class="font-medium">{{ money(order.shipping_cost) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Tax</span>
                <span class="font-medium">{{ money(order.tax_amount) }}</span>
              </div>
              <div class="flex justify-between border-t pt-3 text-base">
                <span class="font-semibold text-gray-900">Total</span>
                <span class="font-bold text-gray-900">{{ money(order.total) }}</span>
              </div>
            </div>
          </section>

          <section class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xl font-semibold text-gray-900">Payment</h2>

            <div class="space-y-2 text-sm text-gray-600">
              <p><span class="font-medium text-gray-800">Method:</span> {{ order.payment_method || 'N/A' }}</p>
              <p><span class="font-medium text-gray-800">Status:</span> {{ order.payment_status || 'pending' }}</p>
              <p v-if="platformTransaction">
                <span class="font-medium text-gray-800">Reference:</span>
                {{ platformTransaction.provider_transaction_id || platformTransaction.reference || platformTransaction.id }}
              </p>
            </div>
          </section>

          <div class="flex flex-col gap-3">
            <button type="button" class="rounded-xl border bg-white px-5 py-3 text-center text-sm font-semibold text-gray-800 hover:bg-gray-50" data-order-print-button @click="window.print()">
              Print receipt
            </button>
            <a href="/" class="rounded-xl bg-gray-900 px-5 py-3 text-center text-sm font-semibold text-white hover:bg-gray-800">
              Continue shopping
            </a>
            <a href="/orders" class="rounded-xl border bg-white px-5 py-3 text-center text-sm font-semibold text-gray-800 hover:bg-gray-50">
              View my orders
            </a>
          </div>
        </aside>
      </div>
    </div>
  </main>
</template>

<!-- S104 order confirmation polish: data-s104-order-confirmation-polish data-order-timeline data-order-print-button orderTimelineSteps -->
