
<script setup>

import { Head } from '@inertiajs/vue3';

defineProps({

    storeName: String,

    storeEmail: String,

});

</script>

<template>

    <Head title="Store Unavailable" />

    <div class="flex min-h-screen items-center justify-center bg-slate-50 px-6 text-slate-900">

        <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">

            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 text-3xl">

                !

            </div>

            <h1 class="text-3xl font-bold">Store Temporarily Unavailable</h1>

            <p class="mt-4 text-slate-600">

                {{ storeName || 'This store' }} is currently unavailable. Please check back later.

            </p>

            <p v-if="storeEmail" class="mt-4 text-sm text-slate-500">

                Store contact:

                <a :href="'mailto:' + storeEmail" class="font-medium text-indigo-600 hover:text-indigo-800">

                    {{ storeEmail }}

                </a>

            </p>

            <p class="mt-8 text-xs text-slate-400">

                This notice is shown by the platform while the store account is inactive.

            </p>

        </div>

    </div>

</template>

