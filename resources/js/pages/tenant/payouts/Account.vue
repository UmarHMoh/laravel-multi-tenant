<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    payoutAccount: Object,
    bankOptions: Array,
});

const isEditing = ref(!props.payoutAccount);

const form = useForm({
    type: props.payoutAccount?.type || 'bank',
    account_holder_name: props.payoutAccount?.account_holder_name || '',
    bank_name: props.payoutAccount?.bank_name || '',
    bank_account_number: props.payoutAccount?.bank_account_number || '',
    branch_transit_number: props.payoutAccount?.branch_transit_number || '',
    bank_account_type: props.payoutAccount?.bank_account_type || '',
    wipay_account_email: props.payoutAccount?.wipay_account_email || '',
    notes: props.payoutAccount?.notes || '',
});

watch(() => props.payoutAccount, (account) => {
    if (!account) {
        isEditing.value = true;
        return;
    }

    form.type = account.type || 'bank';
    form.account_holder_name = account.account_holder_name || '';
    form.bank_name = account.bank_name || '';
    form.bank_account_number = account.bank_account_number || '';
    form.branch_transit_number = account.branch_transit_number || '';
    form.bank_account_type = account.bank_account_type || '';
    form.wipay_account_email = account.wipay_account_email || '';
    form.notes = account.notes || '';
}, { deep: true });

function submit() {
    form.post('/manage/payout-account', {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
}

function cancelEdit() {
    if (!props.payoutAccount) return;

    form.type = props.payoutAccount.type || 'bank';
    form.account_holder_name = props.payoutAccount.account_holder_name || '';
    form.bank_name = props.payoutAccount.bank_name || '';
    form.bank_account_number = props.payoutAccount.bank_account_number || '';
    form.branch_transit_number = props.payoutAccount.branch_transit_number || '';
    form.bank_account_type = props.payoutAccount.bank_account_type || '';
    form.wipay_account_email = props.payoutAccount.wipay_account_email || '';
    form.notes = props.payoutAccount.notes || '';

    isEditing.value = false;
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString();
}

function statusClass(status) {
    return {
        pending: 'bg-yellow-100 text-yellow-700',
        verified: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
    }[status] || 'bg-slate-100 text-slate-700';
}

function accountTypeLabel(type) {
    return {
        savings: 'Savings',
        checking: 'Checking',
    }[type] || '—';
}
</script>

<template>
    <Head title="Payout Account" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 px-6 py-8 text-slate-900">
            <div class="mx-auto max-w-5xl">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold">Payout Account</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Manage the account where your store payouts should be sent.
                    </p>
                </div>

                <div v-if="payoutAccount && !isEditing" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-5">
                        <div>
                            <h2 class="text-lg font-semibold">Current Payout Account</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Your saved payout account is shown below.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="rounded-full px-3 py-1 text-xs font-medium capitalize" :class="statusClass(payoutAccount.status)">
                                {{ payoutAccount.status }}
                            </span>

                            <button
                                @click="isEditing = true"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                Edit Account
                            </button>
                        </div>
                    </div>

                    <div v-if="payoutAccount.status === 'pending'" class="mt-5 rounded-lg bg-yellow-50 p-4 text-sm text-yellow-800">
                        Your payout account is pending platform review.
                    </div>

                    <div v-if="payoutAccount.status === 'verified'" class="mt-5 rounded-lg bg-green-50 p-4 text-sm text-green-800">
                        Your payout account has been approved.
                    </div>

                    <div v-if="payoutAccount.status === 'rejected'" class="mt-5 rounded-lg bg-red-50 p-4 text-sm text-red-800">
                        Your payout account was rejected.
                        <div v-if="payoutAccount.review_notes" class="mt-2 font-medium">
                            Reason: {{ payoutAccount.review_notes }}
                        </div>
                    </div>

                    <dl class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <dt class="text-sm text-slate-500">Type</dt>
                            <dd class="mt-1 font-medium capitalize">{{ payoutAccount.type }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">Account Holder</dt>
                            <dd class="mt-1 font-medium">{{ payoutAccount.account_holder_name || '—' }}</dd>
                        </div>

                        <template v-if="payoutAccount.type === 'bank'">
                            <div>
                                <dt class="text-sm text-slate-500">Bank</dt>
                                <dd class="mt-1 font-medium">{{ payoutAccount.bank_name || '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Account Type</dt>
                                <dd class="mt-1 font-medium">{{ accountTypeLabel(payoutAccount.bank_account_type) }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Branch / Transit Number</dt>
                                <dd class="mt-1 font-medium">{{ payoutAccount.branch_transit_number || '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Account Number</dt>
                                <dd class="mt-1 font-medium">{{ payoutAccount.bank_account_number || '—' }}</dd>
                            </div>
                        </template>

                        <div v-if="payoutAccount.type === 'wipay'">
                            <dt class="text-sm text-slate-500">WiPay Email</dt>
                            <dd class="mt-1 font-medium">{{ payoutAccount.wipay_account_email || '—' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">Submitted</dt>
                            <dd class="mt-1 font-medium">{{ formatDate(payoutAccount.created_at) }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm text-slate-500">Last Updated</dt>
                            <dd class="mt-1 font-medium">{{ formatDate(payoutAccount.updated_at) }}</dd>
                        </div>
                    </dl>

                    <p v-if="payoutAccount.notes" class="mt-5 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                        {{ payoutAccount.notes }}
                    </p>
                </div>

                <form v-if="isEditing" @submit.prevent="submit" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between border-b border-slate-200 pb-5">
                        <div>
                            <h2 class="text-lg font-semibold">
                                {{ payoutAccount ? 'Edit Payout Account' : 'Submit Payout Account' }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Saving changes will send this account back to pending review.
                            </p>
                        </div>

                        <button
                            v-if="payoutAccount"
                            type="button"
                            @click="cancelEdit"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"
                        >
                            Cancel
                        </button>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Payout Method</label>
                            <select v-model="form.type" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                                <option value="bank">Bank Account</option>
                                <option value="wipay">WiPay Account</option>
                            </select>
                            <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Account Holder Name</label>
                            <input v-model="form.account_holder_name" type="text" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                            <p v-if="form.errors.account_holder_name" class="mt-1 text-sm text-red-600">{{ form.errors.account_holder_name }}</p>
                        </div>

                        <template v-if="form.type === 'bank'">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Bank</label>
                                <select v-model="form.bank_name" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                                    <option value="">Select bank</option>
                                    <option v-for="bank in bankOptions" :key="bank.id" :value="bank.name">
                                        {{ bank.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.bank_name" class="mt-1 text-sm text-red-600">{{ form.errors.bank_name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Account Type</label>
                                <select v-model="form.bank_account_type" class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2">
                                    <option value="">Select account type</option>
                                    <option value="savings">Savings</option>
                                    <option value="checking">Checking</option>
                                </select>
                                <p v-if="form.errors.bank_account_type" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_type }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Branch / Transit Number</label>
                                <input v-model="form.branch_transit_number" type="text" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                                <p v-if="form.errors.branch_transit_number" class="mt-1 text-sm text-red-600">{{ form.errors.branch_transit_number }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700">Bank Account Number</label>
                                <input v-model="form.bank_account_number" type="text" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                                <p v-if="form.errors.bank_account_number" class="mt-1 text-sm text-red-600">{{ form.errors.bank_account_number }}</p>
                            </div>
                        </template>

                        <template v-if="form.type === 'wipay'">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">WiPay Account Email</label>
                                <input v-model="form.wipay_account_email" type="email" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2" />
                                <p v-if="form.errors.wipay_account_email" class="mt-1 text-sm text-red-600">{{ form.errors.wipay_account_email }}</p>
                            </div>
                        </template>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Notes</label>
                            <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2"></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                            {{ form.processing ? 'Saving...' : 'Save Payout Account' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
