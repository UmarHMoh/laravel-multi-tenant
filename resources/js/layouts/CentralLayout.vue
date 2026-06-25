<script setup>
import FlashMessages from '@/components/FlashMessages.vue';
import { Link } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    Users,
    Layers,
    CreditCard,
    ReceiptText,
    Wallet,
    Settings,
    PlusCircle,
    HandCoins,
    PackageCheck,
    FileSpreadsheet,
} from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        default: 'Central Dashboard',
    },
    description: {
        type: String,
        default: 'Manage your platform.',
    },
});

const navItems = [
    { label: 'Overview', href: '/central', icon: LayoutDashboard },
    { label: 'Tenants', href: '/central/tenants', icon: Users },
    { label: 'Create Tenant', href: '/central/tenants/create', icon: PlusCircle },
    { label: 'Plans', href: '/central/plans', icon: Layers },
    { label: 'Payment Settings', href: '/central/payment-settings', icon: CreditCard },
    { label: 'Transactions', href: '/central/transactions', icon: ReceiptText },
    { label: 'Payouts', href: '/central/payouts', icon: Wallet },
    { label: 'Settings', href: '/central/settings', icon: Settings },
    { label: 'Bank Options', href: '/central/bank-options', icon: Settings },
    {
        name: 'Payout Requests',
        href: '/central/payout-requests',
        icon: HandCoins,
    },
    {
        name: 'Payout Batches',
        href: '/central/payout-batches',
        icon: PackageCheck,
    },
    {
        name: 'Accounting Exports',
        href: '/central/accounting/exports',
        icon: FileSpreadsheet,
    },
];

function isActive(item) {
    const path = window.location.pathname;

    if (item.href === '/central') {
        return path === '/central';
    }

    return path === item.href || path.startsWith(item.href + '/');
}
</script>

<template>
    <FlashMessages />
    <div class="min-h-screen bg-slate-50 text-slate-900">
        <aside class="fixed inset-y-0 left-0 hidden w-72 border-r border-slate-200 bg-white lg:block">
            <div class="flex h-full flex-col">
                <div class="border-b border-slate-200 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-600">Platform Owner</p>
                    <h1 class="mt-1 text-xl font-bold">Central Admin</h1>
                    <p class="mt-1 text-xs text-slate-500">Multi-tenant commerce control panel</p>
                </div>

                <nav class="flex-1 space-y-1 px-4 py-5">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition"
                        :class="isActive(item)
                            ? 'bg-indigo-50 text-indigo-700'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                    >
                        <component :is="item.icon" class="h-5 w-5" />
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="border-t border-slate-200 p-4">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Platform Admin</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Payments, plans, payouts, tenants, transactions, and domains are managed here.
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="lg:pl-72">
            <header class="border-b border-slate-200 bg-white">
                <div class="flex items-center justify-between px-6 py-5">
                    <div>
                        <h1 class="text-2xl font-bold">{{ title }}</h1>
                        <p class="mt-1 text-sm text-slate-500">{{ description }}</p>
                    </div>

                    <div class="flex gap-3">
                        <slot name="actions" />
                    </div>
                </div>
            </header>

            <main class="px-6 py-8">
                <slot />
            </main>
        </div>
    </div>
</template>
