<script setup>
import StorefrontHeader from '@/components/tenant/StorefrontHeader.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    store: { type: Object, default: () => ({}) },
    theme: { type: Object, default: () => ({}) },
    page: { type: Object, default: () => ({}) },
    themeSettings: { type: Object, default: () => ({}) },
})

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
    source_page: 'contact',
})

function submitContactForm() {
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => form.reset('name', 'email', 'phone', 'subject', 'message'),
    })
}
</script>

<template>
    <div class="min-h-screen bg-white text-gray-900" data-s97-contact-form-working-backend>
        <StorefrontHeader
            :store="props.store || {}"
            :header="props.themeSettings?.header || props.theme?.settings?.header || {}"
        />

        <main class="px-4 py-12">
            <section
                data-contact-form-foundation
                class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 shadow-sm"
            >
                <p class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    Contact us
                </p>

                <h1 class="mt-2 text-3xl font-bold text-gray-900">
                    Send us a message
                </h1>

                <p class="mt-3 text-gray-600">
                    Send us a message and we will get back to you.
                </p>

                <form class="mt-6 grid gap-4" data-contact-message-form @submit.prevent="submitContactForm">
                    <div>
                        <input
                            v-model="form.name"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3"
                            type="text"
                            placeholder="Your name"
                            data-contact-name
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <input
                            v-model="form.email"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3"
                            type="email"
                            placeholder="Email address"
                            data-contact-email
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <input
                        v-model="form.phone"
                        class="rounded-lg border border-gray-300 px-4 py-3"
                        type="text"
                        placeholder="Phone number optional"
                        data-contact-phone
                    />

                    <input
                        v-model="form.subject"
                        class="rounded-lg border border-gray-300 px-4 py-3"
                        type="text"
                        placeholder="Subject optional"
                        data-contact-subject
                    />

                    <div>
                        <textarea
                            v-model="form.message"
                            class="min-h-32 w-full rounded-lg border border-gray-300 px-4 py-3"
                            placeholder="Message"
                            data-contact-message
                        ></textarea>
                        <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">{{ form.errors.message }}</p>
                    </div>

                    <button
                        class="rounded-lg bg-gray-900 px-5 py-3 font-semibold text-white disabled:opacity-50"
                        type="submit"
                        data-contact-submit
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Sending...' : 'Send message' }}
                    </button>
                </form>
            </section>
        </main>

        <footer class="border-t border-gray-200 px-4 py-8 text-center text-sm text-gray-500">
            {{ props.themeSettings?.footer?.text || props.theme?.settings?.footer?.text || 'Powered by your store.' }}
        </footer>
    </div>
</template>
