<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    tenant: Object,
    settings: Object,
});

const isEditing = ref(false);

const form = useForm({
    store_name: props.settings?.store_name || props.tenant?.name || '',
    store_email: props.settings?.store_email || props.tenant?.email || '',
    store_description: props.settings?.store_description || '',
    store_phone: props.settings?.store_phone || '',
    store_currency: props.settings?.store_currency || 'TTD',
    business_address: props.settings?.business_address || '',

    delivery_fee: props.settings?.delivery_fee ?? 0,
    cash_on_delivery_enabled: props.settings?.cash_on_delivery_enabled ?? false,

    instagram_url: props.settings?.instagram_url || '',
    facebook_url: props.settings?.facebook_url || '',
    tiktok_url: props.settings?.tiktok_url || '',
    whatsapp_number: props.settings?.whatsapp_number || '',
});

watch(() => props.settings, (settings) => {
    if (!settings) return;

    form.store_name = settings.store_name || props.tenant?.name || '';
    form.store_email = settings.store_email || props.tenant?.email || '';
    form.store_description = settings.store_description || '';
    form.store_phone = settings.store_phone || '';
    form.store_currency = settings.store_currency || 'TTD';
    form.business_address = settings.business_address || '';
    form.delivery_fee = settings.delivery_fee ?? 0;
    form.cash_on_delivery_enabled = settings.cash_on_delivery_enabled ?? false;
    form.instagram_url = settings.instagram_url || '';
    form.facebook_url = settings.facebook_url || '';
    form.tiktok_url = settings.tiktok_url || '';
    form.whatsapp_number = settings.whatsapp_number || '';
}, { deep: true });

function submit() {
    form.put('/manage/store-settings', {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
}

function cancelEdit() {
    form.store_name = props.settings?.store_name || props.tenant?.name || '';
    form.store_email = props.settings?.store_email || props.tenant?.email || '';
    form.store_description = props.settings?.store_description || '';
    form.store_phone = props.settings?.store_phone || '';
    form.store_currency = props.settings?.store_currency || 'TTD';
    form.business_address = props.settings?.business_address || '';
    form.delivery_fee = props.settings?.delivery_fee ?? 0;
    form.cash_on_delivery_enabled = props.settings?.cash_on_delivery_enabled ?? false;
    form.instagram_url = props.settings?.instagram_url || '';
    form.facebook_url = props.settings?.facebook_url || '';
    form.tiktok_url = props.settings?.tiktok_url || '';
    form.whatsapp_number = props.settings?.whatsapp_number || '';

    isEditing.value = false;
}

function money(value) {
    return (props.settings?.store_currency || 'TTD') + ' ' + Number(value || 0).toFixed(2);
}
</script>

<template>
    <Head title="Store Settings" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 px-6 py-8 text-slate-900">
            <div class="mx-auto max-w-5xl space-y-6">
                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                        <div>
                            <h1 class="text-2xl font-bold">Store Settings</h1>
                            <p class="mt-1 text-sm text-slate-500">
                                Manage your storefront information, delivery fee, and customer contact details.
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
                                @click="cancelEdit"
                                type="button"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </section>

                <section v-if="!isEditing" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Saved Store Information</h2>

                    <dl class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <dt class="text-sm text-slate-500">Store Name</dt>
                            <dd class="mt-1 font-medium">{{ settings.store_name || '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">Store Email</dt>
                            <dd class="mt-1 font-medium">{{ settings.store_email || '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">Phone</dt>
                            <dd class="mt-1 font-medium">{{ settings.store_phone || '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">Currency</dt>
                            <dd class="mt-1 font-medium">{{ settings.store_currency || 'TTD' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">Delivery Fee</dt>
                            <dd class="mt-1 font-medium">{{ money(settings.delivery_fee) }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">Cash on Delivery</dt>
                            <dd class="mt-1 font-medium">{{ settings.cash_on_delivery_enabled ? 'Enabled' : 'Disabled' }}</dd>
                        </div>

                        <div class="md:col-span-2">
                            <dt class="text-sm text-slate-500">Description</dt>
                            <dd class="mt-1 font-medium">{{ settings.store_description || '—' }}</dd>
                        </div>

                        <div class="md:col-span-2">
                            <dt class="text-sm text-slate-500">Business Address</dt>
                            <dd class="mt-1 font-medium">{{ settings.business_address || '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">Instagram</dt>
                            <dd class="mt-1 font-medium">{{ settings.instagram_url || '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">Facebook</dt>
                            <dd class="mt-1 font-medium">{{ settings.facebook_url || '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">TikTok</dt>
                            <dd class="mt-1 font-medium">{{ settings.tiktok_url || '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">WhatsApp</dt>
                            <dd class="mt-1 font-medium">{{ settings.whatsapp_number || '—' }}</dd>
                        </div>
                    </dl>
                </section>

                <form v-if="isEditing" @submit.prevent="submit" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Edit Store Settings</h2>

                    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Store Name</label>
                            <input v-model="form.store_name" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                            <p v-if="form.errors.store_name" class="mt-1 text-sm text-red-600">{{ form.errors.store_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Store Email</label>
                            <input v-model="form.store_email" type="email" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                            <p v-if="form.errors.store_email" class="mt-1 text-sm text-red-600">{{ form.errors.store_email }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Phone</label>
                            <input v-model="form.store_phone" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Currency</label>
                            <input v-model="form.store_currency" maxlength="3" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 uppercase" />
                            <p v-if="form.errors.store_currency" class="mt-1 text-sm text-red-600">{{ form.errors.store_currency }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Delivery Fee</label>
                            <input v-model="form.delivery_fee" type="number" min="0" step="0.01" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                            <p v-if="form.errors.delivery_fee" class="mt-1 text-sm text-red-600">{{ form.errors.delivery_fee }}</p>
                        </div>

                        <div class="flex items-center rounded-lg border border-slate-200 p-4">
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input v-model="form.cash_on_delivery_enabled" type="checkbox" />
                                Enable Cash on Delivery
                            </label>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Description</label>
                            <textarea v-model="form.store_description" rows="3" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Business Address</label>
                            <textarea v-model="form.business_address" rows="3" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Instagram URL</label>
                            <input v-model="form.instagram_url" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Facebook URL</label>
                            <input v-model="form.facebook_url" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">TikTok URL</label>
                            <input v-model="form.tiktok_url" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">WhatsApp Number</label>
                            <input v-model="form.whatsapp_number" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Store Settings' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
