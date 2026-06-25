<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';

defineOptions({ layout: CentralLayout });

import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    tenants: Array,
});

const form = useForm({
    tenant_id: '',
    gross_amount: 100,
    processor_fee: 3,
    provider: 'manual',
    provider_transaction_id: '',
    currency: 'USD',
});

const selectedTenant = computed(() => {
    return props.tenants.find((tenant) => tenant.id === form.tenant_id);
});

const commissionRate = computed(() => {
    return Number(selectedTenant.value?.current_subscription?.plan?.commission_rate || 0);
});

const netAmount = computed(() => {
    return Math.max(Number(form.gross_amount || 0) - Number(form.processor_fee || 0), 0);
});

const commissionAmount = computed(() => {
    return netAmount.value * (commissionRate.value / 100);
});

const tenantPayout = computed(() => {
    return netAmount.value - commissionAmount.value;
});

function money(value) {
    return form.currency + ' ' + Number(value || 0).toFixed(2);
}

function submit() {
    form.post('/central/transactions');
}
</script>

<template>
    <Head title="Add Test Transaction" />

    <div class="min-h-screen bg-slate-50 text-slate-900">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-5">
                <div>
                    <h1 class="text-2xl font-bold">Add Test Transaction</h1>
                    <p class="text-sm text-slate-500">Use this to verify commission and payout calculations before payment API integration.</p>
                </div>

                <Link href="/central/transactions" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                    Back to Transactions
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-8">
            <form @submit.prevent="submit" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tenant</label>
                        <select
                            v-model="form.tenant_id"
                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2"
                        >
                            <option value="">Select tenant</option>
                            <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                                {{ tenant.name }}
                                <template v-if="tenant.current_subscription?.plan">
                                    — {{ tenant.current_subscription.plan.name }}
                                </template>
                            </option>
                        </select>
                        <p v-if="form.errors.tenant_id" class="mt-1 text-sm text-red-600">{{ form.errors.tenant_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Currency</label>
                        <input v-model="form.currency" maxlength="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 uppercase" />
                        <p v-if="form.errors.currency" class="mt-1 text-sm text-red-600">{{ form.errors.currency }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Gross Order Amount</label>
                        <input v-model="form.gross_amount" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                        <p v-if="form.errors.gross_amount" class="mt-1 text-sm text-red-600">{{ form.errors.gross_amount }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Processor Fee</label>
                        <input v-model="form.processor_fee" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                        <p v-if="form.errors.processor_fee" class="mt-1 text-sm text-red-600">{{ form.errors.processor_fee }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Provider</label>
                        <input v-model="form.provider" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="manual / wipay / stripe" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Provider Transaction ID</label>
                        <input v-model="form.provider_transaction_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Optional" />
                    </div>
                </div>

                <section class="mt-8 rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <h2 class="text-lg font-semibold">Calculation Preview</h2>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div class="rounded-lg bg-white p-4">
                            <p class="text-sm text-slate-500">Gross</p>
                            <p class="mt-1 font-bold">{{ money(form.gross_amount) }}</p>
                        </div>

                        <div class="rounded-lg bg-white p-4">
                            <p class="text-sm text-slate-500">Processor Fee</p>
                            <p class="mt-1 font-bold">{{ money(form.processor_fee) }}</p>
                        </div>

                        <div class="rounded-lg bg-white p-4">
                            <p class="text-sm text-slate-500">Net Received</p>
                            <p class="mt-1 font-bold">{{ money(netAmount) }}</p>
                        </div>

                        <div class="rounded-lg bg-white p-4">
                            <p class="text-sm text-slate-500">Commission</p>
                            <p class="mt-1 font-bold text-green-600">{{ money(commissionAmount) }}</p>
                            <p class="text-xs text-slate-500">{{ commissionRate.toFixed(2) }}%</p>
                        </div>

                        <div class="rounded-lg bg-white p-4">
                            <p class="text-sm text-slate-500">Tenant Payout</p>
                            <p class="mt-1 font-bold text-indigo-600">{{ money(tenantPayout) }}</p>
                        </div>
                    </div>

                    <p v-if="selectedTenant && !selectedTenant.current_subscription" class="mt-4 rounded-lg bg-yellow-100 p-3 text-sm text-yellow-800">
                        This tenant has no assigned plan, so commission is currently calculated as 0%.
                    </p>
                </section>

                <div class="mt-8 flex justify-end gap-3">
                    <Link href="/central/transactions" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Cancel
                    </Link>

                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                        {{ form.processing ? 'Saving...' : 'Save Transaction' }}
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
