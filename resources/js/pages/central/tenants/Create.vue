<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';

defineOptions({ layout: CentralLayout });

import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    tenant_id: '',
    store_name: '',
    store_email: '',
    subdomain: '',
    admin_name: '',
    admin_email: '',
    admin_password: '',
});

function submit() {
    form.post('/central/tenants', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Create Tenant" />

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                <div>
                    <h1 class="text-2xl font-bold">Create Tenant</h1>
                    <p class="text-sm text-slate-500">Create a new store, tenant database, domain, and admin user.</p>
                </div>

                <div class="flex gap-3">
                    <Link href="/central/tenants" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Back to Tenants
                    </Link>
                    <Link href="/central" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                        Central Dashboard
                    </Link>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-4xl px-6 py-8">
            <form @submit.prevent="submit" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="border-b border-slate-200 pb-5">
                    <h2 class="text-lg font-semibold">Store Details</h2>
                    <p class="mt-1 text-sm text-slate-500">These details identify the tenant and public storefront.</p>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tenant ID</label>
                        <input
                            v-model="form.tenant_id"
                            type="text"
                            placeholder="awrah"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p class="mt-1 text-xs text-slate-500">Used internally and for database naming. Example: awrah</p>
                        <p v-if="form.errors.tenant_id" class="mt-1 text-sm text-red-600">{{ form.errors.tenant_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Store Name</label>
                        <input
                            v-model="form.store_name"
                            type="text"
                            placeholder="Awrah Store"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.store_name" class="mt-1 text-sm text-red-600">{{ form.errors.store_name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Store Email</label>
                        <input
                            v-model="form.store_email"
                            type="email"
                            placeholder="support@awrah.com"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.store_email" class="mt-1 text-sm text-red-600">{{ form.errors.store_email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Store Subdomain</label>
                        <input
                            v-model="form.subdomain"
                            type="text"
                            placeholder="awrah"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p class="mt-1 text-xs text-slate-500">
                            Local storefront: {{ form.subdomain || 'store' }}.localhost:8000
                        </p>
                        <p v-if="form.errors.subdomain" class="mt-1 text-sm text-red-600">{{ form.errors.subdomain }}</p>
                    </div>
                </div>

                <div class="mt-8 border-b border-slate-200 pb-5">
                    <h2 class="text-lg font-semibold">Tenant Admin User</h2>
                    <p class="mt-1 text-sm text-slate-500">This user will be created inside the new tenant database.</p>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Admin Name</label>
                        <input
                            v-model="form.admin_name"
                            type="text"
                            placeholder="Admin User"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.admin_name" class="mt-1 text-sm text-red-600">{{ form.errors.admin_name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Admin Email</label>
                        <input
                            v-model="form.admin_email"
                            type="email"
                            placeholder="admin@awrah.com"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.admin_email" class="mt-1 text-sm text-red-600">{{ form.errors.admin_email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Admin Password</label>
                        <input
                            v-model="form.admin_password"
                            type="password"
                            placeholder="password"
                            class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        />
                        <p v-if="form.errors.admin_password" class="mt-1 text-sm text-red-600">{{ form.errors.admin_password }}</p>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3">
                    <Link href="/central/tenants" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Tenant' }}
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
