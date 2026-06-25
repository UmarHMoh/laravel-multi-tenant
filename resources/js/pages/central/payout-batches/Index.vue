<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    batches: Array,
    approvedRequests: Array,
})

const form = useForm({
    payout_request_ids: [],
    notes: '',
})

function formatMoney(value, currency = 'TTD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(Number(value || 0))
}

function toggleRequest(id) {
    if (form.payout_request_ids.includes(id)) {
        form.payout_request_ids = form.payout_request_ids.filter((value) => value !== id)
    } else {
        form.payout_request_ids.push(id)
    }
}

function createBatch() {
    form.post('/central/payout-batches', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    })
}

function post(url) {
    router.post(url, {}, { preserveScroll: true })
}
</script>

<template>
    <Head title="Payout Batches" />

    <CentralLayout>
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Payout Batches</h1>
                <p class="text-sm text-slate-500">Group approved payout requests and mark them paid in batches.</p>
            </div>

            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Create Batch from Approved Requests</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Select</th>
                                <th class="py-2">Tenant</th>
                                <th class="py-2">Reference</th>
                                <th class="py-2">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="request in approvedRequests" :key="request.id" class="border-b">
                                <td class="py-2">
                                    <input
                                        type="checkbox"
                                        :checked="form.payout_request_ids.includes(request.id)"
                                        @change="toggleRequest(request.id)"
                                    />
                                </td>
                                <td class="py-2">{{ request.tenant?.name || request.tenant_id }}</td>
                                <td class="py-2">{{ request.reference }}</td>
                                <td class="py-2">{{ formatMoney(request.amount, request.currency) }}</td>
                            </tr>
                            <tr v-if="!approvedRequests?.length">
                                <td colspan="4" class="py-4 text-slate-500">No approved requests available for batching.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium">Notes</label>
                    <textarea v-model="form.notes" class="mt-1 w-full rounded-lg border p-2"></textarea>
                </div>

                <button
                    class="mt-4 rounded-lg bg-slate-900 px-4 py-2 text-white disabled:opacity-50"
                    :disabled="form.processing || !form.payout_request_ids.length"
                    @click="createBatch"
                >
                    Create Batch
                </button>
            </div>

            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Batches</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Batch</th>
                                <th class="py-2">Requests</th>
                                <th class="py-2">Total</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="batch in batches" :key="batch.id" class="border-b">
                                <td class="py-2">{{ batch.batch_number }}</td>
                                <td class="py-2">{{ batch.request_count }}</td>
                                <td class="py-2">{{ formatMoney(batch.total_amount, batch.currency) }}</td>
                                <td class="py-2 capitalize">{{ batch.status }}</td>
                                <td class="space-x-2 py-2">
                                    <button
                                        v-if="['draft', 'approved'].includes(batch.status)"
                                        class="rounded bg-emerald-600 px-3 py-1 text-white"
                                        @click="post(`/central/payout-batches/${batch.id}/mark-paid`)"
                                    >
                                        Mark Paid
                                    </button>

                                    <button
                                        v-if="batch.status !== 'paid' && batch.status !== 'cancelled'"
                                        class="rounded bg-red-600 px-3 py-1 text-white"
                                        @click="post(`/central/payout-batches/${batch.id}/cancel`)"
                                    >
                                        Cancel
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!batches?.length">
                                <td colspan="5" class="py-4 text-slate-500">No payout batches yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </CentralLayout>
</template>
