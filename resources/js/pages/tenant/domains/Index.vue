<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    tenant: Object,
    plan: Object,
    customDomainAllowed: Boolean,
    dnsTargets: Object,
});

const selectedProvider = ref('porkbun');

const form = useForm({
    custom_domain: props.tenant.custom_domain || '',
});

const providers = {
    porkbun: {
        name: 'Porkbun',
        steps: [
            'Log in to Porkbun.',
            'Go to Domain Management.',
            'Find your domain and click Details.',
            'Open DNS Records.',
            'Add a CNAME record.',
            'For Host, enter www if connecting www.yourdomain.com.',
            'For Answer/Value, enter the platform CNAME target shown below.',
            'Save the record and wait for DNS propagation.',
        ],
    },
    cloudflare: {
        name: 'Cloudflare',
        steps: [
            'Log in to Cloudflare.',
            'Select the domain.',
            'Go to DNS, then Records.',
            'Click Add record.',
            'Choose Type: CNAME.',
            'For Name, enter www if connecting www.yourdomain.com.',
            'For Target, enter the platform CNAME target shown below.',
            'Set Proxy status depending on your platform setup. For first testing, DNS only is safest.',
            'Save the record and wait for DNS propagation.',
        ],
    },
    godaddy: {
        name: 'GoDaddy',
        steps: [
            'Log in to GoDaddy.',
            'Open your domain.',
            'Go to DNS.',
            'Click Add New Record.',
            'Choose Type: CNAME.',
            'For Name/Host, enter www if connecting www.yourdomain.com.',
            'For Value, enter the platform CNAME target shown below.',
            'Save the record and wait for DNS propagation.',
        ],
    },
    namecheap: {
        name: 'Namecheap',
        steps: [
            'Log in to Namecheap.',
            'Go to Domain List.',
            'Click Manage beside your domain.',
            'Open the Advanced DNS tab.',
            'Find Host Records and click Add New Record.',
            'Choose CNAME Record.',
            'For Host, enter www if connecting www.yourdomain.com.',
            'For Value, enter the platform CNAME target shown below.',
            'Save changes and wait for DNS propagation.',
        ],
    },
    other: {
        name: 'Other Provider',
        steps: [
            'Log in to your domain provider.',
            'Open DNS management for your domain.',
            'Add a CNAME record.',
            'For Host/Name, enter www if connecting www.yourdomain.com.',
            'For Target/Value/Answer, enter the platform CNAME target shown below.',
            'Save and wait for DNS propagation.',
        ],
    },
};

const providerSteps = computed(() => providers[selectedProvider.value]);

function submit() {
    form.post('/manage/domains', {
        preserveScroll: true,
        onError: (errors) => {
            console.log('Domain form errors:', errors);
        },
    });
}

function domainUrl(domain) {
    return 'http://' + domain + ':8000/home';
}

function formatDate(date) {
    if (!date) return '—';
    return new Date(date).toLocaleString();
}
</script>

<template>
    <Head title="Domains" />

    <AppLayout>
        <div class="min-h-screen bg-slate-50 px-6 py-8 text-slate-900">
            <div class="mx-auto max-w-5xl">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold">Domains</h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Manage your store’s default domain and connect your own custom domain.
                    </p>
                </div>

                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">Default Store Domains</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        These are the platform-generated domains currently attached to your store.
                    </p>

                    <div class="mt-5 divide-y divide-slate-200 rounded-lg border border-slate-200">
                        <div
                            v-for="domain in tenant.domains"
                            :key="domain.id"
                            class="flex items-center justify-between px-4 py-3"
                        >
                            <div>
                                <p class="font-medium">{{ domain.domain }}</p>
                                <p class="text-sm text-slate-500">Store domain</p>
                            </div>

                            <a
                                :href="domainUrl(domain.domain)"
                                target="_blank"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-100"
                            >
                                Open Store
                            </a>
                        </div>
                    </div>
                </section>

                <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                        <div>
                            <h2 class="text-lg font-semibold">Custom Domain</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Connect a domain you already purchased from your domain provider.
                            </p>
                        </div>

                        <span
                            class="rounded-full px-3 py-1 text-xs font-medium"
                            :class="customDomainAllowed ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                        >
                            {{ customDomainAllowed ? 'Available on your plan' : 'Upgrade required' }}
                        </span>
                    </div>

                    <div v-if="!customDomainAllowed" class="mt-5 rounded-xl border border-yellow-200 bg-yellow-50 p-5">
                        <h3 class="font-semibold text-yellow-900">Custom domains are not included in your current plan.</h3>
                        <p class="mt-2 text-sm text-yellow-800">
                            Your current plan
                            <span class="font-semibold">{{ plan?.name || 'No Plan' }}</span>
                            does not allow custom domains. Upgrade to a plan that includes custom domains to connect your own domain.
                        </p>

                        <Link
                            href="/dashboard"
                            class="mt-4 inline-flex rounded-lg bg-yellow-600 px-4 py-2 text-sm font-medium text-white hover:bg-yellow-700"
                        >
                            View Plan Details
                        </Link>
                    </div>

                    <div v-else>
                        <div v-if="tenant.custom_domain" class="mt-5 rounded-lg bg-slate-50 p-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <p class="text-sm text-slate-500">Connected Domain</p>
                                    <p class="mt-1 font-semibold">{{ tenant.custom_domain }}</p>
                                </div>

                                <div>
                                    <p class="text-sm text-slate-500">Status</p>
                                    <span class="mt-1 inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">
                                        {{ tenant.custom_domain_status || 'connected_dns_required' }}
                                    </span>
                                </div>

                                <div>
                                    <p class="text-sm text-slate-500">Connected At</p>
                                    <p class="mt-1 font-semibold">{{ formatDate(tenant.custom_domain_connected_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="mt-6">
                            <label class="block text-sm font-medium text-slate-700">Custom Domain</label>
                            <input
                                v-model="form.custom_domain"
                                type="text"
                                placeholder="www.yourstore.com"
                                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-slate-900 shadow-sm"
                            />
                            <p class="mt-1 text-xs text-slate-500">
                                Enter only the domain. Example: www.yourstore.com
                            </p>
                            <p v-if="form.errors.custom_domain" class="mt-1 text-sm text-red-600">
                                {{ form.errors.custom_domain }}
                            </p>

                            <div class="mt-5 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
                                >
                                    {{ form.processing ? 'Saving...' : 'Connect Domain' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold">DNS Setup Instructions</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Choose your domain provider and follow the steps to point your domain to this platform.
                    </p>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-slate-700">Domain Provider</label>
                        <select
                            v-model="selectedProvider"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm"
                        >
                            <option value="porkbun">Porkbun</option>
                            <option value="cloudflare">Cloudflare</option>
                            <option value="godaddy">GoDaddy</option>
                            <option value="namecheap">Namecheap</option>
                            <option value="other">Other Provider</option>
                        </select>
                    </div>

                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <h3 class="font-semibold">{{ providerSteps.name }} steps</h3>

                        <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm text-slate-700">
                            <li v-for="step in providerSteps.steps" :key="step">
                                {{ step }}
                            </li>
                        </ol>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-lg bg-slate-900 p-4 text-white">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Recommended CNAME Target</p>
                            <p class="mt-2 font-mono text-sm">{{ dnsTargets.cname_target }}</p>
                        </div>

                        <div class="rounded-lg bg-slate-900 p-4 text-white">
                            <p class="text-xs uppercase tracking-wide text-slate-400">Root domain A Record IP</p>
                            <p class="mt-2 font-mono text-sm">{{ dnsTargets.a_record_ip }}</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-lg bg-blue-50 p-4 text-sm text-blue-800">
                        <p class="font-semibold">Recommended setup</p>
                        <p class="mt-1">
                            Use <span class="font-mono">www.yourdomain.com</span> as the custom domain and point it using a CNAME record.
                            Root/apex domains like <span class="font-mono">yourdomain.com</span> may require an A record or CNAME flattening depending on the provider.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
