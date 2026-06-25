<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import CentralLayout from '@/layouts/CentralLayout.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    settings: Array,
    activeSetting: Object,
    authTypes: Array,
    configTypes: Array,
});

const selectedId = ref(props.activeSetting?.id || props.settings?.[0]?.id || null);

const selectedSetting = computed(() => {
    return props.settings?.find((setting) => setting.id === selectedId.value) || null;
});

const blankCustom = {
    id: null,
    name: '',
    config_type: 'custom',
    provider: '',
    environment: 'sandbox',
    country_code: 'TT',
    currency: 'TTD',
    account_number: '',
    fee_structure: 'merchant_absorb',
    auth_type: 'none',
    base_url: '',
    checkout_endpoint: '',
    verify_endpoint: '',
    refund_endpoint: '',
    payout_endpoint: '',
    token_endpoint: '',
    merchant_id: '',
    account_id: '',
    username: '',
    processor_fee_percent: 0,
    processor_fee_fixed: 0,
    headers_template: null,
    request_template: null,
    response_mapping: null,
    metadata: null,
    is_active: false,
};

const editing = ref(false);
const creatingCustom = ref(false);

function stringifyJson(value) {
    if (!value) return '';
    try {
        return JSON.stringify(value, null, 2);
    } catch {
        return '';
    }
}

const form = useForm({
    id: null,
    name: '',
    config_type: 'saved',
    provider: 'wipay',
    environment: 'sandbox',
    country_code: 'TT',
    currency: 'TTD',
    account_number: '',
    fee_structure: 'merchant_absorb',
    auth_type: 'none',
    base_url: '',
    checkout_endpoint: '',
    verify_endpoint: '',
    refund_endpoint: '',
    payout_endpoint: '',
    token_endpoint: '',
    api_key: '',
    public_key: '',
    secret_key: '',
    private_key: '',
    webhook_secret: '',
    merchant_id: '',
    account_id: '',
    username: '',
    password: '',
    bearer_token: '',
    processor_fee_percent: 0,
    processor_fee_fixed: 0,
    headers_template: '',
    request_template: '',
    response_mapping: '',
    metadata: '',
    is_active: false,
});

function loadSetting(setting) {
    const source = setting || blankCustom;

    form.id = source.id;
    form.name = source.name || '';
    form.config_type = source.config_type || 'saved';
    form.provider = source.provider || '';
    form.environment = source.environment || 'sandbox';
    form.country_code = source.country_code || 'TT';
    form.currency = source.currency || 'TTD';
    form.account_number = source.account_number || '';
    form.fee_structure = source.fee_structure || 'merchant_absorb';
    form.auth_type = source.auth_type || 'none';
    form.base_url = source.base_url || '';
    form.checkout_endpoint = source.checkout_endpoint || '';
    form.verify_endpoint = source.verify_endpoint || '';
    form.refund_endpoint = source.refund_endpoint || '';
    form.payout_endpoint = source.payout_endpoint || '';
    form.token_endpoint = source.token_endpoint || '';
    form.api_key = '';
    form.public_key = '';
    form.secret_key = '';
    form.private_key = '';
    form.webhook_secret = '';
    form.merchant_id = source.merchant_id || '';
    form.account_id = source.account_id || '';
    form.username = source.username || '';
    form.password = '';
    form.bearer_token = '';
    form.processor_fee_percent = source.processor_fee_percent || 0;
    form.processor_fee_fixed = source.processor_fee_fixed || 0;
    form.headers_template = stringifyJson(source.headers_template);
    form.request_template = stringifyJson(source.request_template);
    form.response_mapping = stringifyJson(source.response_mapping);
    form.metadata = stringifyJson(source.metadata);
    form.is_active = !!source.is_active;
}

function startEdit(setting) {
    creatingCustom.value = false;
    editing.value = true;
    selectedId.value = setting.id;
    loadSetting(setting);
}

function startCreateCustom() {
    creatingCustom.value = true;
    editing.value = true;
    selectedId.value = null;
    loadSetting(blankCustom);
}

function cancelEdit() {
    editing.value = false;
    creatingCustom.value = false;
    form.clearErrors();
}

function save() {
    form.put('/central/payment-settings', {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = false;
            creatingCustom.value = false;
        },
    });
}

function money(value, currency = 'TTD') {
    return currency + ' ' + Number(value || 0).toFixed(2);
}

function percent(value) {
    return Number(value || 0).toFixed(2) + '%';
}

function statusClass(setting) {
    return setting?.is_active
        ? 'bg-green-100 text-green-700'
        : 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <Head title="Payment Settings" />

    <CentralLayout
        title="Payment Settings"
        description="Manage saved and custom payment API configurations. Only one payment processor can be active at a time."
    >
        <template #actions>
            <button
                type="button"
                @click="startCreateCustom"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
            >
                Add Custom Configuration
            </button>
        </template>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <section class="rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-1">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h2 class="text-lg font-semibold">Saved Configurations</h2>
                    <p class="mt-1 text-sm text-slate-500">Choose which payment API powers tenant checkout.</p>
                </div>

                <div class="divide-y divide-slate-200">
                    <button
                        v-for="setting in settings"
                        :key="setting.id"
                        type="button"
                        @click="selectedId = setting.id"
                        class="block w-full px-6 py-4 text-left hover:bg-slate-50"
                        :class="selectedId === setting.id ? 'bg-indigo-50' : ''"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ setting.name || setting.provider }}</p>
                                <p class="text-xs uppercase tracking-wide text-slate-500">
                                    {{ setting.config_type }} · {{ setting.provider }} · {{ setting.environment }}
                                </p>
                            </div>

                            <span class="rounded-full px-2 py-1 text-xs font-medium" :class="statusClass(setting)">
                                {{ setting.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-slate-500">
                            Processor fee: {{ percent(setting.processor_fee_percent) }} + {{ money(setting.processor_fee_fixed, setting.currency) }}
                        </p>
                    </button>

                    <div v-if="!settings || settings.length === 0" class="px-6 py-8 text-center text-sm text-slate-500">
                        No payment configurations found.
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                <div class="border-b border-slate-200 px-6 py-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold">
                                {{ editing ? (creatingCustom ? 'New Custom Configuration' : 'Edit Configuration') : 'Configuration Details' }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Configure API credentials, endpoints, fee rules, and response mapping.
                            </p>
                        </div>

                        <button
                            v-if="selectedSetting && !editing"
                            type="button"
                            @click="startEdit(selectedSetting)"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"
                        >
                            Edit
                        </button>
                    </div>
                </div>

                <div v-if="!editing && selectedSetting" class="space-y-6 p-6">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Provider</p>
                            <p class="mt-1 font-semibold">{{ selectedSetting.provider }}</p>
                        </div>

                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Environment</p>
                            <p class="mt-1 font-semibold capitalize">{{ selectedSetting.environment }}</p>
                        </div>

                        <div class="rounded-lg bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-slate-500">Active</p>
                            <p class="mt-1 font-semibold">{{ selectedSetting.is_active ? 'Yes' : 'No' }}</p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 p-4">
                        <h3 class="font-semibold">Fee Configuration</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Processor fee:
                            <strong>{{ percent(selectedSetting.processor_fee_percent) }} + {{ money(selectedSetting.processor_fee_fixed, selectedSetting.currency) }}</strong>
                        </p>
                    </div>

                    <div class="rounded-lg border border-slate-200 p-4">
                        <h3 class="font-semibold">API Endpoints</h3>
                        <dl class="mt-3 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                            <div>
                                <dt class="text-slate-500">Base URL</dt>
                                <dd class="break-all font-medium">{{ selectedSetting.base_url || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Checkout</dt>
                                <dd class="break-all font-medium">{{ selectedSetting.checkout_endpoint || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Verify</dt>
                                <dd class="break-all font-medium">{{ selectedSetting.verify_endpoint || '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">Refund</dt>
                                <dd class="break-all font-medium">{{ selectedSetting.refund_endpoint || '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg border border-slate-200 p-4">
                        <h3 class="font-semibold">Stored Secrets</h3>
                        <div class="mt-3 grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
                            <span class="rounded-full bg-slate-100 px-3 py-2">API Key: {{ selectedSetting.has_api_key ? 'Saved' : '—' }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-2">Secret: {{ selectedSetting.has_secret_key ? 'Saved' : '—' }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-2">Bearer: {{ selectedSetting.has_bearer_token ? 'Saved' : '—' }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-2">Webhook: {{ selectedSetting.has_webhook_secret ? 'Saved' : '—' }}</span>
                        </div>
                    </div>
                </div>

                <form v-else-if="editing" @submit.prevent="save" class="space-y-6 p-6">
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                        Activating this configuration will automatically deactivate every other payment configuration.
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Configuration Name</label>
                            <input v-model="form.name" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="WiPay Trinidad Live" />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Configuration Type</label>
                            <select v-model="form.config_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                                <option value="saved">Saved Provider</option>
                                <option value="custom">Custom API</option>
                            </select>
                            <p v-if="form.errors.config_type" class="mt-1 text-sm text-red-600">{{ form.errors.config_type }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Provider Code</label>
                            <input v-model="form.provider" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="wipay, stripe, custom_gateway" />
                            <p v-if="form.errors.provider" class="mt-1 text-sm text-red-600">{{ form.errors.provider }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Environment</label>
                            <select v-model="form.environment" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                                <option value="sandbox">Sandbox</option>
                                <option value="live">Live</option>
                            </select>
                            <p v-if="form.errors.environment" class="mt-1 text-sm text-red-600">{{ form.errors.environment }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Country Code</label>
                            <input v-model="form.country_code" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="TT" />
                            <p v-if="form.errors.country_code" class="mt-1 text-sm text-red-600">{{ form.errors.country_code }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Currency</label>
                            <input v-model="form.currency" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="TTD" />
                            <p v-if="form.errors.currency" class="mt-1 text-sm text-red-600">{{ form.errors.currency }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Processor Fee %</label>
                            <input v-model="form.processor_fee_percent" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                            <p v-if="form.errors.processor_fee_percent" class="mt-1 text-sm text-red-600">{{ form.errors.processor_fee_percent }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Processor Fixed Fee</label>
                            <input v-model="form.processor_fee_fixed" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                            <p v-if="form.errors.processor_fee_fixed" class="mt-1 text-sm text-red-600">{{ form.errors.processor_fee_fixed }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Fee Structure</label>
                            <select v-model="form.fee_structure" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                                <option value="merchant_absorb">Merchant Absorbs</option>
                                <option value="customer_pay">Customer Pays</option>
                                <option value="split">Split</option>
                            </select>
                            <p v-if="form.errors.fee_structure" class="mt-1 text-sm text-red-600">{{ form.errors.fee_structure }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Auth Type</label>
                            <select v-model="form.auth_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                                <option v-for="type in authTypes" :key="type" :value="type">{{ type }}</option>
                            </select>
                            <p v-if="form.errors.auth_type" class="mt-1 text-sm text-red-600">{{ form.errors.auth_type }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Base URL</label>
                            <input v-model="form.base_url" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="https://api.provider.com" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Checkout Endpoint</label>
                            <input v-model="form.checkout_endpoint" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="/checkout" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Verify Endpoint</label>
                            <input v-model="form.verify_endpoint" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="/verify/{transaction_id}" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Refund Endpoint</label>
                            <input v-model="form.refund_endpoint" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="/refund" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Payout Endpoint</label>
                            <input v-model="form.payout_endpoint" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="/payouts" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Token Endpoint</label>
                            <input v-model="form.token_endpoint" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="/oauth/token" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Account Number</label>
                            <input v-model="form.account_number" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Merchant ID</label>
                            <input v-model="form.merchant_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Account ID</label>
                            <input v-model="form.account_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Username</label>
                            <input v-model="form.username" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">API Key</label>
                            <input v-model="form.api_key" type="password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Leave blank to keep current" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Secret Key</label>
                            <input v-model="form.secret_key" type="password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Leave blank to keep current" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Public Key</label>
                            <input v-model="form.public_key" type="password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Leave blank to keep current" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Private Key</label>
                            <input v-model="form.private_key" type="password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Leave blank to keep current" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Password</label>
                            <input v-model="form.password" type="password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Leave blank to keep current" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Bearer Token</label>
                            <input v-model="form.bearer_token" type="password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Leave blank to keep current" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Webhook Secret</label>
                            <input v-model="form.webhook_secret" type="password" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Leave blank to keep current" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Headers Template JSON</label>
                            <textarea v-model="form.headers_template" rows="5" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm" placeholder='{"Authorization":"Bearer {{bearer_token}}"}'></textarea>
                            <p v-if="form.errors.headers_template" class="mt-1 text-sm text-red-600">{{ form.errors.headers_template }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Request Body Template JSON</label>
                            <textarea v-model="form.request_template" rows="6" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm" placeholder='{"amount":"{{amount}}","currency":"{{currency}}"}'></textarea>
                            <p v-if="form.errors.request_template" class="mt-1 text-sm text-red-600">{{ form.errors.request_template }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">Response Mapping JSON</label>
                            <textarea v-model="form.response_mapping" rows="6" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm" placeholder='{"transaction_id":"data.id","checkout_url":"data.url","status":"data.status"}'></textarea>
                            <p v-if="form.errors.response_mapping" class="mt-1 text-sm text-red-600">{{ form.errors.response_mapping }}</p>
                        </div>
                    </div>

                    <label class="flex items-center gap-2">
                        <input v-model="form.is_active" type="checkbox" />
                        <span class="text-sm font-medium text-slate-700">Enable this payment configuration now</span>
                    </label>

                    <div class="flex justify-end gap-3 border-t border-slate-200 pt-6">
                        <button type="button" @click="cancelEdit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">
                            Cancel
                        </button>

                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700" :disabled="form.processing">
                            Save Configuration
                        </button>
                    </div>
                </form>

                <div v-else class="p-8 text-center text-slate-500">
                    Select a payment configuration or create a custom one.
                </div>
            </section>
        </div>
    </CentralLayout>
</template>
