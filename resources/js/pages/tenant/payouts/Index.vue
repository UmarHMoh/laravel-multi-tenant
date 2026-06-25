<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
    balance: Object,
    payoutAccount: Object,
    requests: Array,
})

const form = useForm({
    amount: '',
    tenant_notes: '',
})

function formatMoney(value, currency = 'TTD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(Number(value || 0))
}

function submit() {
    form.post('/manage/payouts', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <Head title="Payouts" />

    <AppLayout>
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Payouts</h1>
                <p class="text-sm text-slate-500">Request withdrawals from your available merchant balance.</p>
            </div>

            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Available Balance</p>
                <p class="mt-2 text-3xl font-bold">
                    {{ formatMoney(balance?.available_balance, balance?.currency || 'TTD') }}
                </p>
                <p class="mt-2 text-sm text-slate-500">
                    Pending: {{ formatMoney(balance?.pending_balance, balance?.currency || 'TTD') }}
                    · Lifetime earned: {{ formatMoney(balance?.lifetime_earned, balance?.currency || 'TTD') }}
                </p>
            </div>

            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Request Payout</h2>

                <form class="mt-4 space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="block text-sm font-medium">Amount</label>
                        <input v-model="form.amount" type="number" step="0.01" min="1" class="mt-1 w-full rounded-lg border p-2" />
                        <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Notes</label>
                        <textarea v-model="form.tenant_notes" class="mt-1 w-full rounded-lg border p-2"></textarea>
                    </div>

                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-slate-900 px-4 py-2 text-white">
                        Submit Payout Request
                    </button>
                </form>
            </div>

            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Payout Requests</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Reference</th>
                                <th class="py-2">Amount</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Requested</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="request in requests" :key="request.id" class="border-b">
                                <td class="py-2">{{ request.reference }}</td>
                                <td class="py-2">{{ formatMoney(request.amount, request.currency) }}</td>
                                <td class="py-2 capitalize">{{ request.status }}</td>
                                <td class="py-2">{{ request.requested_at || request.created_at }}</td>
                            </tr>
                            <tr v-if="!requests?.length">
                                <td colspan="4" class="py-4 text-slate-500">No payout requests yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
