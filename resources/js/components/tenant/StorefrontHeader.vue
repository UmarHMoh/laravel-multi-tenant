<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
  store: { type: Object, default: () => ({}) },
  header: { type: Object, default: () => ({}) },
  cartItemCount: { type: Number, default: 0 },
})

const page = usePage()
const mobileOpen = ref(false)

const headerSettings = computed(() => props.header || {})
const logoText = computed(() =>
  headerSettings.value.logo_text
  || props.store?.store_name
  || props.store?.name
  || props.store?.email
  || 'Storefront'
)

const logoImageUrl = computed(() => headerSettings.value.logo_image_url || '')
const logoPosition = computed(() => headerSettings.value.logo_position || 'left')
const headerLinks = computed(() => {
  const links = Array.isArray(headerSettings.value.links) ? headerSettings.value.links : []

  return links.length ? links : [
    { label: 'Shop', url: '/home' },
    { label: 'Cart', url: '/cart' },
  ]
})

const tenantDashboardUrl = computed(() => '/dashboard')
const loginUrl = computed(() => '/login')
const registerUrl = computed(() => '/register')
const cartUrl = computed(() => '/cart')
const myOrdersUrl = computed(() => '/my-orders')
const logoutUrl = computed(() => '/logout')

const isAuthenticated = computed(() => Boolean(page.props?.auth?.user))

function isCurrent(url) {
  if (typeof window === 'undefined') return false
  return window.location.pathname === url
}
</script>

<template>
  <header data-storefront-header="true" class="sticky top-0 z-40 border-b bg-white/95 backdrop-blur">
    <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3" :class="logoPosition === 'center' ? 'lg:absolute lg:left-1/2 lg:-translate-x-1/2' : ''">
        <Link href="/home" class="flex items-center gap-3">
          <img v-if="logoImageUrl" :src="logoImageUrl" :alt="logoText" class="h-9 w-auto rounded-md object-contain" />
          <span class="text-lg font-black tracking-tight text-gray-950">{{ logoText }}</span>
        </Link>
      </div>

      <nav class="hidden items-center gap-6 text-sm font-semibold text-gray-700 lg:flex">
        <a
          v-for="link in headerLinks"
          :key="`${link.label}-${link.url}`"
          :href="link.url"
          class="hover:text-gray-950"
          :class="isCurrent(link.url) ? 'text-gray-950' : ''"
        >
          {{ link.label }}
        </a>
      </nav>

      <div class="hidden items-center gap-3 lg:flex">
        <Link :href="cartUrl" class="rounded-xl border px-3 py-2 text-sm font-bold">
          Cart<span v-if="cartItemCount"> · {{ cartItemCount }}</span>
        </Link>

        <template v-if="isAuthenticated">
          <Link :href="myOrdersUrl" class="rounded-xl border px-3 py-2 text-sm font-bold">
            My Orders
          </Link>

          <Link :href="tenantDashboardUrl" class="rounded-xl bg-gray-950 px-4 py-2 text-sm font-bold text-white">
            Dashboard
          </Link>

          <Link :href="logoutUrl" method="post" as="button" class="rounded-xl border border-red-200 px-3 py-2 text-sm font-bold text-red-700">
            Logout
          </Link>
        </template>

        <template v-else>
          <Link :href="loginUrl" class="text-sm font-bold text-gray-700 hover:text-gray-950">Login</Link>
          <Link :href="registerUrl" class="rounded-xl bg-gray-950 px-4 py-2 text-sm font-bold text-white">Register</Link>
        </template>
      </div>

      <button
        type="button"
        class="inline-flex min-h-11 items-center rounded-xl border px-3 text-sm font-black lg:hidden"
        aria-label="Toggle menu"
        @click="mobileOpen = !mobileOpen"
      >
        Menu
      </button>
    </div>

    <div v-if="mobileOpen" class="border-t bg-white px-4 py-4 lg:hidden">
      <nav class="grid gap-2 text-sm font-bold text-gray-800">
        <a
          v-for="link in headerLinks"
          :key="`mobile-${link.label}-${link.url}`"
          :href="link.url"
          class="rounded-xl px-3 py-3 hover:bg-gray-50"
          @click="mobileOpen = false"
        >
          {{ link.label }}
        </a>

        <Link :href="cartUrl" class="rounded-xl px-3 py-3 hover:bg-gray-50">
          Cart<span v-if="cartItemCount"> · {{ cartItemCount }}</span>
        </Link>

        <template v-if="isAuthenticated">
          <Link :href="myOrdersUrl" class="rounded-xl px-3 py-3 hover:bg-gray-50">My Orders</Link>
          <Link :href="tenantDashboardUrl" class="rounded-xl px-3 py-3 hover:bg-gray-50">Dashboard</Link>
          <Link :href="logoutUrl" method="post" as="button" class="rounded-xl px-3 py-3 text-left text-red-700 hover:bg-red-50">Logout</Link>
        </template>

        <template v-else>
          <Link :href="loginUrl" class="rounded-xl px-3 py-3 hover:bg-gray-50">Login</Link>
          <Link :href="registerUrl" class="rounded-xl px-3 py-3 hover:bg-gray-50">Register</Link>
        </template>
      </nav>
    </div>
  </header>
</template>

<!-- S64 permanent authenticated system links: My Orders Logout -->
