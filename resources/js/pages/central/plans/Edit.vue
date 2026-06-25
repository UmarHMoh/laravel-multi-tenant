<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';

defineOptions({ layout: CentralLayout });

import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    plan: Object,
    paymentSetting: Object,
});

const providerPercent = computed(() => Number(props.paymentSetting?.processor_fee_percent || 0));
const providerFixed = computed(() => Number(props.paymentSetting?.processor_fee_fixed || 0));
const platformPercent = computed(() => Number(form.transaction_fee_percent || 0));
const platformFixed = computed(() => Number(form.transaction_fee_fixed || 0));
const totalPercent = computed(() => providerPercent.value + platformPercent.value);
const totalFixed = computed(() => providerFixed.value + platformFixed.value);

function formatPercent(value) {
    return Number(value || 0).toFixed(2) + '%';
}

function formatMoney(value) {
    return (props.paymentSetting?.currency || 'TTD') + ' ' + Number(value || 0).toFixed(2);
}

const form = useForm({
    name: props.plan.name,
    slug: props.plan.slug,
    description: props.plan.description || '',
    monthly_price: props.plan.monthly_price,
    commission_rate: props.plan.commission_rate,
    transaction_fee_percent: props.plan.transaction_fee_percent ?? props.plan.commission_rate ?? 0,
    transaction_fee_fixed: props.plan.transaction_fee_fixed ?? 0,
    max_products: props.plan.max_products ?? '',
    custom_domain_enabled: props.plan.custom_domain_enabled,
    api_payment_enabled: props.plan.api_payment_enabled,
    allow_multiple_builder_pages: props.plan.allow_multiple_builder_pages,
    is_active: props.plan.is_active,
});

function submit() {
    form.transaction_fee_percent = Number(form.transaction_fee_percent || 0);
    form.transaction_fee_fixed = Number(form.transaction_fee_fixed || 0);
    form.commission_rate = form.transaction_fee_percent;
    form.put('/central/plans/' + props.plan.id);
}
</script>

<template>
    <Head title="Edit Plan" />

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-5">
                <div>
                    <h1 class="text-2xl font-bold">Edit Plan</h1>
                    <p class="text-sm text-slate-500">
                        Set monthly pricing, your added platform commission, and plan features.
                    </p>
                </div>

                <Link href="/central/plans" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                    Back to Plans
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-8">
            <form @submit.prevent="submit" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                    Enter only your platform add-on fee. The tenant-facing fee is calculated as provider fee plus your added fee.
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Name</label>
                        <input v-model="form.name" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Starter" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Slug</label>
                        <input v-model="form.slug" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="starter" />
                        <p class="mt-1 text-xs text-slate-500">Leave blank to auto-generate.</p>
                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Monthly Price</label>
                        <input v-model="form.monthly_price" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                        <p v-if="form.errors.monthly_price" class="mt-1 text-sm text-red-600">{{ form.errors.monthly_price }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Max Products</label>
                        <input v-model="form.max_products" type="number" min="0" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Leave blank for unlimited" />
                        <p v-if="form.errors.max_products" class="mt-1 text-sm text-red-600">{{ form.errors.max_products }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Your Additional Platform Commission %</label>
                        <input v-model="form.transaction_fee_percent" type="number" step="0.01" min="0" max="100" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                        <p class="mt-1 text-xs text-slate-500">
                            Provider fee: {{ formatPercent(providerPercent) }}. Your added profit: {{ formatPercent(platformPercent) }}. Tenant sees: {{ formatPercent(totalPercent) }}.
                        </p>
                        <p v-if="form.errors.transaction_fee_percent" class="mt-1 text-sm text-red-600">{{ form.errors.transaction_fee_percent }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Your Additional Fixed Fee</label>
                        <input v-model="form.transaction_fee_fixed" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                        <p class="mt-1 text-xs text-slate-500">
                            Provider fixed fee: {{ formatMoney(providerFixed) }}. Your added fixed profit: {{ formatMoney(platformFixed) }}. Tenant sees: {{ formatMoney(totalFixed) }}.
                        </p>
                        <p v-if="form.errors.transaction_fee_fixed" class="mt-1 text-sm text-red-600">{{ form.errors.transaction_fee_fixed }}</p>
                    </div>

                    <div class="space-y-3 md:col-span-2">
                        <label class="flex items-center gap-2">
                            <input v-model="form.custom_domain_enabled" type="checkbox" />
                            <span class="text-sm font-medium text-slate-700">Allow custom domains</span>
                        </label>

                        <label class="flex items-center gap-2">
                            <input v-model="form.api_payment_enabled" type="checkbox" />
                            <span class="text-sm font-medium text-slate-700">Allow API payment settings</span>
                        </label>

                        <label class="flex items-center gap-2">
                            <input v-model="form.allow_multiple_builder_pages" type="checkbox" />
                            <span class="text-sm font-medium text-slate-700">Allow multiple website builder pages</span>
                        </label>

                        <p class="ml-6 text-xs text-slate-500">
                            If unchecked, the tenant can only use the Homepage builder page.
                        </p>

                        <label class="flex items-center gap-2">
                            <input v-model="form.is_active" type="checkbox" />
                            <span class="text-sm font-medium text-slate-700">Plan is active</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <textarea v-model="form.description" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <Link href="/central/plans" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Cancel
                    </Link>

                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" :disabled="form.processing">
                        Update Plan
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
