<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import {
  Eye,
  Pencil,
  Plus,
  RefreshCcw,
  Search,
  Tag,
  Trash2,
} from 'lucide-vue-next'

const props = defineProps({
  categories: { type: Object, default: () => ({ data: [] }) },
  filters: { type: Object, default: () => ({}) },
})

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')
const deleteTarget = ref(null)

const rows = computed(() => props.categories?.data || [])

const stats = computed(() => {
  const all = rows.value
  return {
    total: props.categories?.total || all.length,
    active: all.filter((category) => category.is_active).length,
    inactive: all.filter((category) => !category.is_active).length,
    products: all.reduce((sum, category) => sum + Number(category.products_count || 0), 0),
  }
})

function applyFilters() {
  router.get(route('category.index'), {
    search: search.value || undefined,
    status: status.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  })
}

function resetFilters() {
  search.value = ''
  status.value = ''
  applyFilters()
}

function confirmDelete(category) {
  if (Number(category.products_count || 0) > 0) return
  deleteTarget.value = category
}

function deleteCategory() {
  if (!deleteTarget.value) return

  router.delete(route('category.destroy', deleteTarget.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      deleteTarget.value = null
    },
  })
}

watch(status, () => applyFilters())
</script>

<template>
  <Head title="Categories" />

  <AppLayout>
    <div class="min-h-screen bg-slate-50 px-4 py-6 text-slate-900 sm:px-6 lg:px-8" data-s112-categories-ui>
      <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
          <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Catalog</p>
            <h1 class="mt-1 text-3xl font-bold">Categories</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-500">
              Organize your products into storefront categories and control which categories are active.
            </p>
          </div>

          <Link
            :href="route('category.create')"
            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-700"
            data-create-category-button
          >
            <Plus class="h-4 w-4" />
            Create Category
          </Link>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Categories</p>
            <p class="mt-2 text-2xl font-bold">{{ stats.total }}</p>
          </div>

          <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Active</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600">{{ stats.active }}</p>
          </div>

          <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Inactive</p>
            <p class="mt-2 text-2xl font-bold text-slate-500">{{ stats.inactive }}</p>
          </div>

          <div class="rounded-2xl border bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Products Assigned</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600">{{ stats.products }}</p>
          </div>
        </div>

        <section class="rounded-2xl border bg-white shadow-sm">
          <div class="border-b p-4 sm:p-5">
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
              <label class="relative block">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                  v-model="search"
                  type="search"
                  placeholder="Search category name or description"
                  class="min-h-11 w-full rounded-xl border border-slate-300 pl-10 pr-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                  data-category-search
                  @keyup.enter="applyFilters"
                />
              </label>

              <select
                v-model="status"
                class="min-h-11 rounded-xl border border-slate-300 px-3 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                data-category-status-filter
              >
                <option value="">All statuses</option>
                <option value="active">Active only</option>
                <option value="inactive">Inactive only</option>
              </select>

              <button
                type="button"
                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border px-4 text-sm font-semibold hover:bg-slate-50"
                @click="resetFilters"
              >
                <RefreshCcw class="h-4 w-4" />
                Reset
              </button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
              <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                  <th class="px-5 py-3">Category</th>
                  <th class="px-5 py-3">Products</th>
                  <th class="px-5 py-3">Status</th>
                  <th class="px-5 py-3 text-right">Actions</th>
                </tr>
              </thead>

              <tbody class="divide-y divide-slate-100 bg-white">
                <tr v-for="category in rows" :key="category.id" class="hover:bg-slate-50" data-category-row>
                  <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                      <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <Tag class="h-5 w-5" />
                      </div>

                      <div>
                        <p class="font-semibold text-slate-900">{{ category.name }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ category.slug || 'No slug' }}</p>
                        <p v-if="category.description" class="mt-1 line-clamp-1 max-w-xl text-xs text-slate-500">
                          {{ category.description }}
                        </p>
                      </div>
                    </div>
                  </td>

                  <td class="px-5 py-4">
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                      {{ category.products_count || 0 }} products
                    </span>
                  </td>

                  <td class="px-5 py-4">
                    <span
                      class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                      :class="category.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'"
                      data-category-status-badge
                    >
                      {{ category.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>

                  <td class="px-5 py-4">
                    <div class="flex justify-end gap-2">
                      <Link
                        :href="route('category.show', category.id)"
                        class="inline-flex min-h-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold hover:bg-white"
                        title="View category"
                        data-category-view
                      >
                        <Eye class="h-4 w-4" />
                      </Link>

                      <Link
                        :href="route('category.edit', category.id)"
                        class="inline-flex min-h-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold hover:bg-white"
                        title="Edit category"
                        data-category-edit
                      >
                        <Pencil class="h-4 w-4" />
                      </Link>

                      <button
                        type="button"
                        class="inline-flex min-h-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold"
                        :class="Number(category.products_count || 0) > 0 ? 'cursor-not-allowed text-slate-300' : 'text-red-600 hover:bg-red-50'"
                        :disabled="Number(category.products_count || 0) > 0"
                        :title="Number(category.products_count || 0) > 0 ? 'Remove products before deleting this category.' : 'Delete category'"
                        data-category-delete
                        @click="confirmDelete(category)"
                      >
                        <Trash2 class="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>

                <tr v-if="!rows.length">
                  <td colspan="4" class="px-5 py-16 text-center">
                    <div class="mx-auto max-w-sm">
                      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <Tag class="h-6 w-6" />
                      </div>
                      <p class="mt-4 font-semibold text-slate-900">No categories found</p>
                      <p class="mt-1 text-sm text-slate-500">Create a category or adjust your search filters.</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="categories?.links?.length > 3" class="flex flex-wrap gap-2 border-t p-4">
            <Link
              v-for="link in categories.links"
              :key="link.label"
              :href="link.url || '#'"
              v-html="link.label"
              class="rounded-lg border px-3 py-2 text-sm"
              :class="[
                link.active ? 'bg-slate-900 text-white' : 'bg-white text-slate-700',
                !link.url ? 'pointer-events-none opacity-50' : 'hover:bg-slate-50',
              ]"
            />
          </div>
        </section>
      </div>

      <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
          <h2 class="text-lg font-bold">Delete category?</h2>
          <p class="mt-2 text-sm text-slate-500">
            This will permanently delete “{{ deleteTarget.name }}”. This cannot be undone.
          </p>

          <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="rounded-xl border px-4 py-2 text-sm font-semibold" @click="deleteTarget = null">
              Cancel
            </button>
            <button type="button" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white" @click="deleteCategory">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
