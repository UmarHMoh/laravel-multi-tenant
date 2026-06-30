<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  messages: { type: Object, default: () => ({ data: [] }) },
  stats: { type: Object, default: () => ({}) },
  filters: { type: Object, default: () => ({}) },
  contact_messages_table_ready: { type: Boolean, default: false },
})

function setStatus(status) {
  router.get('/manage/contact-messages', { status: status || undefined }, {
    preserveScroll: true,
    preserveState: true,
  })
}

function markRead(message) {
  router.post(`/manage/contact-messages/${message.id}/read`, {}, {
    preserveScroll: true,
  })
}

function deleteMessage(message) {
  if (!confirm('Delete this contact message?')) return

  router.delete(`/manage/contact-messages/${message.id}`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Contact Messages" />

  <AppLayout>
    <div class="space-y-6 p-4 sm:p-6" data-s98-contact-messages-admin>
      <div class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
        <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">Contact</p>
        <div class="mt-2 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Contact messages</h1>
            <p class="mt-1 text-sm text-gray-600">View, mark as read, and delete messages submitted from the contact form.</p>
          </div>

          <Link href="/manage/website" class="inline-flex min-h-10 items-center justify-center rounded-lg border px-4 py-2 text-sm font-semibold text-gray-700">
            Website pages
          </Link>
        </div>
      </div>

      <div v-if="!contact_messages_table_ready" class="rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-sm text-yellow-800">
        Contact messages table is not ready yet. Run tenant migrations.
      </div>

      <div class="grid gap-4 sm:grid-cols-3">
        <button type="button" class="rounded-2xl border bg-white p-5 text-left shadow-sm" @click="setStatus('')">
          <p class="text-sm text-gray-500">Total</p>
          <p class="mt-2 text-2xl font-bold">{{ stats.total || 0 }}</p>
        </button>

        <button type="button" class="rounded-2xl border bg-white p-5 text-left shadow-sm" @click="setStatus('unread')">
          <p class="text-sm text-gray-500">Unread</p>
          <p class="mt-2 text-2xl font-bold">{{ stats.unread || 0 }}</p>
        </button>

        <button type="button" class="rounded-2xl border bg-white p-5 text-left shadow-sm" @click="setStatus('read')">
          <p class="text-sm text-gray-500">Read</p>
          <p class="mt-2 text-2xl font-bold">{{ stats.read || 0 }}</p>
        </button>
      </div>

      <section class="rounded-2xl border bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Inbox</h2>
          <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
            {{ filters.status || 'all' }}
          </span>
        </div>

        <div class="space-y-3">
          <article
            v-for="message in messages.data || []"
            :key="message.id"
            class="rounded-xl border p-4"
            :class="message.is_read ? 'bg-white' : 'bg-blue-50'"
            data-contact-message-card
          >
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <h3 class="font-semibold text-gray-900">{{ message.subject || 'No subject' }}</h3>
                  <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="message.is_read ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-800'">
                    {{ message.is_read ? 'Read' : 'Unread' }}
                  </span>
                </div>

                <p class="mt-1 text-sm text-gray-600">{{ message.name }} · {{ message.email }}</p>
                <p v-if="message.phone" class="mt-1 text-sm text-gray-500">{{ message.phone }}</p>
                <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-gray-700">{{ message.message }}</p>
                <p class="mt-3 text-xs text-gray-400">{{ message.created_at }}</p>
              </div>

              <div class="flex shrink-0 gap-2">
                <button
                  v-if="!message.is_read"
                  type="button"
                  class="rounded-lg border px-3 py-2 text-sm font-semibold"
                  data-contact-message-mark-read
                  @click="markRead(message)"
                >
                  Mark read
                </button>

                <button
                  type="button"
                  class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700"
                  data-contact-message-delete
                  @click="deleteMessage(message)"
                >
                  Delete
                </button>
              </div>
            </div>
          </article>

          <div v-if="!(messages.data || []).length" class="rounded-xl border border-dashed p-8 text-center text-sm text-gray-500">
            No contact messages yet.
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
