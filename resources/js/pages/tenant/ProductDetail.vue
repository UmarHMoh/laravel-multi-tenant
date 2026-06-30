<script setup>
import StorefrontHeader from '@/components/tenant/StorefrontHeader.vue'
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ShoppingCart, ChevronLeft, Plus, Minus, Heart, Share2, Star, Truck } from 'lucide-vue-next';

const props = defineProps({
    product: Object,
    cartItemCount: Number,
    relatedProducts: Array,
    productRecommendations: { type: Array, default: () => [] },
    productThemePage: Object,
    productThemeSections: { type: [Array, Object], default: () => ({ sections: [] }) },
    themeSections: { type: [Array, Object], default: () => ({ sections: [] }) },
});

// Create a local ref to track cart item count for reactivity
const localCartCount = ref(props.cartItemCount || 0);

const quantity = ref(1);
const selectedImageIndex = ref(0);
const activeTab = ref('details');

// Function to get the correct tenant asset URL
function getTenantAssetUrl(path) {
    if (!path) return 'https://placehold.co/400x400?text=No+Image';

    // Use Laravel's tenant_asset helper if available through a global window variable
    if (window.tenantAssetUrl) {
        return window.tenantAssetUrl + '/' + path;
    }

    // Fallback to constructing the path using the tenant_asset route
    if (route().has('tenant.asset')) {
        return route('tenant.asset', path);
    }

    // Fallback to direct storage path
    return `/storage/${path}`;
}

// Increment quantity (limited by stock)
function incrementQuantity() {
    if (quantity.value < props.product.stock) {
        quantity.value++;
    }
}

// Decrement quantity (minimum 1)
function decrementQuantity() {
    if (quantity.value > 1) {
        quantity.value--;
    }
}

// Add to cart with selected quantity
function addToCart() {
    router.post(route('cart.add'), {
        product_id: props.product.id,
        quantity: quantity.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local cart count
            localCartCount.value += quantity.value;

            // Show a success message (optional)
            alert('Product added to cart!');

            // Reset quantity back to 1
            quantity.value = 1;
        }
    });
}

// Add related product to cart
function addRelatedToCart(productId) {
    router.post(route('cart.add'), {
        product_id: productId,
        quantity: 1
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Update local cart count
            localCartCount.value += 1;
        }
    });
}

// Get current selected image
const selectedImage = computed(() => {
    if (props.product.images && props.product.images.length > 0) {
        return getTenantAssetUrl(props.product.images[selectedImageIndex.value].image_path);
    }
    return 'https://placehold.co/600x600?text=No+Image';
});

// Check if the product is in stock
const isInStock = computed(() => {
    return props.product.stock > 0;
});

// Format currency
function formatCurrency(value) {
    return Number(value).toFixed(2);
}

const productThemeSectionsList = () => {
    const payload = props.productThemeSections || props.themeSections || {};

    if (Array.isArray(payload.sections)) {
        return payload.sections;
    }

    if (Array.isArray(payload)) {
        return payload;
    }

    return [];
};

const productPageHasThemeSections = () => productThemeSectionsList().length > 0;

function sectionSetting(section, key, fallback = null) {
    return section?.settings?.[key] ?? fallback;
}

function shouldShow(section, key, fallback = true) {
    return sectionSetting(section, key, fallback) !== false;
}


function productDetailsLayoutClass(section) {
    const layout = sectionSetting(section, 'layout', 'two_column');

    if (layout === 'stacked') return 'grid grid-cols-1 gap-8';
    if (layout === 'image_right') return 'grid grid-cols-1 gap-8 lg:grid-cols-2 lg:[&>*:first-child]:order-2';

    return 'grid grid-cols-1 gap-8 lg:grid-cols-2';
}

function productDetailsInfoClass(section) {
    return sectionSetting(section, 'sticky_info', false)
        ? 'lg:sticky lg:top-8 self-start'
        : '';
}

function productDetailsImageClass(section) {
    return sectionSetting(section, 'image_style', 'contained') === 'cover'
        ? 'h-[420px] w-full object-cover'
        : 'h-[420px] w-full object-contain';
}


function productGalleryItems() {
    return Array.isArray(props.product.images) ? props.product.images : [];
}

function productGalleryStyle(section) {
    return sectionSetting(section, 'gallery_style', 'thumbnails');
}

function productThumbnailWrapClass(section) {
    return sectionSetting(section, 'thumbnail_position', 'bottom') === 'side'
        ? 'mt-4 grid grid-cols-4 gap-2 lg:mt-0 lg:grid-cols-1'
        : 'mt-4 grid grid-cols-5 gap-2';
}

function productOptionPlaceholderText(section) {
    return sectionSetting(section, 'variant_placeholder_text', 'Product options such as size and color will appear here.');
}

function productSectionProducts(section) {
    const settings = section?.settings || {};
    const source = settings.related_source || 'category';
    const limit = Number(settings.limit || 4);

    let products = [];

    if (source === 'manual') {
        const ids = [
            ...(Array.isArray(settings.product_ids) ? settings.product_ids : []),
            ...(Array.isArray(settings.cards) ? settings.cards.map((card) => card?.product_id) : []),
        ].filter(Boolean).map((id) => String(id));

        products = props.productRecommendations.filter((item) => ids.includes(String(item.id)));
    } else if (source === 'all') {
        products = props.productRecommendations;
    } else {
        products = props.relatedProducts || [];
    }

    return products.slice(0, limit || 4);
}

function productImageForCard(product) {
    if (Array.isArray(product?.images) && product.images.length > 0) {
        const primary = product.images.find((image) => image.is_primary) || product.images[0];
        return getTenantAssetUrl(primary.image_path);
    }

    return 'https://placehold.co/600x600?text=Product';
}

function productButtonWrapClass(section) {
    return sectionSetting(section, 'button_layout', 'inline') === 'stacked'
        ? 'mt-8 flex flex-col gap-3'
        : 'mt-8 flex flex-col gap-4 sm:flex-row';
}

function productSecondaryButtonClass(section) {
    return sectionSetting(section, 'button_style', 'solid') === 'outline'
        ? 'rounded-xl border border-gray-950 bg-white px-6 py-3 font-bold text-gray-950 hover:bg-gray-50'
        : 'rounded-xl bg-gray-950 px-6 py-3 font-bold text-white hover:bg-gray-800';
}

function productDescriptionSectionClass(section) {
    const width = sectionSetting(section, 'width', 'normal');
    const layout = sectionSetting(section, 'layout', 'plain');

    const widthClass = width === 'wide' ? 'max-w-7xl' : width === 'narrow' ? 'max-w-3xl' : 'max-w-5xl';
    const layoutClass = layout === 'card' ? 'rounded-3xl bg-white p-8 shadow-sm' : 'rounded-3xl bg-white p-8 shadow-sm';

    return `${widthClass} ${layoutClass}`;
}

function sectionLayoutClass(section) {
    return sectionSetting(section, 'layout', 'two_column') === 'stacked'
        ? 'grid grid-cols-1 gap-8'
        : 'grid grid-cols-1 lg:grid-cols-2 gap-10';
}

function buyNow() {
    router.post(route('cart.add'), {
        product_id: props.product.id,
        quantity: quantity.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            localCartCount.value += quantity.value;
            router.visit(route('checkout.index'));
        }
    });
}

function productThemeSectionKey(section, index) {
    return section?.id || `${section?.type || 'section'}_${index}`;
}

</script>

<template>
    <StorefrontHeader :store="$page.props.store || {}" :cart-items="$page.props.cartItems || $page.props.orderItems || []" />



    <Head :title="product.name" />

    <main
        v-if="productPageHasThemeSections()"
        data-product-page-sections
        class="bg-gray-50"
    >
        <section
            v-for="(section, index) in productThemeSectionsList()"
            :key="productThemeSectionKey(section, index)"
            data-product-theme-section
            :data-product-section-type="section.type"
            class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
        >
            <div
                v-if="section.type === 'product_details'"
                data-product-details-section
                class="rounded-3xl bg-white p-6 shadow-sm"
            >
                <div :class="productDetailsLayoutClass(section)">
                    <div v-if="shouldShow(section, 'show_images', true)">
                        <div class="overflow-hidden rounded-2xl border bg-gray-50">
                            <img
                                :src="selectedImage"
                                :alt="product.name"
                                :class="productDetailsImageClass(section)"
                            />
                        </div>

                        <div
                            v-if="productGalleryStyle(section) === 'thumbnails' && productGalleryItems().length > 1"
                            :class="productThumbnailWrapClass(section)"
                            data-product-gallery-thumbnails
                        >
                            <button
                                v-for="(image, imageIndex) in product.images"
                                :key="image.id || imageIndex"
                                type="button"
                                @click="selectedImageIndex = imageIndex"
                                class="rounded-xl border bg-white p-1"
                                :class="{ 'ring-2 ring-blue-500': selectedImageIndex === imageIndex }"
                            >
                                <img
                                    :src="getTenantAssetUrl(image.image_path)"
                                    :alt="`${product.name} - Image ${imageIndex + 1}`"
                                    class="h-20 w-full object-contain"
                                />
                            </button>
                        </div>

                        <div
                            v-if="productGalleryStyle(section) === 'dots' && productGalleryItems().length > 1"
                            class="mt-4 flex justify-center gap-2"
                            data-product-gallery-dots
                        >
                            <button
                                v-for="(image, imageIndex) in productGalleryItems()"
                                :key="`dot-${image.id || imageIndex}`"
                                type="button"
                                @click="selectedImageIndex = imageIndex"
                                class="h-2.5 w-2.5 rounded-full"
                                :class="selectedImageIndex === imageIndex ? 'bg-gray-950' : 'bg-gray-300'"
                                :aria-label="`View image ${imageIndex + 1}`"
                            />
                        </div>
                    </div>

                    <div :class="productDetailsInfoClass(section)">
                        <p v-if="product.category" class="text-sm font-bold uppercase tracking-wide text-gray-500">
                            {{ product.category.name }}
                        </p>

                        <h1
                            v-if="shouldShow(section, 'show_title', true)"
                            class="mt-2 text-4xl font-black tracking-tight text-gray-950"
                        >
                            {{ product.name }}
                        </h1>

                        <p
                            v-if="shouldShow(section, 'show_price', true)"
                            class="mt-5 text-3xl font-black text-gray-950"
                        >
                            ${{ formatCurrency(product.price) }}
                        </p>

                        <p
                            v-if="shouldShow(section, 'show_description', true)"
                            class="mt-5 text-base leading-7 text-gray-700"
                        >
                            {{ product.description }}
                        </p>

                        <div
                            v-if="shouldShow(section, 'show_variant_options', false)"
                            class="mt-6 rounded-2xl border border-dashed bg-gray-50 p-4"
                            data-product-variant-options-foundation
                        >
                            <p class="text-sm font-black text-gray-900">Product options</p>
                            <p class="mt-1 text-sm text-gray-600">{{ productOptionPlaceholderText(section) }}</p>
                        </div>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <span
                                :class="isInStock ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                class="rounded-full px-3 py-1 text-xs font-bold"
                            >
                                {{ isInStock ? `In Stock (${product.stock})` : 'Out of Stock' }}
                            </span>

                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">
                                SKU: {{ product.sku }}
                            </span>
                        </div>

                        <div
                            v-if="isInStock && (shouldShow(section, 'show_add_to_cart', true) || shouldShow(section, 'show_buy_now', true))"
                            :class="productButtonWrapClass(section)"
                            data-product-button-layout
                        >
                            <div v-if="shouldShow(section, 'show_quantity_selector', true)" class="flex items-center rounded-xl border border-gray-300 bg-white" data-product-quantity-selector>
                                <button
                                    type="button"
                                    @click="decrementQuantity"
                                    class="px-4 py-3 text-gray-600 hover:bg-gray-100"
                                    :disabled="quantity <= 1"
                                >
                                    <Minus class="h-5 w-5" />
                                </button>

                                <span class="w-12 text-center font-bold">{{ quantity }}</span>

                                <button
                                    type="button"
                                    @click="incrementQuantity"
                                    class="px-4 py-3 text-gray-600 hover:bg-gray-100"
                                    :disabled="quantity >= product.stock"
                                >
                                    <Plus class="h-5 w-5" />
                                </button>
                            </div>

                            <button
                                v-if="shouldShow(section, 'show_add_to_cart', true)"
                                type="button"
                                data-product-add-to-cart
                                @click="addToCart"
                                class="rounded-xl bg-blue-600 px-6 py-3 font-bold text-white hover:bg-blue-700"
                            >
                                {{ sectionSetting(section, 'add_to_cart_label', 'Add to Cart') }}
                            </button>

                            <button
                                v-if="shouldShow(section, 'show_buy_now', true)"
                                type="button"
                                data-product-buy-now
                                @click="buyNow"
                                :class="productSecondaryButtonClass(section)"
                            >
                                {{ sectionSetting(section, 'buy_now_label', 'Buy now') }}
                            </button>
                        </div>

                        <p v-else-if="!isInStock" class="mt-8 font-bold text-red-600">
                            This product is currently out of stock.
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-else-if="section.type === 'product_description'"
                data-product-description-section
                :class="productDescriptionSectionClass(section)"
                data-product-description-layout
            >
                <h2 class="text-2xl font-black tracking-tight text-gray-950">
                    {{ sectionSetting(section, 'heading', 'Description') }}
                </h2>

                <p v-if="shouldShow(section, 'show_full_description', true)" class="mt-4 leading-7 text-gray-700">
                    {{ product.description || 'No product description available yet.' }}
                </p>
            </div>

            <div
                v-else-if="section.type === 'product_reviews'"
                data-product-reviews-section
                class="rounded-3xl border border-dashed border-gray-300 bg-white p-8 shadow-sm"
            >
                <p class="text-sm font-bold uppercase tracking-wide text-gray-500">
                    Product reviews
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight text-gray-950">
                    {{ sectionSetting(section, 'heading', 'Reviews') }}
                </h2>

                <p class="mt-3 text-gray-600">
                    {{ sectionSetting(section, 'placeholder', 'Reviews are coming soon.') }}
                </p>

                <div
                    v-if="shouldShow(section, 'show_rating_summary', true)"
                    data-product-rating-summary-placeholder
                    class="mt-6 rounded-2xl bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-700"
                >
                    ★★★★★ {{ sectionSetting(section, 'rating_placeholder_text', 'Product rating summary placeholder') }}
                </div>

                <div
                    v-if="shouldShow(section, 'show_comment_box', true)"
                    data-product-comment-box-placeholder
                    class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-4"
                >
                    <p class="text-sm font-bold text-gray-800">
                        {{ sectionSetting(section, 'comment_heading', 'Customer comments') }}
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ sectionSetting(section, 'comment_placeholder_text', 'Comment submissions will be enabled later.') }}
                    </p>

                    <textarea
                        disabled
                        placeholder="Product comments coming soon"
                        class="mt-3 min-h-24 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-500"
                    ></textarea>
                </div>
            </div>

            <div
                v-else-if="section.type === 'featured_products' && relatedProducts && relatedProducts.length > 0"
                data-product-featured-products-section
                class="rounded-3xl bg-white p-8 shadow-sm"
            >
                <h2 class="text-2xl font-black tracking-tight text-gray-950">
                    {{ sectionSetting(section, 'heading', 'Related products') }}
                </h2>

                <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="relatedProduct in relatedProducts"
                        :key="relatedProduct.id"
                        class="overflow-hidden rounded-2xl border bg-white"
                    >
                        <Link :href="route('products.show', relatedProduct.id)">
                            <img
                                :src="relatedProduct.images && relatedProduct.images.length > 0
                                    ? getTenantAssetUrl(relatedProduct.images[0].image_path)
                                    : 'https://placehold.co/400x300?text=No+Image'"
                                :alt="relatedProduct.name"
                                class="h-48 w-full object-cover"
                            />
                        </Link>

                        <div class="p-4">
                            <Link :href="route('products.show', relatedProduct.id)" class="font-bold hover:text-blue-600">
                                {{ relatedProduct.name }}
                            </Link>

                            <p class="mt-2 font-black">${{ formatCurrency(relatedProduct.price) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div v-else>
        <!-- Navbar -->
        <div class="bg-white shadow-sm sticky top-0 z-10">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <!-- Shop Title -->
                    <Link :href="route('home')" class="text-2xl font-bold text-gray-800">
                        {{ usePage().props.tenant?.name || 'Online Shop' }}
                    </Link>

                    <!-- Cart Icon with Badge -->
                    <Link :href="route('cart.index')" class="relative">
                        <ShoppingCart class="h-6 w-6 text-gray-600" />
                        <span
                            v-if="localCartCount > 0"
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"
                        >
                            {{ localCartCount }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-8">
            <!-- Breadcrumb Navigation -->
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <Link :href="route('home')" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                                Home
                            </Link>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <Link
                                    :href="route('home', { category: product.category_id })"
                                    class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600"
                                >
                                    {{ product.category ? product.category.name : 'Products' }}
                                </Link>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ product.name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Product Images -->
                <div class="lg:w-2/5">
                    <!-- Main Image -->
                    <div class="mb-4 bg-white rounded-lg overflow-hidden shadow">
                        <img
                            :src="selectedImage"
                            :alt="product.name"
                            class="w-full h-96 object-contain"
                        />
                    </div>

                    <!-- Image Thumbnails -->
                    <div v-if="product.images && product.images.length > 1" class="grid grid-cols-5 gap-2">
                        <div
                            v-for="(image, index) in product.images"
                            :key="index"
                            @click="selectedImageIndex = index"
                            class="cursor-pointer border rounded-md overflow-hidden"
                            :class="{ 'ring-2 ring-blue-500': selectedImageIndex === index }"
                        >
                            <img
                                :src="getTenantAssetUrl(image.image_path)"
                                :alt="`${product.name} - Image ${index + 1}`"
                                class="w-full h-20 object-contain"
                            />
                        </div>
                    </div>
                </div>

                <!-- Product Information -->
                <div class="lg:w-3/5">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>

                        <div class="flex items-center mt-2">
                            <div class="flex items-center">
                                <Star v-for="i in 5" :key="i" :class="i <= 4 ? 'text-yellow-400' : 'text-gray-300'" class="h-5 w-5" />
                            </div>
                            <span class="text-sm text-gray-600 ml-2">4.0 (24 reviews)</span>
                        </div>

                        <div class="mt-4">
                            <p class="text-sm text-gray-500">SKU: {{ product.sku }}</p>
                        </div>

                        <div class="mt-6">
                            <p class="text-3xl font-bold text-gray-900">${{ formatCurrency(product.price) }}</p>
                        </div>

                        <div class="mt-4">
                            <p class="text-gray-700">{{ product.description }}</p>
                        </div>

                        <div class="mt-6 flex items-center">
                            <div :class="isInStock ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                {{ isInStock ? `In Stock (${product.stock})` : 'Out of Stock' }}
                            </div>

                            <div v-if="product.category" class="ml-4 bg-blue-100 text-blue-800 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                {{ product.category.name }}
                            </div>
                        </div>

                        <div class="mt-8 border-t pt-6">
                            <div class="flex items-center mb-6">
                                <Truck class="h-5 w-5 text-gray-500 mr-2" />
                                <p class="text-sm text-gray-600">Free shipping on orders over $50</p>
                            </div>

                            <div v-if="isInStock" class="flex flex-col sm:flex-row gap-4">
                                <div class="flex items-center border border-gray-300 rounded-md">
                                    <button
                                        @click="decrementQuantity"
                                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="quantity <= 1"
                                    >
                                        <Minus class="h-5 w-5" />
                                    </button>
                                    <span class="px-4 py-2 text-center w-12">{{ quantity }}</span>
                                    <button
                                        @click="incrementQuantity"
                                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="quantity >= product.stock"
                                    >
                                        <Plus class="h-5 w-5" />
                                    </button>
                                </div>

                                <button
                                    @click="addToCart"
                                    class="bg-blue-600 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700 transition flex items-center justify-center gap-2 sm:flex-1"
                                    :disabled="!isInStock"
                                >
                                    <ShoppingCart class="h-5 w-5" />
                                    Add to Cart
                                </button>

                                <button class="border border-gray-300 text-gray-700 px-4 py-3 rounded-md hover:bg-gray-50">
                                    <Heart class="h-5 w-5" />
                                </button>

                                <button class="border border-gray-300 text-gray-700 px-4 py-3 rounded-md hover:bg-gray-50">
                                    <Share2 class="h-5 w-5" />
                                </button>
                            </div>

                            <div v-else class="mt-4">
                                <p class="text-red-600 font-medium">This product is currently out of stock. Please check back later.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details Tabs -->
            <div class="mt-12">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex" aria-label="Tabs">
                            <button
                                @click="activeTab = 'details'"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                                :class="activeTab === 'details' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Product Details
                            </button>
                            <button
                                @click="activeTab = 'specs'"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                                :class="activeTab === 'specs' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Specifications
                            </button>
                            <button
                                @click="activeTab = 'reviews'"
                                class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                                :class="activeTab === 'reviews' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Reviews
                            </button>
                        </nav>
                    </div>
                    <div class="p-6">
                        <!-- Product Details Tab -->
                        <div v-if="activeTab === 'details'" class="prose max-w-none">
                            <h3>Product Information</h3>
                            <p>{{ product.description }}</p>
                        </div>

                        <!-- Specifications Tab -->
                        <div v-else-if="activeTab === 'specs'" class="prose max-w-none">
                            <h3>Technical Specifications</h3>
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">SKU</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.sku }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">Category</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.category ? product.category.name : 'Uncategorized' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">Weight</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">0.5 kg</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">Dimensions</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">25 × 15 × 5 cm</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-gray-50">Material</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Premium Quality</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Reviews Tab -->
                        <div v-else-if="activeTab === 'reviews'" class="prose max-w-none">
                            <h3>Customer Reviews</h3>
                            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                                <p class="text-center text-gray-500">Be the first to review this product!</p>
                                <button class="mt-2 mx-auto block bg-blue-600 text-white px-4 py-2 rounded font-medium hover:bg-blue-700 transition">
                                    Write a Review
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts && relatedProducts.length > 0" class="mt-12">
                <h2 class="text-2xl font-bold mb-6">Related Products</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="relatedProduct in relatedProducts" :key="relatedProduct.id" class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                        <Link :href="route('products.show', relatedProduct.id)">
                            <div class="h-48 overflow-hidden">
                                <img
                                    :src="relatedProduct.images && relatedProduct.images.length > 0
                                        ? getTenantAssetUrl(relatedProduct.images[0].image_path)
                                        : 'https://placehold.co/400x300?text=No+Image'"
                                    class="w-full h-full object-cover transition-transform hover:scale-105"
                                    :alt="relatedProduct.name"
                                />
                            </div>
                        </Link>
                        <div class="p-4">
                            <Link :href="route('products.show', relatedProduct.id)">
                                <h3 class="font-medium text-lg hover:text-blue-600">{{ relatedProduct.name }}</h3>
                            </Link>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-bold">${{ formatCurrency(relatedProduct.price) }}</span>
                                <button
                                    @click="addRelatedToCart(relatedProduct.id)"
                                    class="bg-blue-600 text-white rounded-md p-1.5 hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="relatedProduct.stock <= 0"
                                >
                                    <Plus class="h-5 w-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div
        data-s69-product-page-theme
        class="sr-only"
    >
        Individually editable product page
        Product Details
        Product Description
        Product Reviews
        Add to Cart
        Buy now
        Product page sections
        data-product-theme-section
    </div>


<!-- S83-S88 live product page controls
data-s83-s88-live-product-page-controls data-s89-s94-product-display-foundation productGalleryStyle productSectionProducts show_variant_options variant_placeholder_text gallery_style thumbnail_position related_source
productDetailsLayoutClass
productButtonWrapClass
productDescriptionSectionClass
rating_placeholder_text
comment_placeholder_text
-->
</template>
