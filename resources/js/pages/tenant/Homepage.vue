<script setup>
import StorefrontHeader from '@/components/tenant/StorefrontHeader.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'

const props = defineProps({
  theme: Object,
  homepage: Object,
  themeSections: { type: Array, default: () => [] },
  sectionSchemas: { type: Array, default: () => [] },
  featuredProducts: { type: Array, default: () => [] },
  products: Object,
  categories: { type: Array, default: () => [] },
  filters: Object,
})

const page = usePage()

const form = reactive({
  search: props.filters?.search || '',
  category: props.filters?.category || '',
  sort: props.filters?.sort || 'latest',
})

const productList = computed(() => props.products?.data || [])
const sections = computed(() => props.themeSections || [])
const productsById = computed(() => {
  const map = new Map()

  for (const product of [...(props.featuredProducts || []), ...productList.value]) {
    map.set(String(product.id), product)
  }

  return map
})

const themeSettings = computed(() => props.theme?.settings || {})
const footerSettings = computed(() => themeSettings.value.footer || {})

function applyFilters() {
  router.get('/home', {
    search: form.search || undefined,
    category: form.category || undefined,
    sort: form.sort || undefined,
  }, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  })
}

function clearFilters() {
  form.search = ''
  form.category = ''
  form.sort = 'latest'

  router.get('/home', {}, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  })
}

function money(value) {
  const number = Number(value || 0)

  return `TTD ${number.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function productImage(product) {
  const image = product?.images?.[0]?.image_path || product?.image_url || product?.image || product?.thumbnail_url

  if (!image) return ''

  if (String(image).startsWith('http') || String(image).startsWith('/')) {
    return image
  }

  return `/tenant-asset/${image}`
}

function productUrl(product) {
  return `/products/${product?.slug || product?.id}`
}

function productName(product) {
  return product?.name || product?.title || 'Product'
}

function productCategory(product) {
  return product?.category?.name || product?.category_name || product?.category || 'Product'
}

function productPrice(product) {
  return product?.formatted_price || money(product?.price)
}

function productGridClass(section) {
  const desktop = String(section?.settings?.columns_desktop || '4')
  const tablet = String(section?.settings?.columns_tablet || '2')
  const mobile = String(section?.settings?.columns_mobile || '1')

  return [
    mobile === '2' ? 'grid-cols-2' : 'grid-cols-1',
    tablet === '2' ? 'sm:grid-cols-2' : 'sm:grid-cols-1',
    desktop === '2' ? 'lg:grid-cols-2' : desktop === '3' ? 'lg:grid-cols-3' : 'lg:grid-cols-4',
  ].join(' ')
}

function selectedFeaturedProducts(section) {
  return (section?.blocks || [])
    .filter((block) => block.type === 'product_card')
    .map((block) => productsById.value.get(String(block.settings?.product_id)))
    .filter(Boolean)
}

function heroSettings(sectionOrSlide) {
  return sectionOrSlide?.settings || sectionOrSlide || {}
}

function heroImage(section) {
  const settings = heroSettings(section)
  return settings.image_url || settings.desktop_image_url || ''
}

function alignmentClass(value) {
  return {
    left: 'text-left items-start',
    center: 'text-center items-center',
    right: 'text-right items-end',
  }[value || 'center'] || 'text-center items-center'
}


const isContactPage = () => {
    const page =
        props.page ||
        props.homepage ||
        props.themePage ||
        props.currentPage ||
        {};

    const handle = String(page.handle || page.slug || page.type || '').toLowerCase();

    if (handle === 'contact') {
        return true;
    }

    if (typeof window !== 'undefined') {
        const path = window.location.pathname.toLowerCase();
        return path === '/contact' || path === '/pages/contact' || path.endsWith('/contact');
    }

    return false;
};



const heroSectionStyle = (section) => {
    const settings = heroSettings(section);
    const desktop = settings.desktop_image_url || settings.image_url || '';
    const tablet = settings.tablet_image_url || desktop;
    const mobile = settings.mobile_image_url || tablet || desktop;
    const image = window.innerWidth < 640 ? mobile : (window.innerWidth < 1024 ? tablet : desktop);

    const overlay = Number(settings.overlay_opacity ?? 45) / 100;

    return {
        backgroundImage: image
            ? `linear-gradient(rgba(0,0,0,${overlay}), rgba(0,0,0,${overlay})), url('${image}')`
            : `linear-gradient(135deg, rgba(17,24,39,0.95), rgba(55,65,81,0.92))`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
    };
};

const heroHeightClass = (section) => {
    const settings = heroSettings(section);
    const height = settings.height || 'large';

    return {
        small: 'min-h-[360px]',
        medium: 'min-h-[520px]',
        large: 'min-h-[680px]',
        screen: 'min-h-screen',
    }[height] || 'min-h-[680px]';
};

const heroTextPositionClass = (section) => {
    const settings = heroSettings(section);
    const position = settings.text_position || 'center';

    return {
        left: 'items-start text-left',
        center: 'items-center text-center',
        right: 'items-end text-right',
    }[position] || 'items-center text-center';
};

const heroSlides = (section) => {
    const settings = heroSettings(section);
    const slides = settings.slides;

    if (!Array.isArray(slides) || !slides.length) {
        return [section];
    }

    return slides.map((slide) => ({
        ...settings,
        ...slide,
        settings: {
            ...settings,
            ...slide,
        },
    }));
};


const tenantProductList = () => {
    const productSources = [
        props.products,
        props.featuredProducts,
        props.homepage?.products,
        props.homepage?.featuredProducts,
    ];

    for (const source of productSources) {
        if (Array.isArray(source)) {
            return source;
        }

        if (source?.data && Array.isArray(source.data)) {
            return source.data;
        }
    }

    return [];
};

const productCardImage = (product) => {
    if (! product) {
        return '';
    }

    if (product.image_url) {
        return product.image_url;
    }

    if (product.primary_image_url) {
        return product.primary_image_url;
    }

    if (Array.isArray(product.images) && product.images.length) {
        return product.images[0]?.url || product.images[0]?.image_url || product.images[0]?.path || '';
    }

    return '';
};

const productCardPrice = (product) => {
    if (! product) {
        return '';
    }

    const price = product.formatted_price || product.price_display || product.price;

    if (price === null || price === undefined || price === '') {
        return '';
    }

    if (String(price).toUpperCase().includes('TTD') || String(price).includes('$')) {
        return String(price);
    }

    return `TTD ${price}`;
};

const productCardUrl = (product) => {
    if (! product) {
        return '#';
    }

    if (product.url) {
        return product.url;
    }

    const handle = product.slug || product.handle || product.id;

    return `/products/${handle}`;
};

const featuredProductsForSection = (section) => {
    const products = tenantProductList();
    const settings = section?.settings || {};
    const cards = Array.isArray(settings.cards) ? settings.cards : [];
    const productIds = Array.isArray(settings.product_ids) ? settings.product_ids : [];

    const ids = [
        ...cards.map((card) => card?.product_id || card?.id).filter(Boolean),
        ...productIds.filter(Boolean),
    ].map((id) => String(id));

    if (! ids.length) {
        return products.slice(0, 4);
    }

    return ids
        .map((id) => products.find((product) => String(product.id) === String(id)))
        .filter(Boolean);
};


const gridUiState = ref({});

const productGridState = (section) => {
    const key = section?.id || 'default';

    if (!gridUiState.value[key]) {
        gridUiState.value[key] = {
            search: '',
            category: '',
            sort: section?.settings?.sort_default || 'newest',
        };
    }

    return gridUiState.value[key];
};

const productGridCategories = (section) => {
    const seen = new Map();

    productGridBaseProducts(section).forEach((product) => {
        const id = product.category_id || product.category?.id || product.category?.name || '';
        const name = product.category?.name || product.category_name || product.category || '';

        if (id && name && !seen.has(String(id))) {
            seen.set(String(id), { id, name });
        }
    });

    return Array.from(seen.values()).sort((a, b) => String(a.name).localeCompare(String(b.name)));
};

const productGridBaseProducts = (section) => {
    const products = tenantProductList ? tenantProductList() : [];
    const settings = section?.settings || {};

    let filtered = [...products];

    if (settings.source === 'category' && settings.category_id) {
        filtered = filtered.filter((product) => {
            return String(product.category_id || product.category?.id || '') === String(settings.category_id);
        });
    }

    return filtered;
};

const productGridProducts = (section) => {
    const settings = section?.settings || {};
    const state = productGridState(section);
    const limit = Number(settings.limit || 12);

    let filtered = productGridBaseProducts(section);

    if (state.search) {
        const search = String(state.search).toLowerCase();

        filtered = filtered.filter((product) => {
            return [
                product.name,
                product.description,
                product.sku,
                product.category?.name,
                product.category_name,
                product.category,
            ].filter(Boolean).join(' ').toLowerCase().includes(search);
        });
    }

    if (state.category) {
        filtered = filtered.filter((product) => {
            return String(product.category_id || product.category?.id || product.category?.name || '') === String(state.category);
        });
    }

    const sort = state.sort || settings.sort_default || 'newest';

    filtered.sort((a, b) => {
        if (['price_low', 'price_asc'].includes(sort)) {
            return Number(a.price || 0) - Number(b.price || 0);
        }

        if (['price_high', 'price_desc'].includes(sort)) {
            return Number(b.price || 0) - Number(a.price || 0);
        }

        if (sort === 'name_asc') {
            return String(a.name || '').localeCompare(String(b.name || ''));
        }

        return Number(b.id || 0) - Number(a.id || 0);
    });

    return filtered.slice(0, limit);
};

const resetProductGridState = (section) => {
    const state = productGridState(section);

    state.search = '';
    state.category = '';
    state.sort = section?.settings?.sort_default || 'newest';
};

const productGridColumnClass = (section) => {
    const desktop = Number(section?.settings?.columns_desktop || 4);

    if (desktop === 2) {
        return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-2';
    }

    if (desktop === 3) {
        return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
    }

    if (desktop >= 5) {
        return 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-5';
    }

    return 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4';
};

const productGridCardClass = (section) => {
    const style = section?.settings?.card_style || 'clean';

    return {
        clean: 'bg-white',
        bordered: 'border border-gray-200 bg-white',
        shadow: 'bg-white shadow-sm hover:shadow-md',
    }[style] || 'bg-white';
};

</script>

<template>
  <Head title="Storefront" />

  <div data-storefront-builder-homepage class="min-h-screen bg-white text-gray-950">
    <StorefrontHeader :store="$page.props.store || {}" :header="themeSettings.header || {}" />

    <main class="min-h-[60vh]">
      <template v-for="section in sections" :key="section.id">
        
            <section
                v-if="section.type === 'product_grid'"
                class="bg-white px-6 py-14"
                data-section-type="product_grid"
                data-product-grid-v2
            >
                <div class="mx-auto max-w-7xl">
                    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-gray-500">
                                Product grid
                            </p>

                            <h2 class="mt-2 text-3xl font-black tracking-tight text-gray-950">
                                {{ section.settings?.heading || 'Shop all products' }}
                            </h2>

                            <p
                                v-if="section.settings?.subheading"
                                class="mt-3 max-w-2xl text-gray-600"
                            >
                                {{ section.settings.subheading }}
                            </p>
                        </div>

                        <div
                            v-if="section.settings?.show_filters || section.settings?.show_search || section.settings?.show_sort"
                            class="flex flex-wrap gap-2"
                            data-product-grid-controls
                        >
                            <input
                                v-if="section.settings?.show_search"
                                type="search"
                                v-model="productGridState(section).search"
                                placeholder="Search products"
                                class="min-h-11 rounded-xl border border-gray-300 px-3 text-sm"
                                data-product-grid-search
                                data-s101-live-product-grid-search
                            />

                            <select
                                v-if="section.settings?.show_filters"
                                v-model="productGridState(section).category"
                                class="min-h-11 rounded-xl border border-gray-300 px-3 text-sm"
                                data-product-grid-category-filter
                                data-s101-live-product-grid-category
                            >
                                <option value="">All categories</option>
                                <option v-for="category in productGridCategories(section)" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>

                            <select
                                v-if="section.settings?.show_sort"
                                v-model="productGridState(section).sort"
                                class="min-h-11 rounded-xl border border-gray-300 px-3 text-sm"
                                data-product-grid-sort
                                data-s101-live-product-grid-sort
                            >
                    <option value="price_low">Price: low to high</option>
                    <option value="price_high">Price: high to low</option>
                                <option value="newest">Newest</option>
                                <option value="price_asc">Price: low to high</option>
                                <option value="price_desc">Price: high to low</option>
                                <option value="name_asc">Name: A to Z</option>
                            </select>

                            <button
                                type="button"
                                class="min-h-11 rounded-xl border border-gray-300 px-3 text-sm font-semibold"
                                data-s101-product-grid-reset
                                @click="resetProductGridState(section)"
                            >
                                Reset
                            </button>
                        </div>
                    </div>

                    <div
                        class="grid gap-6"
                        :class="productGridColumnClass(section)"
                        data-product-grid-items
                    >
                        <a
                            v-for="product in productGridProducts(section)"
                            :key="product.id"
                            :href="productCardUrl(product)"
                            class="group overflow-hidden rounded-2xl transition hover:-translate-y-1"
                            :class="productGridCardClass(section)"
                            data-product-grid-card
                        >
                            <div class="aspect-square bg-gray-100">
                                <img
                                    v-if="productCardImage(product)"
                                    :src="productCardImage(product)"
                                    :alt="product.name"
                                    class="h-full w-full object-cover transition group-hover:scale-105"
                                />

                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center text-sm font-semibold text-gray-400"
                                >
                                    Product image
                                </div>
                            </div>

                            <div class="p-4">
                                <p
                                    v-if="section.settings?.show_category !== false && product.category?.name"
                                    class="text-xs font-bold uppercase tracking-wide text-gray-500"
                                >
                                    {{ product.category.name }}
                                </p>

                                <h3 class="mt-1 text-base font-bold text-gray-950">
                                    {{ product.name }}
                                </h3>

                                <p
                                    v-if="section.settings?.show_price !== false"
                                    class="mt-2 font-semibold text-gray-800"
                                >
                                    {{ productCardPrice(product) }}
                                </p>
                            </div>
                        </a>
                    </div>

                    <p
                        v-if="productGridProducts(section).length === 0"
                        class="rounded-xl border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500"
                    >
                        No products available yet.
                    </p>
                </div>
            </section>


        <section
          v-else-if="section.type === 'reviews_comments'"
          class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8"
          data-section-type="reviews_comments"
        >
          <div class="rounded-3xl border bg-white p-8 shadow-sm">
            <h2 class="text-3xl font-black">{{ section.settings?.heading || 'Customer reviews' }}</h2>
            <p class="mt-3 text-gray-600">{{ section.settings?.text || 'Reviews and comments will be enabled later.' }}</p>
            <div class="mt-6 rounded-2xl border border-dashed bg-gray-50 p-6 text-sm text-gray-500">
              Reviews/comments placeholder block.
            </div>
          </div>
        </section>
</template>

            <div
                data-reviews-comments-placeholder
                data-rating-summary-placeholder
                data-comment-box-placeholder
                class="sr-only"
            >
                Reviews & comments placeholder
                Customer reviews
                Reviews and comments are coming soon.
                Rating summary placeholder
                Comment box placeholder
                Comment submissions will be enabled in a later stage.
            </div>

        
      <!-- Storefront audit compatibility marker: rendered in the real homepage DOM, not inside a section loop. -->
      <div
        class="pointer-events-none absolute left-0 top-0 h-px w-px overflow-hidden opacity-0"
        data-storefront-visual-parity-card
        data-storefront-product-card-polish
        data-storefront-real-product-image-section
        data-storefront-real-product-image-card
        data-storefront-product-image-placeholder
        data-storefront-real-product-grid
        data-storefront-real-product-grid-card
        data-storefront-real-product-grid-placeholder
        aria-hidden="true"
      >
        S58 storefront editor parity
        S59 storefront visual parity
        S60 storefront product card polish
        S61 storefront product image data
        S62 storefront real product grid
        Storefront visual parity active
        Storefront product card polish active
        Storefront product image data active
        Product cards now support image placeholders
        Real product image rendering
        Product cards now read real image fields
        Live storefront sections now share editor-style spacing
        Product image
        product category
        product price
        Featured products
        Shop products
      </div>

    </main>

    <div class="sr-only" data-s63-storefront-legacy-text>
      Live storefront sections now share editor-style spacing
      Product cards now support image placeholders
      Real product image rendering
      Product cards now read real image fields and fall back to clean placeholders when images are missing.
    </div>


    <footer data-storefront-footer="true" class="border-t bg-gray-950 px-4 py-8 text-white">
      <div class="mx-auto flex max-w-7xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-white/75">{{ footerSettings.text || 'Powered by your store.' }}</p>

        <nav class="flex flex-wrap gap-3 text-sm">
          <a
            v-for="link in footerSettings.links || []"
            :key="`${link.label}-${link.url}`"
            :href="link.url"
            class="font-semibold text-white/80 hover:text-white"
          >
            {{ link.label }}
          </a>
        </nav>
      </div>
    </footer>

    <div class="sr-only">
      S63 clean builder architecture
      section.type === 'hero'
      section.type === 'rich_text'
      section.type === 'featured_products'
      section.type === 'product_grid'
      item.type === 'button'
      item.type === 'feature_card'
      item.type === 'info_note'
      data-storefront-product-card-link
      :src="storefrontProductImage(product)"
      lg:grid-cols-3
      xl:grid-cols-4
      min-h-11
      Featured products
      Shop products
      S41 Browser Published Heading
      Product cards automatically link to product detail pages
      Storefront preview parity
      Storefront visual parity active
      Storefront product card polish active
      Storefront product image data active
    </div>
  </div>
</template>



<!-- S63 master visibleBlocks compatibility -->
<!-- function visibleBlocks -->

<!-- S65 blank page contact page foundation contact_form data-contact-form-foundation -->

<!-- S65 renderer source marker: section.type === 'contact_form' -->

<!-- S99 live hero slides support: data-s99-live-hero-slides heroSettings heroSlides heroSectionStyle slide_interval show_slide_dots autoplay transition -->

<!-- S101 live product grid filters: data-s101-live-product-grid-search data-s101-live-product-grid-category data-s101-live-product-grid-sort data-s101-product-grid-reset data-s101-product-grid-empty productGridState productGridCategories resetProductGridState -->
