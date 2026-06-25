<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const from = ref('')
const to = ref('')
const tenantId = ref('')

const queryString = computed(() => {
    const params = new URLSearchParams()

    if (from.value) params.set('from', from.value)
    if (to.value) params.set('to', to.value)
    if (tenantId.value) params.set('tenant_id', tenantId.value)

    const query = params.toString()

    return query ? `?${query}` : ''
})

const exportsList = [
    {
        title: 'Payout Requests',
        description: 'Withdrawal requests submitted by tenants.',
        href: '/central/accounting/exports/payout-requests',
    },
    {
        title: 'Payout Batches',
        description: 'Grouped payout runs created by central admin.',
        href: '/central/accounting/exports/payout-batches',
    },
    {
        title: 'Payout Ledger',
        description: 'Credit and debit ledger entries for tenant balances.',
        href: '/central/accounting/exports/payout-ledger',
    },
    {
        title: 'Platform Transactions',
        description: 'Gross order transactions, fees, and merchant payouts.',
        href: '/central/accounting/exports/platform-transactions',
    },
]
</script>

<template>
    <Head title="Accounting Exports" />

    <CentralLayout>
        <div class="space-y-6 p-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Accounting Exports</h1>
                <p class="text-sm text-slate-500">Download payout and transaction reports as CSV files.</p>
            </div>

            <div class="rounded-xl border bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold">Filters</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium">From</label>
                        <input v-model="from" type="date" class="mt-1 w-full rounded-lg border p-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">To</label>
                        <input v-model="to" type="date" class="mt-1 w-full rounded-lg border p-2" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Tenant ID</label>
                        <input v-model="tenantId" type="text" placeholder="tenant1" class="mt-1 w-full rounded-lg border p-2" />
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div v-for="item in exportsList" :key="item.href" class="rounded-xl border bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">{{ item.title }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ item.description }}</p>

                    <a
                        :href="item.href + queryString"
                        class="mt-4 inline-flex rounded-lg bg-slate-900 px-4 py-2 text-white"
                    >
                        Download CSV
                    </a>
                </div>
            </div>
        </div>
    </CentralLayout>
</template>
