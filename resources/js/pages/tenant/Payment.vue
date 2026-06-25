<script setup>
import StorefrontHeader from '@/components/tenant/StorefrontHeader.vue'
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CreditCard, Building, ChevronsRight, ArrowLeft, Banknote } from 'lucide-vue-next';

const props = defineProps({
    cart: Object,
    cartItems: Array,
    user: Object,
    store: Object,
    activePaymentProcessor: Object,
    allowTestPayment: Boolean,
    planFeatures: Object,
})
;

const countries = [
    'Trinidad and Tobago',
    'Jamaica',
    'Barbados',
    'Guyana',
    'United States',
    'United Kingdom',
    'Canada',
    'Australia',
];

function money(value) {
    const currency = props.store?.currency || 'USD';
    return currency + ' ' + Number(value || 0).toFixed(2);
}

const defaultPaymentMethod = computed(() => {
    if (props.allowTestPayment) {
        return 'Test Payment';
    }

    return props.activePaymentProcessor?.provider || 'Bank Transfer';
});

const paymentMethod = ref(defaultPaymentMethod.value);
const shippingSameAsBilling = ref(true);
const processingOrder = ref(false);

const form = ref({
    billing_name: props.user?.name || '',
    billing_email: props.user?.email || '',
    billing_phone: '',
    billing_address: '',
    billing_city: '',
    billing_state: '',
    billing_country: 'Trinidad and Tobago',
    billing_zipcode: '',

    shipping_name: '',
    shipping_address: '',
    shipping_city: '',
    shipping_state: '',
    shipping_country: 'Trinidad and Tobago',
    shipping_zipcode: '',

    payment_method: defaultPaymentMethod.value,
    notes: '',
});

const subtotal = computed(() => {
    if (!props.cartItems || props.cartItems.length === 0) return 0;

    return props.cartItems.reduce((sum, item) => {
        return sum + Number(item.quantity || 0) * Number(item.price || 0);
    }, 0);
});

const totalItems = computed(() => {
    if (!props.cartItems || props.cartItems.length === 0) return 0;

    return props.cartItems.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
});

const deliveryFee = computed(() => Number(props.store?.delivery_fee || 0));

const orderTotal = computed(() => {
    return subtotal.value + deliveryFee.value;
});

watch(shippingSameAsBilling, (newValue) => {
    if (newValue) {
        copyBillingToShipping();
    }
});

watch(() => [
    form.value.billing_name,
    form.value.billing_address,
    form.value.billing_city,
    form.value.billing_state,
    form.value.billing_country,
    form.value.billing_zipcode,
], () => {
    if (shippingSameAsBilling.value) {
        copyBillingToShipping();
    }
}, { deep: true });

function copyBillingToShipping() {
    form.value.shipping_name = form.value.billing_name;
    form.value.shipping_address = form.value.billing_address;
    form.value.shipping_city = form.value.billing_city;
    form.value.shipping_state = form.value.billing_state;
    form.value.shipping_country = form.value.billing_country;
    form.value.shipping_zipcode = form.value.billing_zipcode;
}

function placeOrder() {
    processingOrder.value = true;
    form.value.payment_method = paymentMethod.value;

    if (shippingSameAsBilling.value) {
        copyBillingToShipping();
    }

    router.post(route('orders.store'), form.value, {
        onError: () => {
            processingOrder.value = false;
        },
    });
}

function getTenantAssetUrl(path) {
    if (!path) return 'https://placehold.co/100x100?text=No+Image';

    if (window.tenantAssetUrl) {
        return window.tenantAssetUrl + '/' + path;
    }

    if (route().has('tenant.asset')) {
        return route('tenant.asset', path);
    }

    return `/storage/${path}`;
}
</script>

<template>
    <StorefrontHeader :store="$page.props.store || {}" :cart-items="$page.props.cartItems || $page.props.orderItems || []" />



    <Head title="Checkout" />

    <div class="min-h-screen bg-gray-50">
        <div class="container mx-auto px-4 py-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Checkout</h1>
                <p class="mt-1 text-sm text-gray-500">
                    {{ props.store?.name || 'Online Shop' }}
                    <span v-if="props.store?.email"> · {{ props.store.email }}</span>
                </p>
            </div>

            <div v-if="activePaymentProcessor && !planFeatures?.api_payment_enabled" class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Online payments are locked on your current plan. Upgrade your plan to enable online payment checkout.
            </div>

            <div v-if="cartItems && cartItems.length > 0" class="flex flex-col gap-8 lg:flex-row">
                <div class="lg:w-2/3">
                    <form @submit.prevent="placeOrder" class="space-y-6">
                        <div class="overflow-hidden rounded-lg bg-white shadow">
                            <div class="border-b p-6">
                                <h2 class="text-lg font-medium">Billing Information</h2>
                            </div>

                            <div class="space-y-4 p-6">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Full Name</label>
                                        <input v-model="form.billing_name" type="text" required class="w-full rounded-md border px-4 py-2" />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Email Address</label>
                                        <input v-model="form.billing_email" type="email" required class="w-full rounded-md border px-4 py-2" />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">Phone Number</label>
                                        <input v-model="form.billing_phone" type="tel" required class="w-full rounded-md border px-4 py-2" />
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Address</label>
                                    <input v-model="form.billing_address" type="text" required class="w-full rounded-md border px-4 py-2" />
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">City</label>
                                        <input v-model="form.billing_city" type="text" required class="w-full rounded-md border px-4 py-2" />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">State/Province</label>
                                        <input v-model="form.billing_state" type="text" required class="w-full rounded-md border px-4 py-2" />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">ZIP/Postal Code</label>
                                        <input v-model="form.billing_zipcode" type="text" required class="w-full rounded-md border px-4 py-2" />
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Country</label>
                                    <select v-model="form.billing_country" required class="w-full rounded-md border px-4 py-2">
                                        <option v-for="country in countries" :key="country" :value="country">
                                            {{ country }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-lg bg-white shadow">
                            <div class="flex items-center justify-between border-b p-6">
                                <h2 class="text-lg font-medium">Shipping Information</h2>

                                <div class="flex items-center">
                                    <input
                                        id="same_as_billing"
                                        v-model="shippingSameAsBilling"
                                        type="checkbox"
                                        class="h-4 w-4 rounded text-blue-600"
                                    />
                                    <label for="same_as_billing" class="ml-2 text-sm text-gray-700">
                                        Same as billing address
                                    </label>
                                </div>
                            </div>

                            <div v-if="!shippingSameAsBilling" class="space-y-4 p-6">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Full Name</label>
                                    <input v-model="form.shipping_name" type="text" required class="w-full rounded-md border px-4 py-2" />
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Address</label>
                                    <input v-model="form.shipping_address" type="text" required class="w-full rounded-md border px-4 py-2" />
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">City</label>
                                        <input v-model="form.shipping_city" type="text" required class="w-full rounded-md border px-4 py-2" />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">State/Province</label>
                                        <input v-model="form.shipping_state" type="text" required class="w-full rounded-md border px-4 py-2" />
                                    </div>

                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-gray-700">ZIP/Postal Code</label>
                                        <input v-model="form.shipping_zipcode" type="text" required class="w-full rounded-md border px-4 py-2" />
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Country</label>
                                    <select v-model="form.shipping_country" required class="w-full rounded-md border px-4 py-2">
                                        <option v-for="country in countries" :key="country" :value="country">
                                            {{ country }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div v-else class="bg-gray-50 p-6">
                                <p class="text-sm text-gray-600">
                                    Shipping address will be the same as billing address.
                                </p>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-lg bg-white shadow">
                            <div class="border-b p-6">
                                <h2 class="text-lg font-medium">Payment Method</h2>
                            </div>

                            <div class="space-y-4 p-6">
                                <div v-if="activePaymentProcessor && planFeatures?.api_payment_enabled" class="flex items-center">
                                    <input
                                        id="active_processor"
                                        v-model="paymentMethod"
                                        type="radio"
                                        :value="activePaymentProcessor.provider"
                                        class="h-4 w-4 text-blue-600"
                                    />

                                    <label for="active_processor" class="ml-3 flex items-center">
                                        <CreditCard class="mr-2 h-5 w-5 text-gray-600" />
                                        <span class="text-gray-700">
                                            Pay with {{ activePaymentProcessor.name || activePaymentProcessor.provider }}
                                        </span>
                                    </label>
                                </div>

                                <div v-if="paymentMethod === activePaymentProcessor?.provider" class="ml-7 rounded-md border border-blue-200 bg-blue-50 p-4">
                                    <p class="text-sm text-blue-800">
                                        Payments are processed through {{ activePaymentProcessor?.name || activePaymentProcessor?.provider }}.
                                        Environment: {{ activePaymentProcessor?.environment || 'sandbox' }}.
                                    </p>
                                </div>

                                <div v-if="allowTestPayment" class="border-t pt-4">
                                    <div class="flex items-center">
                                        <input
                                            id="test_payment"
                                            v-model="paymentMethod"
                                            type="radio"
                                            value="Test Payment"
                                            class="h-4 w-4 text-blue-600"
                                        />

                                        <label for="test_payment" class="ml-3 flex items-center">
                                            <CreditCard class="mr-2 h-5 w-5 text-gray-600" />
                                            <span class="text-gray-700">Test Payment</span>
                                        </label>
                                    </div>

                                    <div v-if="paymentMethod === 'Test Payment'" class="ml-7 mt-2 rounded-md border border-green-200 bg-green-50 p-4">
                                        <p class="text-sm text-green-800">
                                            Test mode: this marks the order as paid and creates the platform transaction and tenant payout calculation using the active processor fee rules.
                                        </p>
                                    </div>
                                </div>

                                <div class="border-t pt-4">
                                    <div class="flex items-center">
                                        <input
                                            id="bank_transfer"
                                            v-model="paymentMethod"
                                            type="radio"
                                            value="Bank Transfer"
                                            class="h-4 w-4 text-blue-600"
                                        />

                                        <label for="bank_transfer" class="ml-3 flex items-center">
                                            <Building class="mr-2 h-5 w-5 text-gray-600" />
                                            <span class="text-gray-700">Bank Transfer</span>
                                        </label>
                                    </div>

                                    <div v-if="paymentMethod === 'Bank Transfer'" class="ml-7 mt-2 rounded-md bg-gray-50 p-4">
                                        <p class="text-sm text-gray-700">
                                            Your order will remain pending until payment is confirmed by the store.
                                        </p>
                                    </div>
                                </div>

                                <div v-if="props.store?.cash_on_delivery_enabled" class="border-t pt-4">
                                    <div class="flex items-center">
                                        <input
                                            id="cash_on_delivery"
                                            v-model="paymentMethod"
                                            type="radio"
                                            value="Cash on Delivery"
                                            class="h-4 w-4 text-blue-600"
                                        />

                                        <label for="cash_on_delivery" class="ml-3 flex items-center">
                                            <Banknote class="mr-2 h-5 w-5 text-gray-600" />
                                            <span class="text-gray-700">Cash on Delivery</span>
                                        </label>
                                    </div>

                                    <div v-if="paymentMethod === 'Cash on Delivery'" class="ml-7 mt-2 rounded-md bg-gray-50 p-4">
                                        <p class="text-sm text-gray-700">
                                            Pay when your order is delivered. Your order will remain pending until payment is collected.
                                        </p>
                                    </div>
                                </div>

                                <div class="border-t pt-4">
                                    <label class="mb-1 block text-sm font-medium text-gray-700">Order Notes (Optional)</label>
                                    <textarea
                                        v-model="form.notes"
                                        rows="3"
                                        class="w-full rounded-md border px-4 py-2"
                                        placeholder="Special instructions for delivery or any other notes..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="lg:w-1/3">
                    <div class="sticky top-24 rounded-lg bg-white p-6 shadow">
                        <h2 class="mb-4 text-lg font-bold">Order Summary</h2>

                        <div class="mb-4 max-h-64 overflow-y-auto">
                            <div v-for="item in cartItems" :key="item.id" class="flex gap-4 border-b py-3 last:border-0">
                                <div class="h-16 w-16 shrink-0">
                                    <img
                                        :src="item.product.images && item.product.images.length > 0
                                            ? getTenantAssetUrl(item.product.images[0].image_path)
                                            : 'https://placehold.co/100x100?text=No+Image'"
                                        class="h-full w-full rounded-md object-cover"
                                        :alt="item.product.name"
                                    />
                                </div>

                                <div class="flex-grow">
                                    <h3 class="font-medium">{{ item.product.name }}</h3>

                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>Qty: {{ item.quantity }}</span>
                                        <span>{{ money(item.price) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6 space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Subtotal ({{ totalItems }} items)</span>
                                <span class="font-medium">{{ money(subtotal) }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-gray-600">Delivery</span>
                                <span class="font-medium">{{ money(deliveryFee) }}</span>
                            </div>

                            <div class="mt-3 border-t pt-3">
                                <div class="flex justify-between">
                                    <span class="font-bold">Total</span>
                                    <span class="font-bold">{{ money(orderTotal) }}</span>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="flex w-full items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-3 text-center font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="processingOrder"
                            @click="placeOrder"
                        >
                            <template v-if="!processingOrder">
                                Place Order
                                <ChevronsRight class="h-5 w-5" />
                            </template>

                            <template v-else>
                                Processing...
                            </template>
                        </button>

                        <Link
                            :href="route('cart.index')"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-3 text-center font-medium text-gray-700 transition hover:bg-gray-50"
                        >
                            <ArrowLeft class="h-5 w-5" />
                            Back to Cart
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="mx-auto max-w-md rounded-lg bg-white p-8 text-center shadow">
                <h2 class="mb-2 text-2xl font-medium text-gray-800">Your cart is empty</h2>
                <p class="mb-6 text-gray-600">You cannot proceed to checkout with an empty cart.</p>

                <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-2 text-center font-medium text-white transition hover:bg-blue-700"
                >
                    <ArrowLeft class="h-5 w-5" />
                    Browse Products
                </Link>
            </div>
        </div>
    </div>
</template>
