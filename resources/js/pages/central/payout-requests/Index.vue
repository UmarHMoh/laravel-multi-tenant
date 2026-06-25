<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue'
import { Head, router } from '@inertiajs/vue3'

const props = defineProps({
    requests: Array,
})

function formatMoney(value, currency = 'TTD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(Number(value || 0))
}

function post(url) {
    router.post(url, {}, { preserveScroll: true })
}
</script>

<template>
    <Head title="Payout Requests" />

    <CentralLayout>
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Payout Requests</h1>
                <p class="text-sm text-slate-500">Review and process tenant withdrawal requests.</p>
            </div>

            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Tenant</th>
                                <th class="py-2">Reference</th>
                                <th class="py-2">Amount</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="request in requests" :key="request.id" class="border-b">
                                <td class="py-2">{{ request.tenant?.name || request.tenant_id }}</td>
                                <td class="py-2">{{ request.reference }}</td>
                                <td class="py-2">{{ formatMoney(request.amount, request.currency) }}</td>
                                <td class="py-2 capitalize">{{ request.status }}</td>
                                <td class="space-x-2 py-2">
                                    <button
                                        v-if="request.status === 'requested'"
                                        class="rounded bg-blue-600 px-3 py-1 text-white"
                                        @click="post(`/central/payout-requests/${request.id}/approve`)"
                                    >
                                        Approve
                                    </button>

                                    <button
                                        v-if="['requested', 'approved'].includes(request.status)"
                                        class="rounded bg-emerald-600 px-3 py-1 text-white"
                                        @click="post(`/central/payout-requests/${request.id}/mark-paid`)"
                                    >
                                        Mark Paid
                                    </button>

                                    <button
                                        v-if="['requested', 'approved'].includes(request.status)"
                                        class="rounded bg-red-600 px-3 py-1 text-white"
                                        @click="post(`/central/payout-requests/${request.id}/reject`)"
                                    >
                                        Reject
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!requests?.length">
                                <td colspan="5" class="py-4 text-slate-500">No payout requests yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </CentralLayout>
</template>
