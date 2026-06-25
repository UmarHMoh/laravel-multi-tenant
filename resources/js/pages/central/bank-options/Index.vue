<script setup>
import CentralLayout from '@/layouts/CentralLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({ layout: CentralLayout });

const props = defineProps({
    banks: Array,
});

const editingBankId = ref(null);

const createForm = useForm({
    name: '',
});

const editForm = useForm({
    name: '',
    is_active: true,
});

function addBank() {
    createForm.post('/central/bank-options', {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
        },
    });
}

function startEdit(bank) {
    editingBankId.value = bank.id;
    editForm.name = bank.name;
    editForm.is_active = bank.is_active;
}

function cancelEdit() {
    editingBankId.value = null;
    editForm.reset();
}

function saveBank(bank) {
    editForm.put('/central/bank-options/' + bank.id, {
        preserveScroll: true,
        onSuccess: () => {
            editingBankId.value = null;
        },
    });
}
</script>

<template>
    <Head title="Bank Options" />

    <div class="mx-auto max-w-5xl space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold">Bank Options</h1>
            <p class="mt-1 text-sm text-slate-500">
                Manage the bank names tenants can select when submitting payout accounts.
            </p>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold">Add Bank</h2>

            <form @submit.prevent="addBank" class="mt-5 flex flex-col gap-3 md:flex-row">
                <div class="flex-1">
                    <input
                        v-model="createForm.name"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2"
                        placeholder="Example: Republic Bank"
                    />
                    <p v-if="createForm.errors.name" class="mt-1 text-sm text-red-600">
                        {{ createForm.errors.name }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="createForm.processing"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                >
                    {{ createForm.processing ? 'Adding...' : 'Add Bank' }}
                </button>
            </form>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold">Available Banks</h2>
            </div>

            <div class="divide-y divide-slate-200">
                <div v-for="bank in banks" :key="bank.id" class="px-6 py-4">
                    <div v-if="editingBankId !== bank.id" class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-medium">{{ bank.name }}</p>
                            <span
                                class="mt-1 inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                :class="bank.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                            >
                                {{ bank.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <button
                            @click="startEdit(bank)"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-100"
                        >
                            Edit
                        </button>
                    </div>

                    <form v-else @submit.prevent="saveBank(bank)" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                        <div class="md:col-span-2">
                            <input
                                v-model="editForm.name"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2"
                            />
                            <p v-if="editForm.errors.name" class="mt-1 text-sm text-red-600">
                                {{ editForm.errors.name }}
                            </p>
                        </div>

                        <label class="flex items-center gap-2 text-sm">
                            <input v-model="editForm.is_active" type="checkbox" />
                            Active
                        </label>

                        <div class="flex justify-end gap-2">
                            <button
                                type="button"
                                @click="cancelEdit"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-100"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                            >
                                Save
                            </button>
                        </div>
                    </form>
                </div>

                <div v-if="banks.length === 0" class="px-6 py-8 text-center text-slate-500">
                    No banks added yet.
                </div>
            </div>
        </section>
    </div>
</template>
