<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    CreditCard,
    Globe,
    LayoutGrid,
    ReceiptText,
    Settings,
    ShoppingCart,
    SquareChartGantt,
    Tag,
    UsersIcon,
    Wallet,
    LayoutTemplate,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Products',
        href: '/manage/product',
        icon: SquareChartGantt,
    },
    {
        title: 'Categories',
        href: '/manage/category',
        icon: Tag,
    },
    {
        title: 'Orders',
        href: '/manage/order',
        icon: ShoppingCart,
    },
    {
        title: 'Customers',
        href: '/manage/customer',
        icon: UsersIcon,
    },
    {
        title: 'Billing',
        href: '/manage/billing',
        icon: ReceiptText,
    },
    {
        title: 'Payouts',
        href: '/manage/payouts',
        icon: Wallet,
    },
    {
        title: 'Website Builder',
        href: '/manage/website',
        icon: LayoutTemplate,
    },
    {
        title: 'Store Settings',
        href: '/manage/store-settings',
        icon: Settings,
    },
    {
        title: 'Domains',
        href: '/manage/domains',
        icon: Globe,
    },
    {
        title: 'Payout Account',
        href: '/manage/payout-account',
        icon: CreditCard,
    },
];

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>

    <slot />
</template>
