<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { computed } from 'vue'

const props = defineProps({
  theme: { type: Object, required: true },
  homepage: { type: Object, required: false },
  pages: { type: Array, default: () => [] },
  pageStats: { type: Object, default: () => ({}) },
  builderLimits: { type: Object, default: () => ({}) },
})

const form = useForm({
  title: '',
  handle: '',
})

const canCreatePage = computed(() => Boolean(props.builderLimits?.can_create_page))

function createPage() {
  form.post('/manage/website/pages', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <Head title="Website Pages" />

  <AppLayout>
    <div class="space-y-6 p-4 sm:p-6">
      <div class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
        <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Website Builder</p>

        <div class="mt-2 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Website pages</h1>
            <p class="mt-1 max-w-3xl text-sm text-gray-600">
              Manage storefront pages before opening the full-screen editor. Each page has a draft version and a published version.
            </p>
          </div>

          <Link
            href="/manage/website/homepage/editor"
            class="inline-flex min-h-11 items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
          >
            Open homepage editor
          </Link>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border bg-white p-5 shadow-sm">
          <p class="text-sm text-gray-500">Theme</p>
          <p class="mt-2 text-xl font-bold text-gray-900">{{ theme.name }}</p>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
          <p class="text-sm text-gray-500">Total pages</p>
          <p class="mt-2 text-xl font-bold text-gray-900">{{ pageStats.total_pages || 0 }}</p>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
          <p class="text-sm text-gray-500">Published pages</p>
          <p class="mt-2 text-xl font-bold text-gray-900">{{ pageStats.published_pages || 0 }}</p>
        </div>

        <div class="rounded-2xl border bg-white p-5 shadow-sm">
          <p class="text-sm text-gray-500">Page limit</p>
          <p class="mt-2 text-xl font-bold text-gray-900">
            {{ builderLimits.allow_multiple_builder_pages ? 'Unlimited' : builderLimits.max_builder_pages }}
          </p>
        </div>
      </div>

      <div class="grid gap-6 xl:grid-cols-12">
        <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6 xl:col-span-8">
          <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h2 class="text-lg font-semibold text-gray-900">Pages</h2>
              <p class="text-sm text-gray-500">Open a page to design it in the editor.</p>
            </div>

            <span class="w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
              Draft + published workflow
            </span>
          </div>

          <div class="space-y-3">
            <article
              v-for="page in pages"
              :key="page.id"
              class="rounded-xl border p-4 transition hover:bg-gray-50"
            >
              <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                  <div class="flex flex-wrap items-center gap-2">
                    <h3 class="text-base font-semibold text-gray-900">{{ page.title }}</h3>
                    <span
                      class="rounded-full px-2.5 py-1 text-xs font-semibold"
                      :class="page.type === 'home' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700'"
                    >
                      {{ page.type === 'home' ? 'Homepage' : 'Custom page' }}
                    </span>
                    <span
                      class="rounded-full px-2.5 py-1 text-xs font-semibold"
                      :class="page.is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                    >
                      {{ page.is_published ? 'Published' : 'Draft only' }}
                    </span>
                  </div>

                  <p class="mt-2 text-sm text-gray-500">
                    /{{ page.handle }} · {{ page.template }} template
                  </p>

                  <p class="mt-1 text-xs text-gray-500">
                    Draft sections: {{ page.draft_section_count }} · Published sections: {{ page.published_section_count }}
                  </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                  <Link
                    :href="page.editor_url"
                    class="inline-flex min-h-10 items-center justify-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                  >
                    Open Editor
                  </Link>

                  <a
                    :href="page.type === 'home' ? '/' : `/pages/${page.handle}`"
                    target="_blank"
                    class="inline-flex min-h-10 items-center justify-center rounded-lg border px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-white"
                  >
                    View
                  </a>
                </div>
              </div>
            </article>

            <div v-if="!pages.length" class="rounded-xl border border-dashed p-8 text-center text-sm text-gray-500">
              No pages yet.
            </div>
          </div>
        </section>

        <aside class="space-y-6 xl:col-span-4">
          <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-semibold text-gray-900">Create page</h2>
            <p class="mt-1 text-sm text-gray-500">Create a page, then open it in the editor.</p>

            <form class="mt-4 space-y-4" @submit.prevent="createPage">
              <div>
                <label class="text-sm font-semibold text-gray-800">Page title</label>
                <input
                  v-model="form.title"
                  type="text"
                  class="mt-1 min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-gray-900"
                  placeholder="About us"
                  :disabled="!canCreatePage"
                />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
              </div>

              <div>
                <label class="text-sm font-semibold text-gray-800">URL handle</label>
                <input
                  v-model="form.handle"
                  type="text"
                  class="mt-1 min-h-11 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-gray-900"
                  placeholder="about-us"
                  :disabled="!canCreatePage"
                />
                <p class="mt-1 text-xs text-gray-500">Leave blank to auto-generate from the title.</p>
                <p v-if="form.errors.handle" class="mt-1 text-sm text-red-600">{{ form.errors.handle }}</p>
              </div>

              <button
                type="submit"
                class="min-h-11 w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800 disabled:opacity-50"
                :disabled="form.processing || !canCreatePage"
              >
                {{ form.processing ? 'Creating...' : 'Create page' }}
              </button>

              <p v-if="!canCreatePage" class="rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800">
                {{ builderLimits.message }}
              </p>
            </form>
          </section>

          <section class="rounded-2xl border bg-slate-50 p-5 text-sm text-slate-700 sm:p-6">
            <h2 class="font-semibold">S42 foundation</h2>
            <p class="mt-2">
              This stage makes /manage/website a page manager. The full editor is now opened per page.
            </p>
          </section>
        </aside>
      </div>
    </div>
  </AppLayout>
</template>
