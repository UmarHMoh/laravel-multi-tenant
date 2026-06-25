<script setup>
import StorefrontHeader from '@/components/tenant/StorefrontHeader.vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  orders: { type: Object, required: true },
  store: { type: Object, default: () => ({}) },
})

const currency = props.store?.currency || 'USD'

const money = (value) => `${currency} ${Number(value || 0).toFixed(2)}`

const statusClass = (status) => {
  const value = String(status || '').toLowerCase()
  if (['paid', 'completed', 'processing', 'delivered'].includes(value)) return 'bg-green-100 text-green-800'
  if (['pending', 'requested'].includes(value)) return 'bg-yellow-100 text-yellow-800'
  if (['failed', 'cancelled', 'canceled'].includes(value)) return 'bg-red-100 text-red-800'
  return 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <Head title="My Orders" />

  <AppLayout>
    <StorefrontHeader :store="$page.props.store || {}" :cart-items="$page.props.cartItems || $page.props.orderItems || []" />



    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
      <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ store.name || 'Store' }}</p>
          <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
          <p class="mt-1 text-gray-600">Track your recent orders and payment statuses.</p>
        </div>

        <Link :href="route('home')" class="rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">
          Continue shopping
        </Link>
      </div>

      <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div v-if="orders.data.length" class="divide-y">
          <div v-for="order in orders.data" :key="order.id" class="p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
              <div>
                <Link :href="route('orders.confirmation', order.id)" class="text-lg font-semibold text-gray-900 hover:underline">
                  #{{ order.order_number || order.id }}
                </Link>
                <p class="mt-1 text-sm text-gray-500">
                  {{ order.items_count || 0 }} item(s) · {{ new Date(order.created_at).toLocaleString() }}
                </p>
              </div>

              <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(order.status)">
                  {{ order.status || 'pending' }}
                </span>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(order.payment_status)">
                  {{ order.payment_status || 'pending' }}
                </span>
                <span class="text-base font-bold text-gray-900">{{ money(order.total) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="p-10 text-center">
          <h2 class="text-xl font-semibold text-gray-900">No orders yet</h2>
          <p class="mt-2 text-gray-600">Once you place an order, it will appear here.</p>
          <Link :href="route('home')" class="mt-5 inline-flex rounded-xl bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-gray-800">
            Start shopping
          </Link>
        </div>
      </div>

      <div v-if="orders.links?.length > 3" class="mt-6 flex flex-wrap gap-2">
        <Link
          v-for="link in orders.links"
          :key="link.label"
          :href="link.url || ''"
          preserve-scroll
          class="rounded-lg border px-3 py-2 text-sm"
          :class="[
            link.active ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
            !link.url ? 'pointer-events-none opacity-50' : ''
          ]"
          v-html="link.label"
        />
      </div>
    </div>
  </AppLayout>
</template>
