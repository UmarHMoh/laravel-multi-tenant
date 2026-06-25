<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

defineOptions({ layout: CentralLayout });

const props = defineProps({
    tenant: Object,
    payoutAccounts: Array,
    latestPayoutAccount: Object,
    pendingTransactions: Array,
    payouts: Array,
    summary: Object,
});

const form = useForm({
    reference: '',
    notes: '',
});

const reviewForm = useForm({
    review_notes: '',
});

function money(value, currency = 'USD') {
    return currency + ' ' + Number(value || 0).toFixed(2);
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

function markPaid() {
    if (!confirm('Mark all pending transactions for this tenant as paid out?')) return;

    form.post('/central/payouts/' + props.tenant.id + '/mark-paid', {
        preserveScroll: true,
    });
}

function approveAccount(account) {
    if (!confirm('Approve this payout account?')) return;

    reviewForm.post('/central/payout-accounts/' + account.id + '/approve', {
        preserveScroll: true,
        onSuccess: () => {
            reviewForm.review_notes = '';
        },
    });
}

function rejectAccount(account) {
    const reason = prompt('Enter rejection reason for the tenant');

    if (!reason) return;

    router.post('/central/payout-accounts/' + account.id + '/reject', {
        review_notes: reason,
    }, {
        preserveScroll: true,
    });
}

function markPending(account) {
    if (!confirm('Mark this account back to pending?')) return;

    reviewForm.post('/central/payout-accounts/' + account.id + '/pending', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="'Payouts - ' + tenant.name" />

    <div class="mx-auto max-w-7xl space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-center">
                <div>
                    <h1 class="text-2xl font-bold">Payouts: {{ tenant.name }}</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Review payout account, pending transactions, and payout history.
                    </p>
                </div>

                <div class="flex gap-3">
                    <Link href="/central/payouts" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Back to Payouts
                    </Link>

                    <Link :href="'/central/tenants/' + tenant.id" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                        Tenant Profile
                    </Link>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-5">
                <div>
                    <h2 class="text-lg font-semibold">Payout Account Review</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Approve or reject the tenant’s latest payout account before sending payouts.
                    </p>
                </div>
            </div>

            <div v-if="latestPayoutAccount" class="mt-5 rounded-lg border border-slate-200 p-4">
                <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-start">
                    <div>
                        <div class="flex items-center gap-3">
                            <h3 class="font-semibold capitalize">{{ latestPayoutAccount.type }} Account</h3>
                            <span class="rounded-full px-3 py-1 text-xs font-medium capitalize" :class="statusClass(latestPayoutAccount.status)">
                                {{ latestPayoutAccount.status }}
                            </span>
                        </div>

                        <dl class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <dt class="text-sm text-slate-500">Account Holder</dt>
                                <dd class="mt-1 font-medium">{{ latestPayoutAccount.account_holder_name || '—' }}</dd>
                            </div>

                            <div v-if="latestPayoutAccount.type === 'bank'">
                                <dt class="text-sm text-slate-500">Bank</dt>
                                <dd class="mt-1 font-medium">{{ latestPayoutAccount.bank_name || '—' }}</dd>
                            </div>

                            <div v-if="latestPayoutAccount.type === 'bank'">
                                <dt class="text-sm text-slate-500">Account Type</dt>
                                <dd class="mt-1 font-medium capitalize">{{ latestPayoutAccount.bank_account_type || '—' }}</dd>
                            </div>

                            <div v-if="latestPayoutAccount.type === 'bank'">
                                <dt class="text-sm text-slate-500">Branch / Transit Number</dt>
                                <dd class="mt-1 font-medium">{{ latestPayoutAccount.branch_transit_number || '—' }}</dd>
                            </div>

                            <div v-if="latestPayoutAccount.type === 'bank'">
                                <dt class="text-sm text-slate-500">Account Number</dt>
                                <dd class="mt-1 font-medium">{{ latestPayoutAccount.bank_account_number || '—' }}</dd>
                            </div>

                            <div v-if="latestPayoutAccount.type === 'wipay'">
                                <dt class="text-sm text-slate-500">WiPay Email</dt>
                                <dd class="mt-1 font-medium">{{ latestPayoutAccount.wipay_account_email || '—' }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Submitted</dt>
                                <dd class="mt-1 font-medium">{{ formatDate(latestPayoutAccount.created_at) }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Reviewed</dt>
                                <dd class="mt-1 font-medium">{{ formatDate(latestPayoutAccount.reviewed_at) }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm text-slate-500">Reviewed By</dt>
                                <dd class="mt-1 font-medium">{{ latestPayoutAccount.reviewed_by || '—' }}</dd>
                            </div>
                        </dl>

                        <div v-if="latestPayoutAccount.notes" class="mt-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">
                            Tenant notes: {{ latestPayoutAccount.notes }}
                        </div>

                        <div v-if="latestPayoutAccount.review_notes" class="mt-4 rounded-lg bg-blue-50 p-3 text-sm text-blue-800">
                            Review notes: {{ latestPayoutAccount.review_notes }}
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-3">
                        <button
                            v-if="latestPayoutAccount.status !== 'verified'"
                            @click="approveAccount(latestPayoutAccount)"
                            :disabled="reviewForm.processing"
                            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-60"
                        >
                            Approve
                        </button>

                        <button
                            v-if="latestPayoutAccount.status !== 'rejected'"
                            @click="rejectAccount(latestPayoutAccount)"
                            :disabled="reviewForm.processing"
                            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-60"
                        >
                            Reject
                        </button>

                        <button
                            v-if="latestPayoutAccount.status !== 'pending'"
                            @click="markPending(latestPayoutAccount)"
                            :disabled="reviewForm.processing"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100 disabled:opacity-60"
                        >
                            Mark Pending
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="mt-5 rounded-lg bg-yellow-50 p-4 text-sm text-yellow-800">
                This tenant has not submitted a payout account yet.
            </div>
        </section>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Pending Gross</p>
                <p class="mt-2 text-2xl font-bold">{{ money(summary.gross) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Processor Fees</p>
                <p class="mt-2 text-2xl font-bold">{{ money(summary.processorFees) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Net Received</p>
                <p class="mt-2 text-2xl font-bold">{{ money(summary.net) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Platform Profit</p>
                <p class="mt-2 text-2xl font-bold text-green-600">{{ money(summary.commission) }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500">Payout Due</p>
                <p class="mt-2 text-2xl font-bold text-indigo-600">{{ money(summary.payout) }}</p>
            </div>
        </div>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <h2 class="text-lg font-semibold">Mark Payout as Paid</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        The tenant payout account must be approved before you can mark payouts as paid.
                    </p>
                </div>

                <button
                    @click="markPaid"
                    :disabled="form.processing || pendingTransactions.length === 0 || !latestPayoutAccount || latestPayoutAccount.status !== 'verified'"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving...' : 'Mark All Pending as Paid' }}
                </button>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Payout Reference</label>
                    <input v-model="form.reference" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Bank transfer ref / WiPay ref" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Notes</label>
                    <input v-model="form.notes" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Optional notes" />
                </div>
            </div>

            <p v-if="form.errors.payout" class="mt-3 text-sm text-red-600">{{ form.errors.payout }}</p>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Pending Transactions</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Paid At</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Gross</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Fee</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Net</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Platform Profit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Payout</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Provider</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="transaction in pendingTransactions" :key="transaction.id">
                            <td class="px-6 py-4 text-sm text-slate-600">{{ formatDate(transaction.paid_at) }}</td>
                            <td class="px-6 py-4 text-sm">{{ money(transaction.gross_amount, transaction.currency) }}</td>
                            <td class="px-6 py-4 text-sm">{{ money(transaction.processor_fee, transaction.currency) }}</td>
                            <td class="px-6 py-4 text-sm">{{ money(transaction.net_amount, transaction.currency) }}</td>
                            <td class="px-6 py-4 text-sm text-green-700">{{ money(transaction.commission_amount, transaction.currency) }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-indigo-700">{{ money(transaction.tenant_payout_amount, transaction.currency) }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ transaction.provider }}</td>
                        </tr>

                        <tr v-if="pendingTransactions.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                No pending payout transactions.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Payout History</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Paid At</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Gross</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Platform Profit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Payout</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-slate-500">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200 bg-white">
                        <tr v-for="payout in payouts" :key="payout.id">
                            <td class="px-6 py-4 text-sm text-slate-600">{{ formatDate(payout.paid_at) }}</td>
                            <td class="px-6 py-4 text-sm">{{ payout.reference || '—' }}</td>
                            <td class="px-6 py-4 text-sm">{{ money(payout.total_gross, payout.currency) }}</td>
                            <td class="px-6 py-4 text-sm text-green-700">{{ money(payout.total_commission, payout.currency) }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-indigo-700">{{ money(payout.total_payout, payout.currency) }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                    {{ payout.status }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <Link :href="'/central/payout-batches/' + payout.id" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                    View Batch
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="payouts.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
                                No payout history yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
