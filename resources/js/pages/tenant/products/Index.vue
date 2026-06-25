<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, computed } from 'vue'

const props = defineProps({
    products: Object,
    stats: Object,
    filters: Object,
    stockStatusOptions: Array,
})

const form = reactive({
    search: props.filters?.search || '',
    stock_status: props.filters?.stock_status || '',
})

const productRows = computed(() => props.products?.data || [])

function money(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'TTD',
    }).format(Number(value || 0))
}

function stockBadge(product) {
    const stock = Number(product.stock || 0)

    if (stock <= 0) {
        return {
            label: 'Out of Stock',
            class: 'bg-red-100 text-red-800',
        }
    }

    if (stock <= 5) {
        return {
            label: 'Low Stock',
            class: 'bg-amber-100 text-amber-800',
        }
    }

    return {
        label: 'Available',
        class: 'bg-emerald-100 text-emerald-800',
    }
}

function applyFilters() {
    router.get('/manage/product', {
        search: form.search || undefined,
        stock_status: form.stock_status || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

function clearFilters() {
    form.search = ''
    form.stock_status = ''

    router.get('/manage/product', {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}
</script>

<template>
    <Head title="Products" />

    <AppLayout>
        <div class="space-y-6 p-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Products</h1>
                    <p class="text-sm text-slate-500">Manage products, prices, visibility, and inventory stock.</p>
                </div>

                <Link href="/manage/product/create" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">
                    Add Product
                </Link>
            </div>

            <div class="grid gap-4 md:grid-cols-5">
                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Total Products</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.total_products || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Active</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.active_products || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Inventory Units</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.inventory_units || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Low Stock</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.low_stock || 0 }}</p>
                </div>

                <div class="rounded-xl border bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">Out of Stock</p>
                    <p class="mt-2 text-2xl font-bold">{{ stats?.out_of_stock || 0 }}</p>
                </div>
            </div>

            <div class="rounded-xl border bg-white p-5 shadow-sm">
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium">Search Products</label>
                        <input
                            v-model="form.search"
                            type="search"
                            placeholder="Name, SKU, or description"
                            class="mt-1 w-full rounded-lg border px-3 py-2"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Stock Status</label>
                        <select v-model="form.stock_status" class="mt-1 w-full rounded-lg border px-3 py-2" @change="applyFilters">
                            <option value="">All</option>
                            <option v-for="option in stockStatusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end gap-3">
                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white" @click="applyFilters">
                            Filter
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
                                <th class="px-4 py-3">Product</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Price</th>
                                <th class="px-4 py-3">Stock</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Visibility</th>
                                <th class="px-4 py-3">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="product in productRows" :key="product.id" class="border-b">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">{{ product.name }}</div>
                                    <div class="text-xs text-slate-500">SKU: {{ product.sku || '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ product.category?.name || '—' }}
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ money(product.price) }}</td>
                                <td class="px-4 py-3 font-semibold">{{ product.stock || 0 }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="stockBadge(product).class">
                                        {{ stockBadge(product).label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="product.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'"
                                    >
                                        {{ product.is_active ? 'Active' : 'Hidden' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <Link :href="`/manage/product/${product.id}/edit`" class="rounded-lg border px-3 py-1.5 text-sm">
                                        Edit
                                    </Link>
                                </td>
                            </tr>

                            <tr v-if="!productRows.length">
                                <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                                    No products found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="products?.links?.length > 3" class="flex flex-wrap gap-2 p-4">
                    <Link
                        v-for="link in products.links"
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
