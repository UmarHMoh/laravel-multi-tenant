<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

defineOptions({ layout: CentralLayout });

const props = defineProps({
    settings: Object,
});

const isEditing = ref(false);

const form = useForm({
    platform_name: props.settings?.platform_name || 'Central Admin',
    owner_email: props.settings?.owner_email || '',
    subscription_currency: props.settings?.subscription_currency || 'TTD',
    renewal_grace_days: props.settings?.renewal_grace_days ?? 0,
    auto_deactivate_overdue_tenants: props.settings?.auto_deactivate_overdue_tenants ?? true,
    login_heading: props.settings?.login_heading || 'Central Admin Login',
    login_subheading: props.settings?.login_subheading || '',
});

watch(() => props.settings, (settings) => {
    if (!settings) return;

    form.platform_name = settings.platform_name || 'Central Admin';
    form.owner_email = settings.owner_email || '';
    form.subscription_currency = settings.subscription_currency || 'TTD';
    form.renewal_grace_days = settings.renewal_grace_days ?? 0;
    form.auto_deactivate_overdue_tenants = settings.auto_deactivate_overdue_tenants ?? true;
    form.login_heading = settings.login_heading || 'Central Admin Login';
    form.login_subheading = settings.login_subheading || '';
}, { deep: true });

function submit() {
    form.put('/central/settings', {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
}

function cancelEdit() {
    form.platform_name = props.settings?.platform_name || 'Central Admin';
    form.owner_email = props.settings?.owner_email || '';
    form.subscription_currency = props.settings?.subscription_currency || 'TTD';
    form.renewal_grace_days = props.settings?.renewal_grace_days ?? 0;
    form.auto_deactivate_overdue_tenants = props.settings?.auto_deactivate_overdue_tenants ?? true;
    form.login_heading = props.settings?.login_heading || 'Central Admin Login';
    form.login_subheading = props.settings?.login_subheading || '';

    isEditing.value = false;
}
</script>

<template>
    <Head title="Portal Settings" />

    <div class="mx-auto max-w-5xl space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                <div>
                    <h1 class="text-2xl font-bold">Portal Settings</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Manage platform identity, login text, and subscription renewal rules.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button
                        v-if="!isEditing"
                        @click="isEditing = true"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Edit Settings
                    </button>

                    <button
                        v-if="isEditing"
                        type="button"
                        @click="cancelEdit"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </section>

        <section v-if="!isEditing" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Saved Portal Settings</h2>

            <dl class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <dt class="text-sm text-slate-500">Platform Name</dt>
                    <dd class="mt-1 font-medium">{{ settings.platform_name || '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Owner Email</dt>
                    <dd class="mt-1 font-medium">{{ settings.owner_email || '—' }}</dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Subscription Currency</dt>
                    <dd class="mt-1 font-medium">{{ settings.subscription_currency || 'TTD' }}</dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Renewal Grace Days</dt>
                    <dd class="mt-1 font-medium">{{ settings.renewal_grace_days ?? 0 }} day(s)</dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Auto Deactivate Overdue Tenants</dt>
                    <dd class="mt-1">
                        <span
                            class="rounded-full px-3 py-1 text-xs font-medium"
                            :class="settings.auto_deactivate_overdue_tenants ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                        >
                            {{ settings.auto_deactivate_overdue_tenants ? 'Enabled' : 'Disabled' }}
                        </span>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-slate-500">Login Heading</dt>
                    <dd class="mt-1 font-medium">{{ settings.login_heading || '—' }}</dd>
                </div>

                <div class="md:col-span-2">
                    <dt class="text-sm text-slate-500">Login Subheading</dt>
                    <dd class="mt-1 font-medium">{{ settings.login_subheading || '—' }}</dd>
                </div>
            </dl>
        </section>

        <form v-if="isEditing" @submit.prevent="submit" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Edit Portal Settings</h2>

            <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Platform Name</label>
                    <input v-model="form.platform_name" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                    <p v-if="form.errors.platform_name" class="mt-1 text-sm text-red-600">{{ form.errors.platform_name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Owner Email</label>
                    <input v-model="form.owner_email" type="email" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                    <p v-if="form.errors.owner_email" class="mt-1 text-sm text-red-600">{{ form.errors.owner_email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Subscription Currency</label>
                    <input v-model="form.subscription_currency" maxlength="3" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 uppercase" />
                    <p v-if="form.errors.subscription_currency" class="mt-1 text-sm text-red-600">{{ form.errors.subscription_currency }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Renewal Grace Days</label>
                    <input v-model="form.renewal_grace_days" type="number" min="0" max="365" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                    <p v-if="form.errors.renewal_grace_days" class="mt-1 text-sm text-red-600">{{ form.errors.renewal_grace_days }}</p>
                </div>

                <div class="rounded-lg border border-slate-200 p-4 md:col-span-2">
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input v-model="form.auto_deactivate_overdue_tenants" type="checkbox" />
                        Automatically deactivate overdue tenants
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Login Heading</label>
                    <input v-model="form.login_heading" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                    <p v-if="form.errors.login_heading" class="mt-1 text-sm text-red-600">{{ form.errors.login_heading }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Login Subheading</label>
                    <textarea v-model="form.login_subheading" rows="3" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>
                    <p v-if="form.errors.login_subheading" class="mt-1 text-sm text-red-600">{{ form.errors.login_subheading }}</p>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save Portal Settings' }}
                </button>
            </div>
        </form>
    </div>
</template>
