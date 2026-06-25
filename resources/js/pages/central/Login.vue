<script setup lang="ts">
import FlashMessages from '@/components/FlashMessages.vue';
import InputError from '@/components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/central/login', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Central Admin Login" />

    <div class="min-h-screen bg-slate-950 text-white flex items-center justify-center px-4">
        <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-8 shadow-2xl">
            <FlashMessages />

            <div class="mb-8 text-center">
                <p class="text-sm uppercase tracking-[0.3em] text-slate-400">
                    Central Admin
                </p>

                <h1 class="mt-3 text-3xl font-bold">
                    Login
                </h1>

                <p class="mt-2 text-sm text-slate-400">
                    Access the SaaS control panel.
                </p>
            </div>

            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-300">
                        Email
                    </label>

                    <input
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-white outline-none focus:border-white"
                        placeholder="admin@example.com"
                    />

                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-300">
                        Password
                    </label>

                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-white outline-none focus:border-white"
                        placeholder="••••••••"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full rounded-lg bg-white px-4 py-3 font-semibold text-slate-950 transition hover:bg-slate-200 disabled:opacity-60"
                >
                    {{ form.processing ? 'Logging in...' : 'Login' }}
                </button>
            </form>
        </div>
    </div>
</template>
