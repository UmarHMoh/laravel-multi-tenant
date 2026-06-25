<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();

const visible = ref(true);

const success = computed(() => page.props.flash?.success || null);
const error = computed(() => page.props.flash?.error || null);

watch([success, error], () => {
    visible.value = true;
});
</script>

<template>
    <div v-if="visible && (success || error)" class="fixed right-6 top-6 z-50 w-full max-w-md space-y-3">
        <div
            v-if="success"
            class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-lg"
        >
            <div class="flex items-start justify-between gap-4">
                <p>{{ success }}</p>
                <button @click="visible = false" class="font-bold text-green-700">×</button>
            </div>
        </div>

        <div
            v-if="error"
            class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-lg"
        >
            <div class="flex items-start justify-between gap-4">
                <p>{{ error }}</p>
                <button @click="visible = false" class="font-bold text-red-700">×</button>
            </div>
        </div>
    </div>
</template>
