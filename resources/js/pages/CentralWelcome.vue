<script lang="ts">
import { Link, Head } from '@inertiajs/vue3';

export default {
  components: {
    Link,
    Head
  },
  props: {
    tenants: Array
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <Head>
      <title>Multi-Tenant eCommerce Platform - Central Hub</title>
      <meta name="description" content="Central dashboard for our multi-tenant eCommerce platform" />
    </Head>

    <header class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6">
        <div>
          <h1 class="text-3xl font-bold">Multi-Tenant eCommerce Platform</h1>
          <p class="mt-1 text-sm text-slate-500">Central hub for your SaaS commerce platform.</p>
        </div>

        <div class="flex gap-3">
          <Link href="/central" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Central Dashboard
          </Link>
          <Link href="/central/tenants" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
            Manage Tenants
          </Link>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">
      <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold">Welcome to your eCommerce Platform</h2>
        <p class="mt-2 text-slate-600">This is the central domain of the multi-tenant eCommerce application.</p>

        <div class="mt-8">
          <h3 class="text-lg font-medium">Available Stores</h3>

          <div v-if="tenants.length > 0" class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div v-for="tenant in tenants" :key="tenant.id" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
              <h4 class="text-lg font-bold">{{ tenant.name }}</h4>
              <p class="text-sm text-slate-500">{{ tenant.email }}</p>

              <div class="mt-4 flex gap-3">
                <a :href="'http://' + tenant.id + '.localhost:8000/home'" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                  Visit store →
                </a>

                <Link :href="'/central/tenants/' + tenant.id" class="text-sm font-medium text-slate-700 hover:text-slate-900">
                  View tenant
                </Link>
              </div>
            </div>
          </div>

          <div v-else class="mt-4 text-slate-500">
            No stores available yet.
          </div>
        </div>
      </section>
    </main>
  </div>
</template>
