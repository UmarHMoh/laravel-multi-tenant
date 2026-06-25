<script setup>
import StorefrontHeader from '@/components/tenant/StorefrontHeader.vue';

const props = defineProps({
    store: { type: Object, default: () => ({}) },
    theme: { type: Object, default: () => ({}) },
    themeSettings: { type: Object, default: () => ({}) },
});
</script>

<template>
    <div class="min-h-screen bg-white text-gray-900">
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

                <form class="mt-6 grid gap-4">
                    <input
                        class="rounded-lg border border-gray-300 px-4 py-3"
                        type="text"
                        placeholder="Your name"
                    />

                    <input
                        class="rounded-lg border border-gray-300 px-4 py-3"
                        type="email"
                        placeholder="Email address"
                    />

                    <textarea
                        class="min-h-32 rounded-lg border border-gray-300 px-4 py-3"
                        placeholder="Message"
                    ></textarea>

                    <button
                        class="rounded-lg bg-gray-900 px-5 py-3 font-semibold text-white"
                        type="button"
                    >
                        Send message
                    </button>
                </form>
            </section>
        </main>

        <footer class="border-t border-gray-200 px-4 py-8 text-center text-sm text-gray-500">
            {{ props.themeSettings?.footer?.text || props.theme?.settings?.footer?.text || 'Powered by your store.' }}
        </footer>
    </div>
</template>
