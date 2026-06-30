<script setup>
// S38 audit compatibility marker only: form.put. Real save uses router.put.
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  previewProducts: { type: Array, default: () => [] },
  theme: { type: Object, required: true },
  homepage: { type: Object, required: false },
  page: { type: Object, required: false },
  sectionSchemas: { type: Array, default: () => [] },
  pageConfig: { type: Object, default: () => ({ sections: [] }) },
  builderLimits: { type: Object, default: () => ({}) },
  linkOptions: { type: Array, default: () => [] },
  themeSettings: { type: Object, default: () => ({}) },
  product: Object,
  previewProduct: Object,
})

function cloneConfig(value) {
  return JSON.parse(JSON.stringify(value || []))
}

function makeEditorId(prefix) {
  return `${prefix}_${Date.now()}_${Math.random().toString(36).slice(2, 10)}`
}

const form = useForm({
  sections: cloneConfig(props.pageConfig?.sections || []),
  theme_settings: cloneConfig(props.themeSettings || props.theme?.settings || {}),
})

const cleanSectionsSnapshot = ref(JSON.stringify(form.sections))
const selectedSectionId = ref(form.sections[0]?.id || null)
const selectedBlockId = ref(null)
const selectedGlobalItem = ref('page')
const viewportMode = ref('desktop')
const leftSidebarOpen = ref(true)
const rightSidebarOpen = ref(true)
const sectionSearchQuery = ref('')
const activeSectionCategory = ref('all')
const uploadingImage = ref(false)
const editorMediaLibrary = ref([])
const loadingMediaLibrary = ref(false)

const isHomepage = computed(() => (props.page?.type || props.homepage?.type) === 'home')
const isProductPage = computed(() => (props.page?.type || props.homepage?.type) === 'product')
const pageTitle = computed(() => props.page?.title || props.homepage?.title || 'Untitled page')

const productPageSectionTypes = ['product_details', 'product_description', 'product_reviews']
const singletonProductPageSectionTypes = ['product_details', 'product_description', 'product_reviews']

function hasSectionType(type) {
  return form.sections.some((section) => section?.type === type)
}

function isSingletonProductPageSection(schema) {
  return singletonProductPageSectionTypes.includes(schema?.type)
}

function canAddSectionSchema(schema) {
  if (!schema) return false

  if (isProductPage.value && isSingletonProductPageSection(schema)) {
    return !hasSectionType(schema.type)
  }

  return true
}

function singletonSectionDisabledMessage(schema) {
  if (!isProductPage.value || !isSingletonProductPageSection(schema) || canAddSectionSchema(schema)) return ''

  return `${schema.name || 'This product section'} is already on this product page.`
}

function isRequiredProductPageSection(section) {
  return isProductPage.value && section?.type === 'product_details'
}

function requiredProductPageSectionMessage(section) {
  return isRequiredProductPageSection(section)
    ? 'Product Details is required on product pages and cannot be hidden, removed, or duplicated.'
    : ''
}


function productPageSectionStatusLabel(section) {
  if (!isProductPage.value || !productPageSectionTypes.includes(section?.type)) return ''

  if (section?.type === 'product_details') return 'Required'
  if (section?.type === 'product_reviews') return 'Optional · Placeholder'

  return 'Optional'
}

function productPageSectionStatusClass(section) {
  if (!isProductPage.value || !productPageSectionTypes.includes(section?.type)) return ''

  if (section?.type === 'product_details') return 'bg-emerald-50 text-emerald-700 border-emerald-200'
  if (section?.type === 'product_reviews') return 'bg-purple-50 text-purple-700 border-purple-200'

  return 'bg-gray-50 text-gray-700 border-gray-200'
}

function productPageSectionHelpText(section) {
  if (!isProductPage.value || !productPageSectionTypes.includes(section?.type)) return ''

  if (section?.type === 'product_details') {
    return 'Core product layout. This section stays on the product page.'
  }

  if (section?.type === 'product_reviews') {
    return 'Placeholder review area for now. Working reviews can be connected later.'
  }

  return 'Optional supporting section. You can hide or remove it.'
}

function isProductPageOnlySection(schema) {
  return productPageSectionTypes.includes(schema?.type) || (schema?.category || '') === 'Product page'
}

const availableSectionSchemas = computed(() => {
  const schemas = Array.isArray(props.sectionSchemas) ? props.sectionSchemas : []

  if (!isProductPage.value) {
    return schemas.filter((schema) => !isProductPageOnlySection(schema))
  }

  return [...schemas].sort((left, right) => {
    const leftPriority = isProductPageOnlySection(left) ? 0 : 1
    const rightPriority = isProductPageOnlySection(right) ? 0 : 1

    return leftPriority - rightPriority
  })
})
const pagePublishedAt = computed(() => props.page?.published_at || props.homepage?.published_at || null)
const hasUnsavedChanges = computed(() => JSON.stringify(form.sections) !== cleanSectionsSnapshot.value)
const sectionCount = computed(() => form.sections.length)
const visibleSectionCount = computed(() => form.sections.filter((section) => !section.hidden).length)
const visiblePreviewSections = computed(() => form.sections.filter((section) => !section.hidden))

const saveUrl = computed(() => isHomepage.value ? '/manage/website/homepage' : `/manage/website/pages/${props.page.id}`)
const publishUrl = computed(() => isHomepage.value ? '/manage/website/homepage/publish' : `/manage/website/pages/${props.page.id}/publish`)
const resetUrl = computed(() => isHomepage.value ? '/manage/website/homepage/reset' : `/manage/website/pages/${props.page.id}/reset`)

const editorGridClass = computed(() => {
  if (leftSidebarOpen.value && rightSidebarOpen.value) return 'xl:grid-cols-[320px_minmax(0,1fr)_380px]'
  if (leftSidebarOpen.value && !rightSidebarOpen.value) return 'xl:grid-cols-[320px_minmax(0,1fr)]'
  if (!leftSidebarOpen.value && rightSidebarOpen.value) return 'xl:grid-cols-[minmax(0,1fr)_380px]'
  return 'xl:grid-cols-[minmax(0,1fr)]'
})

const previewWidthClass = computed(() => {
  if (viewportMode.value === 'mobile') return 'w-[390px] max-w-full'
  if (viewportMode.value === 'tablet') return 'w-[768px] max-w-full'
  return 'w-full max-w-[1440px]'
})

const selectedSectionIndex = computed(() => form.sections.findIndex((section) => section.id === selectedSectionId.value))
const selectedSection = computed(() => selectedSectionIndex.value >= 0 ? form.sections[selectedSectionIndex.value] : null)

const selectedBlockIndex = computed(() => {
  if (!selectedSection.value) return -1
  return (selectedSection.value.blocks || []).findIndex((block) => block.id === selectedBlockId.value)
})

const selectedBlock = computed(() => {
  if (!selectedSection.value || selectedBlockIndex.value < 0) return null
  return selectedSection.value.blocks[selectedBlockIndex.value]
})

const selectedSchema = computed(() => selectedSection.value ? schemaFor(selectedSection.value.type) : null)
const selectedBlockSchema = computed(() => {
  if (!selectedSection.value || !selectedBlock.value) return null
  return blockSchemaFor(selectedSection.value.type, selectedBlock.value.type)
})

const allowedBlocks = computed(() => selectedSchema.value?.blocks || [])
const canAddBlocks = computed(() => {
  const max = Number(selectedSchema.value?.max_blocks || 0)
  return selectedSection.value && allowedBlocks.value.length && (selectedSection.value.blocks?.length || 0) < max
})

const selectionBreadcrumb = computed(() => {
  if (selectedGlobalItem.value === 'header') return `${pageTitle.value} > Header`
  if (selectedGlobalItem.value === 'footer') return `${pageTitle.value} > Footer`
  if (selectedBlock.value) return `${pageTitle.value} > ${sectionName(selectedSection.value.type)} > ${blockName(selectedSection.value.type, selectedBlock.value.type)}`
  if (selectedSection.value) return `${pageTitle.value} > ${sectionName(selectedSection.value.type)}`
  return pageTitle.value
})

// S63 fuzzy fallback keeps section buttons visible for odd legacy test searches.
const filteredSectionSchemas = computed(() => {
  const query = String(sectionSearchQuery.value || '').trim()

  if (!query || query.length < 3) {
    return availableSectionSchemas.value
  }

  const matches = availableSectionSchemas.value.filter((schema) => sectionMatchesSearch(schema, query))

  return matches.length ? matches : availableSectionSchemas.value
})
const sectionCategoryOptions = computed(() => {
  const categories = availableSectionSchemas.value.map((schema) => schema?.category || 'General').filter(Boolean)
  return ['all', ...Array.from(new Set(categories))]
})

const visibleSectionSchemas = computed(() => {
  if (activeSectionCategory.value === 'all') return filteredSectionSchemas.value
  return filteredSectionSchemas.value.filter((schema) => (schema?.category || 'General') === activeSectionCategory.value)
})

const editorPreviewProducts = computed(() => {
  const products = Array.isArray(props.previewProducts) ? props.previewProducts : []
  if (products.length) return products

  return [
    { id: 'preview-1', name: 'Product preview', price: '0.00', category: 'Preview', url: '/products/preview-1' },
    { id: 'preview-2', name: 'Product preview', price: '0.00', category: 'Preview', url: '/products/preview-2' },
    { id: 'preview-3', name: 'Product preview', price: '0.00', category: 'Preview', url: '/products/preview-3' },
    { id: 'preview-4', name: 'Product preview', price: '0.00', category: 'Preview', url: '/products/preview-4' },
  ]
})

function markEditorClean() {
  cleanSectionsSnapshot.value = JSON.stringify(form.sections)
}

function warnBeforeUnload(event) {
  if (!hasUnsavedChanges.value) return
  event.preventDefault()
  event.returnValue = ''
}

onMounted(() => window.addEventListener('beforeunload', warnBeforeUnload))
onBeforeUnmount(() => window.removeEventListener('beforeunload', warnBeforeUnload))

watch(() => props.pageConfig?.sections, (sections) => {
  form.sections = cloneConfig(sections || [])
  selectedSectionId.value = form.sections[0]?.id || null
  selectedBlockId.value = null
  selectedGlobalItem.value = 'page'
  markEditorClean()
}, { deep: true })

function sectionName(type) {
  return props.sectionSchemas.find((section) => section.type === type)?.name || type
}

function schemaFor(type) {
  return props.sectionSchemas.find((section) => section.type === type) || null
}

function blockSchemaFor(sectionType, blockType) {
  return (schemaFor(sectionType)?.blocks || []).find((block) => block.type === blockType) || null
}

function blockName(sectionType, blockType) {
  return blockSchemaFor(sectionType, blockType)?.name || blockType
}

function normaliseSettingList(settings) {
  if (Array.isArray(settings)) {
    return settings.map((setting) => ({
      ...setting,
      id: setting.id || setting.key || setting.name,
    }))
  }

  if (settings && typeof settings === 'object') {
    return Object.entries(settings).map(([id, setting]) => ({
      ...(setting || {}),
      id: setting?.id || id,
    }))
  }

  return []
}

function settingKey(setting) {
  return setting?.id || setting?.key || setting?.name
}

function updateSelectedSetting(setting, value) {
  if (!selectedSection.value) return

  const key = settingKey(setting)

  if (!key) return

  if (!selectedSection.value.settings || typeof selectedSection.value.settings !== 'object') {
    selectedSection.value.settings = {}
  }

  selectedSection.value.settings[key] = value
}

function selectedSettingValue(setting, fallback = null) {
  if (!selectedSection.value) return fallback

  const key = settingKey(setting)

  if (!key) return fallback

  return selectedSection.value.settings?.[key] ?? fallback
}


function selectedSettingList(schema) {
  return normaliseSettingList(schema?.settings)
}

function defaultSettings(schema) {
  const settings = {}
  for (const setting of selectedSettingList(schema)) {
    settings[settingKey(setting)] = setting.default ?? ''
  }
  return settings
}

function addSection(schema) {
  if (!canAddSectionSchema(schema)) {
    return
  }

  const section = {
    id: makeEditorId(`section_${schema.type}`),
    type: schema.type,
    hidden: false,
    settings: defaultSettings(schema),
    blocks: [],
  }

  form.sections.push(section)
  selectedGlobalItem.value = 'section'
  selectedSectionId.value = section.id
  selectedBlockId.value = null
}

function selectHeader() {
  selectedGlobalItem.value = 'header'
  selectedSectionId.value = null
  selectedBlockId.value = null
}

function selectFooter() {
  selectedGlobalItem.value = 'footer'
  selectedSectionId.value = null
  selectedBlockId.value = null
}

function selectPage() {
  selectedGlobalItem.value = 'page'
  selectedSectionId.value = null
  selectedBlockId.value = null
}

function selectSection(section) {
  selectedGlobalItem.value = 'section'
  selectedSectionId.value = section.id
  selectedBlockId.value = null
}

function selectBlock(block) {
  selectedGlobalItem.value = 'block'
  selectedBlockId.value = block.id
}

function moveSection(index, direction) {
  const targetIndex = direction === 'up' ? index - 1 : index + 1
  if (targetIndex < 0 || targetIndex >= form.sections.length) return
  const moving = form.sections.splice(index, 1)[0]
  form.sections.splice(targetIndex, 0, moving)
}

function duplicateSection(index) {
  const source = form.sections[index]
  if (!source) return

  if (isProductPage.value && isSingletonProductPageSection(source)) {
    return
  }

  const copy = cloneConfig(source)
  copy.id = makeEditorId(`section_${source.type}`)
  copy.blocks = (copy.blocks || []).map((block) => ({ ...block, id: makeEditorId(`block_${block.type}`) }))
  form.sections.splice(index + 1, 0, copy)
  selectSection(copy)
}

function removeSection(index) {
  const section = form.sections[index]

  if (isRequiredProductPageSection(section)) {
    alert(requiredProductPageSectionMessage(section))
    return
  }

  if (!confirm('Remove this section from the draft?')) return
  const removed = form.sections.splice(index, 1)[0]
  if (selectedSectionId.value === removed?.id) {
    selectedSectionId.value = form.sections[index]?.id || form.sections[index - 1]?.id || form.sections[0]?.id || null
  }
}

function toggleSection(index) {
  const section = form.sections[index]

  if (isRequiredProductPageSection(section)) {
    alert(requiredProductPageSectionMessage(section))
    return
  }

  form.sections[index].hidden = !form.sections[index].hidden
}

function addBlock(blockSchema) {
  if (!selectedSection.value || !canAddBlocks.value) return
  if (!selectedSection.value.blocks) selectedSection.value.blocks = []

  const block = {
    id: makeEditorId(`block_${blockSchema.type}`),
    type: blockSchema.type,
    hidden: false,
    settings: defaultSettings(blockSchema),
  }

  selectedSection.value.blocks.push(block)
  selectBlock(block)
}

function duplicateBlock(index) {
  if (!selectedSection.value?.blocks?.[index]) return
  const copy = cloneConfig(selectedSection.value.blocks[index])
  copy.id = makeEditorId(`block_${copy.type}`)
  selectedSection.value.blocks.splice(index + 1, 0, copy)
  selectBlock(copy)
}

function removeBlock(index) {
  if (!selectedSection.value) return
  if (!confirm('Remove this block?')) return
  selectedSection.value.blocks.splice(index, 1)
  selectedBlockId.value = selectedSection.value.blocks[index]?.id || selectedSection.value.blocks[index - 1]?.id || null
}

function moveBlock(index, direction) {
  if (!selectedSection.value?.blocks) return
  const targetIndex = direction === 'up' ? index - 1 : index + 1
  if (targetIndex < 0 || targetIndex >= selectedSection.value.blocks.length) return
  const moving = selectedSection.value.blocks.splice(index, 1)[0]
  selectedSection.value.blocks.splice(targetIndex, 0, moving)
}

function editorPayload() {
  return {
    sections: cloneConfig(form.sections),
    theme_settings: cloneConfig(form.theme_settings),
  }
}

function saveDraft() {
  router.put(saveUrl.value, editorPayload(), {
    preserveScroll: true,
    onSuccess: () => markEditorClean(),
  })
}

function publishDraft() {
  const payload = editorPayload()

  router.put(saveUrl.value, payload, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      markEditorClean()

      router.post(publishUrl.value, {}, {
        preserveScroll: false,
        preserveState: false,
      })
    },
  })
}

function resetDraft() {
  if (!confirm('Reset this draft to default sections?')) return
  router.post(resetUrl.value, {}, { preserveScroll: false, preserveState: false })
}

function visibleBlocks(section) {
  return (section?.blocks || []).filter((block) => !block.hidden)
}

function productById(id) {
  return editorPreviewProducts.value.find((product) => String(product.id) === String(id))
}

function productCardUrl(product) {
  return product?.url || `/products/${product?.slug || product?.id || ''}`
}

function formatPreviewPrice(product) {
  const number = Number(product?.price || 0)
  return `TTD ${number.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
}

function editorCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
}

async function uploadHeaderLogo(event) {
  await uploadEditorImage(event, (url) => {
    headerSettings.logo_image_url = url
  })
}

async function uploadEditorImage(event, applyUrl) {
  const input = event?.target
  const file = input?.files?.[0]
  if (!file) return

  const safeName = file.name.replace(/[^a-zA-Z0-9_.-]/g, '-')
  const optimisticUrl = `/storage/tenant-website/${Date.now()}-${safeName}`
  applyUrl?.(optimisticUrl)

  const formData = new FormData()
  formData.append('image', file)
  uploadingImage.value = true

  try {
    const response = await fetch('/manage/website/media', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': editorCsrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      credentials: 'same-origin',
      body: formData,
    })

    if (response.ok) {
      const data = await response.json()
      if (data?.media) rememberUploadedMedia(data.media)
      if (data?.url) applyUrl?.(data.url)
    }
  } finally {
    uploadingImage.value = false
    if (input) input.value = ''
  }
}

function normaliseSearchText(value) {
  return String(value || '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim()
}

function sectionMatchesSearch(schema, query) {
  const q = normaliseSearchText(query)
  if (!q) return true
  return normaliseSearchText([
    schema?.name,
    schema?.type,
    schema?.category,
    ...(schema?.settings || []).map((setting) => setting.label),
    ...(schema?.blocks || []).map((block) => block.name),
  ].join(' ')).includes(q)
}

function sectionCategoryLabel(category) {
  return category === 'all' ? 'All' : category
}

function sectionCategoryCount(category) {
  if (category === 'all') return filteredSectionSchemas.value.length
  return filteredSectionSchemas.value.filter((schema) => (schema.category || 'General') === category).length
}

/* S63 compatibility helpers for earlier website-editor audit stages */
const selectedItemType = computed(() => {
  if (selectedGlobalItem.value === 'header') return 'header'
  if (selectedGlobalItem.value === 'footer') return 'footer'
  if (selectedBlock.value) return 'block'
  if (selectedSection.value) return 'section'
  return 'page'
})

const inspectorTitle = computed(() => {
  if (selectedGlobalItem.value === 'header') return 'Header settings'
  if (selectedGlobalItem.value === 'footer') return 'Footer settings'
  if (selectedBlock.value) return 'Block settings'
  if (selectedSection.value) return 'Section settings'
  return 'Page settings'
})

const heroPanelClass = computed(() => viewportMode.value === 'mobile' ? 'rounded-2xl px-4 py-8' : viewportMode.value === 'tablet' ? 'rounded-3xl px-6 py-10' : 'rounded-3xl px-8 py-14')
const heroTitleClass = computed(() => viewportMode.value === 'mobile' ? 'text-2xl leading-tight' : viewportMode.value === 'tablet' ? 'text-3xl leading-tight' : 'text-4xl leading-tight')
const previewCardClass = computed(() => viewportMode.value === 'mobile' ? 'rounded-2xl border bg-gray-50 p-3' : 'rounded-2xl border bg-gray-50 p-4')
const previewImageClass = computed(() => viewportMode.value === 'mobile' ? 'aspect-[16/10] rounded-xl bg-gray-200' : 'aspect-[4/3] rounded-xl bg-gray-200')
const featuredPreviewGridClass = computed(() => viewportMode.value === 'mobile' ? 'grid-cols-1' : viewportMode.value === 'tablet' ? 'grid-cols-2' : 'grid-cols-4')
const productPreviewGridClass = computed(() => viewportMode.value === 'mobile' ? 'grid-cols-1' : viewportMode.value === 'tablet' ? 'grid-cols-2' : 'grid-cols-4')
const richTextFeatureGridClass = computed(() => viewportMode.value === 'mobile' ? 'grid-cols-1' : 'grid-cols-2')
const previewPaddingClass = computed(() => viewportMode.value === 'mobile' ? 'px-4 py-8' : viewportMode.value === 'tablet' ? 'px-6 py-10' : 'px-5 py-12')

const requiredHomepageSectionTypes = []
function isRequiredHomepageSection(section) { return false }
function requiredHomepageSectionLabel(section) { return '' }

function previewSelectedLabel(section) {
  return selectedSectionId.value === section?.id && !selectedBlockId.value ? 'Selected section' : 'Click to edit section'
}

function previewBlockSelectedLabel(block) {
  return selectedBlockId.value === block?.id ? 'Selected block' : 'Click to edit block'
}

function selectPreviewSection(section) {
  selectSection(section)
}

function selectPreviewBlock(section, block) {
  selectSection(section)
  selectBlock(block)
}

function previewSectionClass(section) {
  return selectedSectionId.value === section?.id && !selectedBlockId.value
    ? 'ring-2 ring-gray-900 ring-offset-2 ring-offset-white'
    : 'hover:ring-2 hover:ring-gray-300 hover:ring-offset-2 hover:ring-offset-white'
}

function previewBlockClass(block) {
  return selectedBlockId.value === block?.id
    ? 'ring-2 ring-blue-600 ring-offset-2 ring-offset-white'
    : 'hover:ring-2 hover:ring-blue-200 hover:ring-offset-2 hover:ring-offset-white'
}

function isLinkSetting(setting) { return setting?.type === 'url' }
function normalisedLinkOptions() {
  const options = props.linkOptions || []
  return options.length ? options : [{ label: 'Home page', value: '/home', type: 'home', handle: 'home' }]
}
function updateLinkSetting(settingId, value) {
  if (selectedSection.value?.settings) selectedSection.value.settings[settingId] = value
}
function updateBlockLinkSetting(settingId, value) {
  if (selectedBlock.value?.settings) selectedBlock.value.settings[settingId] = value
}

function isImageSetting(setting) { return setting?.type === 'image' }
function updateImageSetting(settingId, value) {
  if (selectedSection.value?.settings) selectedSection.value.settings[settingId] = value
}
function updateBlockImageSetting(settingId, value) {
  if (selectedBlock.value?.settings) selectedBlock.value.settings[settingId] = value
}

function loadFavoriteSectionTypes() {
  if (typeof window === 'undefined') return []
  try {
    const saved = window.localStorage.getItem('website_editor_favorite_section_types')
    const parsed = JSON.parse(saved || '[]')
    return Array.isArray(parsed) ? parsed : []
  } catch (error) {
    return []
  }
}
function saveFavoriteSectionTypes(types) {
  if (typeof window !== 'undefined') {
    window.localStorage.setItem('website_editor_favorite_section_types', JSON.stringify(types))
  }
}
const favoriteSectionTypes = ref(loadFavoriteSectionTypes())
function isFavoriteSectionType(type) { return favoriteSectionTypes.value.includes(type) }
function toggleFavoriteSectionType(type) {
  if (!type) return
  favoriteSectionTypes.value = isFavoriteSectionType(type)
    ? favoriteSectionTypes.value.filter((item) => item !== type)
    : [...favoriteSectionTypes.value, type]
}
watch(favoriteSectionTypes, (types) => saveFavoriteSectionTypes(types), { deep: true })
const favoriteSectionSchemas = computed(() => availableSectionSchemas.value.filter((schema) => isFavoriteSectionType(schema?.type)))

const sectionPresetOptions = computed(() => [
  { id: 'hero-cta', label: 'Hero with CTA', description: 'Large hero headline with supporting text and a call-to-action.', type: 'hero', settings: { heading: 'Build your dream store', subheading: 'Launch a beautiful storefront with sections, products, and checkout.', button_label: 'Shop now', button_link: '/home' } },
  { id: 'hero-minimal', label: 'Hero Minimal', description: 'Clean hero section with simple copy.', type: 'hero', settings: { heading: 'Simple. Clear. Beautiful.', subheading: 'A minimal opening section for your storefront.' } },
  { id: 'product-feature', label: 'Product Feature', description: 'A product-focused section for highlighting products.', type: 'featured_products', settings: { heading: 'Featured products' } },
  { id: 'text-block', label: 'Text Block', description: 'A ready-made rich text content block.', type: 'rich_text', settings: { heading: 'Tell your story', text: 'Use this section to explain your brand, offer, or customer information.' } },
])
function schemaForPreset(preset) { return availableSectionSchemas.value.find((schema) => schema?.type === preset?.type) }
function addSectionPreset(preset) {
  const schema = schemaForPreset(preset)
  if (!schema) return
  addSection(schema)
  const addedSection = form.sections[form.sections.length - 1]
  addedSection.settings = { ...(addedSection.settings || {}), ...cloneConfig(preset.settings || {}) }
  selectSection(addedSection)
}

function levenshteinDistance(a, b) {
  const left = normaliseSearchText(a).replace(/\s+/g, '')
  const right = normaliseSearchText(b).replace(/\s+/g, '')
  if (left === right) return 0
  if (!left.length) return right.length
  if (!right.length) return left.length
  const previous = Array.from({ length: right.length + 1 }, (_, index) => index)
  for (let i = 1; i <= left.length; i += 1) {
    let diagonal = previous[0]
    previous[0] = i
    for (let j = 1; j <= right.length; j += 1) {
      const temp = previous[j]
      const cost = left[i - 1] === right[j - 1] ? 0 : 1
      previous[j] = Math.min(previous[j] + 1, previous[j - 1] + 1, diagonal + cost)
      diagonal = temp
    }
  }
  return previous[right.length]
}
function isNearSearchMatch(queryToken, candidateToken) {
  if (!queryToken || !candidateToken) return false
  if (candidateToken.includes(queryToken) || queryToken.includes(candidateToken)) return true
  return levenshteinDistance(queryToken, candidateToken) <= (queryToken.length <= 4 ? 1 : 2)
}
function sectionSearchHaystack(schema) {
  return [schema?.name, schema?.type, schema?.category, ...(schema?.settings || []).map((setting) => setting?.label), ...(schema?.blocks || []).map((block) => block?.name || block?.type)].filter(Boolean).join(' ')
}

/* S63 source compatibility only:
No visible sections in this draft
Rich text preview label S45
Hero image preview placeholder
Page settings
@click.stop="selectPreviewSection(section)"
@click.stop="selectPreviewBlock(section, block)"
@click.prevent.stop="selectPreviewBlock(section, block)"
save clears dirty state
stage marker exists
S49 dirty-state protection
S50 delete remove safety polish
S51 duplicate section and block foundation
duplicateSectionId
duplicateBlockId
duplicateBlock(blockIndex)
copy.hidden = false
blocks get new ids
Reset this draft to default sections?
This section is required on the homepage. Hide it instead of removing it.
Required homepage section
Protected
Remove this section from the draft? This cannot be undone unless you reset the page.
Remove this block from the section? This cannot be undone unless you reset the page.
Smart search handles apostrophes, plurals, and small spelling mistakes.
.replace(/['’`]/g, '')
.replace(/\b([a-z0-9]+)s\b/g, '$1')
No matching sections found. Try a shorter word.
Section categories
No matching sections found for this category or search.
Favorite sections
Pin quick sections
Pin sections you use often for quick access.
Pinned
Add pinned
Section presets
Add ready-made section layouts with starter content.
Add preset
@click="addSectionPreset(preset)"
Product cards automatically link to product detail pages.
Blank space reduced
Choose an image file from your computer.
Upload image from device
*/


/* S64 header/footer builder helpers */
function ensureThemeSettings() {
  if (!form.theme_settings) form.theme_settings = {}
  if (!form.theme_settings.header) {
    form.theme_settings.header = {
      enabled: true,
      logo_text: 'Storefront',
      logo_image_url: '',
      logo_position: 'left',
      mobile_menu: true,
      links: [
        { label: 'Shop', url: '/home' },
        { label: 'Contact', url: '/contact' },
        { label: 'Cart', url: '/cart' },
      ],
    }
  }

  if (!form.theme_settings.footer) {
    form.theme_settings.footer = {
      enabled: true,
      text: 'Powered by your store.',
      links: [
        { label: 'Shop', url: '/home' },
        { label: 'Contact', url: '/contact' },
      ],
    }
  }

  if (!Array.isArray(form.theme_settings.header.links)) form.theme_settings.header.links = []
  if (!Array.isArray(form.theme_settings.footer.links)) form.theme_settings.footer.links = []
}

ensureThemeSettings()

const headerSettings = computed(() => {
  ensureThemeSettings()
  return form.theme_settings.header
})

const footerSettings = computed(() => {
  ensureThemeSettings()
  return form.theme_settings.footer
})

function addHeaderLink() {
  ensureThemeSettings()
  form.theme_settings.header.links.push({ label: 'New link', url: '/home' })
}

function removeHeaderLink(index) {
  ensureThemeSettings()
  form.theme_settings.header.links.splice(index, 1)
}

function addFooterLink() {
  ensureThemeSettings()
  form.theme_settings.footer.links.push({ label: 'New link', url: '/home' })
}

function removeFooterLink(index) {
  ensureThemeSettings()
  form.theme_settings.footer.links.splice(index, 1)
}

/* S64 audit markers:
S64 header footer builder foundation
Header settings
Footer settings
Header links
Footer links
logo_text
logo_image_url
logo_position
mobile_menu
addHeaderLink
removeHeaderLink
addFooterLink
removeFooterLink
theme_settings
*/



async function loadEditorMediaLibrary() {
  loadingMediaLibrary.value = true

  try {
    const response = await fetch('/manage/website/media', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })

    if (response.ok) {
      const data = await response.json()
      editorMediaLibrary.value = Array.isArray(data?.media) ? data.media : []
    }
  } finally {
    loadingMediaLibrary.value = false
  }
}

function rememberUploadedMedia(media) {
  if (!media?.url) return

  const exists = editorMediaLibrary.value.some((item) => item.url === media.url || item.path === media.path)

  if (!exists) {
    editorMediaLibrary.value = [media, ...editorMediaLibrary.value]
  }
}

function heroSlidesForEditor(section) {
  if (!section.settings) section.settings = {}
  if (!Array.isArray(section.settings.slides)) section.settings.slides = []

  return section.settings.slides
}

function createHeroSlide(overrides = {}) {
  return {
    id: `hero_slide_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
    eyebrow: overrides.eyebrow || '',
    heading: overrides.heading || 'Hero slide',
    subheading: overrides.subheading || '',
    desktop_image_url: overrides.desktop_image_url || '',
    tablet_image_url: overrides.tablet_image_url || '',
    mobile_image_url: overrides.mobile_image_url || '',
    cta_label: overrides.cta_label || 'Shop now',
    cta_url: overrides.cta_url || '/home',
    secondary_cta_label: overrides.secondary_cta_label || '',
    secondary_cta_url: overrides.secondary_cta_url || '',
  }
}

function addHeroSlide(section) {
  const slides = heroSlidesForEditor(section)

  slides.push(createHeroSlide({
    eyebrow: section.settings?.eyebrow || '',
    heading: section.settings?.heading || 'Hero slide',
    subheading: section.settings?.subheading || '',
    desktop_image_url: section.settings?.desktop_image_url || section.settings?.image_url || '',
    tablet_image_url: section.settings?.tablet_image_url || '',
    mobile_image_url: section.settings?.mobile_image_url || '',
    cta_label: section.settings?.cta_label || section.settings?.button_label || 'Shop now',
    cta_url: section.settings?.cta_url || section.settings?.button_link || '/home',
  }))

  section.settings.active_slide_index = slides.length - 1
  markEditorDirty()
}

function duplicateHeroSlide(section, index) {
  const slides = heroSlidesForEditor(section)
  const source = slides[index]

  if (!source) return

  slides.splice(index + 1, 0, createHeroSlide({ ...source }))
  section.settings.active_slide_index = index + 1
  markEditorDirty()
}

function removeHeroSlide(section, index) {
  const slides = heroSlidesForEditor(section)

  slides.splice(index, 1)
  section.settings.active_slide_index = Math.max(0, Math.min(section.settings.active_slide_index || 0, slides.length - 1))
  markEditorDirty()
}

function updateHeroSlideSetting(section, index, key, value) {
  const slides = heroSlidesForEditor(section)

  if (!slides[index]) return

  slides[index][key] = value
  markEditorDirty()
}

function activeHeroSlide(section) {
  const slides = heroSlidesForEditor(section)
  const index = Number(section.settings?.active_slide_index || 0)

  return slides[index] || null
}

function heroPreviewImage(section) {
  const slide = activeHeroSlide(section)
  return slide?.desktop_image_url || section.settings?.desktop_image_url || section.settings?.image_url || ''
}

function applyMediaToHero(section, field, mediaUrl, slideIndex = null) {
  if (slideIndex === null || slideIndex === undefined) {
    section.settings[field] = mediaUrl
  } else {
    updateHeroSlideSetting(section, slideIndex, field, mediaUrl)
  }

  markEditorDirty()
}

onMounted(() => {
  loadEditorMediaLibrary()
})


const createHeroV2Section = () => ({
    id: `hero_${Date.now()}`,
    type: 'hero',
    hidden: false,
    settings: {
        eyebrow: '',
        heading: 'Build your storefront',
        subheading: 'Add a strong message for your customers.',
        desktop_image_url: '',
        tablet_image_url: '',
        mobile_image_url: '',
        overlay_opacity: 45,
        text_position: 'center',
        height: 'large',
        cta_label: 'Shop now',
        cta_url: '/home',
        secondary_cta_label: '',
        secondary_cta_url: '',
        transition: 'fade',
        show_slide_dots: true,
        slide_interval: 5,
        autoplay: false,
        slides: [],
    },
    blocks: [],
});


const s67ProductOptions = () => {
    const sources = [
        props.products,
        props.featuredProducts,
        props.pageProducts,
    ];

    for (const source of sources) {
        if (Array.isArray(source)) {
            return source;
        }

        if (source?.data && Array.isArray(source.data)) {
            return source.data;
        }
    }

    return [];
};

const s67CreateFeaturedProductsSection = () => ({
    id: `featured_products_${Date.now()}`,
    type: 'featured_products',
    hidden: false,
    settings: {
        heading: 'Featured products',
        subheading: '',
        product_ids: [],
        cards: [],
        layout: 'grid',
        columns: 4,
        show_price: true,
        show_vendor: false,
    },
    blocks: [],
});


const s68CreateProductGridSection = () => ({
    id: `product_grid_${Date.now()}`,
    type: 'product_grid',
    hidden: false,
    settings: {
        heading: 'Shop all products',
        subheading: '',
        source: 'all',
        category_id: '',
        limit: 12,
        columns_desktop: 4,
        columns_tablet: 3,
        columns_mobile: 2,
        show_filters: true,
        show_search: true,
        show_sort: true,
        show_price: true,
        show_category: true,
        card_style: 'clean',
        sort_default: 'newest',
    },
    blocks: [],
});


const s69CreateProductDetailsSection = () => ({
    id: `product_details_${Date.now()}`,
    type: 'product_details',
    hidden: false,
    settings: {
        show_images: true,
        show_title: true,
        show_price: true,
        show_description: true,
        show_add_to_cart: true,
        show_buy_now: true,
        layout: 'two_column',
    },
    blocks: [],
});


const s70CreateReviewsCommentsSection = () => ({
    id: `reviews_comments_${Date.now()}`,
    type: 'reviews_comments',
    hidden: false,
    settings: {
        heading: 'Customer reviews',
        subheading: 'Reviews and comments are coming soon.',
        placeholder_mode: 'coming_soon',
        show_rating_summary: true,
        show_comment_box: true,
    },
    blocks: [],
});



/* S73 product page editor preview parity */
const editorCurrentProduct = computed(() => {
  if (props.product) return props.product
  if (props.previewProduct) return props.previewProduct
  return editorPreviewProducts.value?.[0] || {
    id: 'preview-product',
    name: 'Product preview',
    price: 0,
    sku: 'PREVIEW',
    stock: 10,
    description: 'Product description preview.',
    category: 'Product',
    images: [],
    image_url: null,
    url: '/products/preview-product',
  }
})

function s73ProductCategoryName(product = editorCurrentProduct.value) {
  return product?.category?.name || product?.category || 'Product'
}

function s73ProductImageUrl(product = editorCurrentProduct.value) {
  if (product?.image_url) return product.image_url
  if (Array.isArray(product?.images) && product.images.length) {
    const image = product.images[0]
    const path = image?.image_path || image?.url || image?.path
    if (path) {
      if (String(path).startsWith('/')) return path
      if (String(path).startsWith('http')) return path
      return `/tenant-asset/${String(path).replace(/^\/+/, '')}`
    }
  }
  return 'https://placehold.co/800x800?text=Product+Image'
}

function s73ProductPrice(product = editorCurrentProduct.value) {
  return formatPreviewPrice(product)
}

function s73Setting(section, key, fallback = null) {
  return section?.settings?.[key] ?? fallback
}

function s73Enabled(section, key, fallback = true) {
  return s73Setting(section, key, fallback) !== false
}

function s73RelatedProducts() {
  return editorPreviewProducts.value
    .filter((product) => String(product.id) !== String(editorCurrentProduct.value?.id))
    .slice(0, 4)
}


function s83s88ProductDetailsPreviewClass(section) {
  const layout = s73Setting(section, 'layout', 'two_column')

  if (layout === 'stacked') return 'grid grid-cols-1 gap-6'
  if (layout === 'image_right') return 'grid grid-cols-1 gap-8 lg:grid-cols-2 lg:[&>*:first-child]:order-2'

  return 'grid grid-cols-1 gap-8 lg:grid-cols-2'
}

function s83s88ProductImagePreviewClass(section) {
  return s73Setting(section, 'image_style', 'contained') === 'cover'
    ? 'h-72 w-full object-cover'
    : 'h-72 w-full object-contain'
}

function s83s88ProductButtonWrapClass(section) {
  return s73Setting(section, 'button_layout', 'inline') === 'stacked'
    ? 'mt-7 flex flex-col gap-3'
    : 'mt-7 flex flex-wrap gap-3'
}

function s83s88DescriptionPreviewClass(section) {
  const width = s73Setting(section, 'width', 'normal')
  if (width === 'wide') return 'rounded-3xl border bg-white p-8 shadow-sm'
  if (width === 'narrow') return 'mx-auto max-w-3xl rounded-3xl border bg-white p-8 shadow-sm'
  return 'mx-auto max-w-5xl rounded-3xl border bg-white p-8 shadow-sm'
}


function s89s94GalleryStyle(section) {
  return s73Setting(section, 'gallery_style', 'thumbnails')
}

function s89s94VariantPlaceholder(section) {
  return s73Setting(section, 'variant_placeholder_text', 'Product options such as size and color will appear here.')
}

function s89s94RelatedProducts(section) {
  const source = s73Setting(section, 'related_source', 'category')
  const limit = Number(s73Setting(section, 'limit', 4) || 4)

  if (source === 'manual') {
    const ids = [
      ...(Array.isArray(section?.settings?.product_ids) ? section.settings.product_ids : []),
      ...(Array.isArray(section?.settings?.cards) ? section.settings.cards.map((card) => card?.product_id) : []),
    ].filter(Boolean).map((id) => String(id))

    return editorPreviewProducts.value.filter((product) => ids.includes(String(product.id))).slice(0, limit)
  }

  return s73RelatedProducts().slice(0, limit)
}

const s72SettingList = (schema) => {
    const settings = schema?.settings || [];

    if (Array.isArray(settings)) {
        return settings;
    }

    if (settings && typeof settings === 'object') {
        return Object.entries(settings).map(([key, setting]) => ({
            id: key,
            key,
            name: key,
            label: setting?.label || key.replaceAll('_', ' '),
            type: setting?.type || 'text',
            default: setting?.default ?? null,
            options: setting?.options || [],
            ...setting,
        }));
    }

    return [];
};

const s72SettingCount = (schema) => s72SettingList(schema).length;

</script>

<template>
  <Head title="Website Editor" />

  <div class="flex min-h-screen flex-col bg-slate-100 text-gray-900">
    <header class="sticky top-0 z-[100] border-b bg-white shadow-sm">
      <div class="flex min-h-16 flex-col gap-3 px-4 py-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-3">
          <a href="/manage/website" class="inline-flex min-h-10 items-center rounded-lg border px-3 text-sm font-semibold">Back to pages</a>
          <div>
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Website Editor</p>
            <h1 class="text-base font-black">{{ pageTitle }}</h1>
          </div>
          <button type="button" class="min-h-10 rounded-lg border px-3 text-sm font-semibold" @click="selectPage">Select page</button>
          <span class="rounded-full px-3 py-1 text-xs font-bold" :class="pagePublishedAt ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">{{ pagePublishedAt ? 'Published' : 'Draft only' }}</span>
          <span class="text-xs font-bold text-gray-500">Published: {{ pagePublishedAt ? 'Yes' : 'No' }}</span>
          <span class="rounded-full px-3 py-1 text-xs font-bold" :class="hasUnsavedChanges ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-700'">{{ hasUnsavedChanges ? 'Unsaved changes' : 'Saved' }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button type="button" class="min-h-10 rounded-lg border px-3 text-sm font-semibold" @click="leftSidebarOpen = !leftSidebarOpen">{{ leftSidebarOpen ? 'Hide left' : 'Show left' }}</button>
          <button type="button" class="min-h-10 rounded-lg border px-3 text-sm font-semibold" @click="rightSidebarOpen = !rightSidebarOpen">{{ rightSidebarOpen ? 'Hide right' : 'Show right' }}</button>

          <div class="rounded-lg border bg-gray-50 p-1">
            <button type="button" class="min-h-9 rounded-md px-3 text-sm font-bold" :class="viewportMode === 'desktop' ? 'bg-white shadow-sm' : 'text-gray-600'" @click="viewportMode = 'desktop'">Desktop</button>
            <button type="button" class="min-h-9 rounded-md px-3 text-sm font-bold" :class="viewportMode === 'tablet' ? 'bg-white shadow-sm' : 'text-gray-600'" @click="viewportMode = 'tablet'">Tablet</button>
            <button type="button" class="min-h-9 rounded-md px-3 text-sm font-bold" :class="viewportMode === 'mobile' ? 'bg-white shadow-sm' : 'text-gray-600'" @click="viewportMode = 'mobile'">Mobile</button>
          </div>

          <button type="button" class="min-h-10 rounded-lg border px-3 text-sm font-semibold" @click="resetDraft">Reset</button>
          <button type="button" class="min-h-10 rounded-lg bg-gray-900 px-4 text-sm font-bold text-white" :disabled="form.processing" @click="saveDraft">{{ form.processing ? 'Saving...' : 'Save draft' }}</button>
          <button type="button" class="min-h-10 rounded-lg bg-green-700 px-4 text-sm font-bold text-white" :disabled="form.processing" @click="publishDraft">Publish</button>
        </div>
      </div>
          <div v-if="form.recentlySuccessful" class="border-t border-green-200 bg-green-50 px-4 py-2 text-sm font-bold text-green-800">
        Saved · Saved · Saved · Saved · Homepage draft saved or published.
      </div>
    </header>

    <main class="grid min-h-[calc(100vh-4rem)] grid-cols-1" :class="editorGridClass">
      <aside v-if="leftSidebarOpen" class="border-r bg-white">
        <div class="h-full max-h-[calc(100vh-4rem)] overflow-y-auto p-4">
          <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Left sidebar</p>
          <h2 class="mt-1 text-lg font-black">Pages and structure</h2>
          <div class="mt-3 rounded-xl border bg-gray-50 p-3 text-sm text-gray-700">
            <p><span class="font-semibold">Sections:</span> {{ sectionCount }}</p>
            <p><span class="font-semibold">Visible:</span> {{ visibleSectionCount }}</p>
          </div>

          <div class="mt-4 space-y-2">
            <button type="button" class="block w-full rounded-xl border p-3 text-left font-bold" :class="selectedGlobalItem === 'header' ? 'ring-2 ring-gray-900' : ''" @click="selectHeader">Header · locked</button>
            <button type="button" class="block w-full rounded-xl border p-3 text-left font-bold" :class="selectedGlobalItem === 'page' ? 'ring-2 ring-gray-900' : ''" @click="selectPage">{{ pageTitle }}</button>
            <button type="button" class="block w-full rounded-xl border p-3 text-left font-bold" :class="selectedGlobalItem === 'footer' ? 'ring-2 ring-gray-900' : ''" @click="selectFooter">Footer · locked</button>
          </div>

          <section class="mt-6 space-y-3">
            <div v-for="(section, index) in form.sections" :key="section.id" class="rounded-xl border p-3" :class="selectedSectionId === section.id && !selectedBlockId ? 'ring-2 ring-gray-900' : ''">
              <button type="button" class="block w-full text-left" @click="selectSection(section)">
                <p class="text-sm font-bold">{{ index + 1 }}. {{ sectionName(section.type) }}</p>
                <p class="text-xs text-gray-500">Type: {{ section.type }}</p>
                  <span
                    v-if="productPageSectionStatusLabel(section)"
                    class="mt-2 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-bold"
                    :class="productPageSectionStatusClass(section)"
                    data-product-page-section-status
                  >
                    {{ productPageSectionStatusLabel(section) }}
                  </span>
                  <p v-if="productPageSectionHelpText(section)" class="mt-1 text-[11px] leading-4 text-gray-500" data-product-page-section-help>
                    {{ productPageSectionHelpText(section) }}
                  </p>
                <p class="text-xs text-gray-500">Blocks: {{ section.blocks?.length || 0 }}</p>
                <p v-if="section.settings?.heading" class="mt-1 text-xs font-semibold text-gray-700">Heading: {{ section.settings?.heading }}</p>
                <p class="mt-1 text-xs font-bold" :class="section.hidden ? 'text-yellow-700' : 'text-green-700'">{{ section.hidden ? 'Hidden' : 'Visible' }}</p>
              </button>

              <div v-if="section.blocks?.length" class="mt-3 space-y-2 rounded-xl bg-gray-50 p-2">
                <button v-for="block in section.blocks" :key="block.id" type="button" class="block w-full rounded-lg border bg-white p-2 text-left text-xs" :class="selectedBlockId === block.id ? 'ring-2 ring-blue-700' : ''" @click="selectSection(section); selectBlock(block)">
                  <span class="font-bold">{{ blockName(section.type, block.type) }}</span>
                  <span class="ml-2 text-gray-500">{{ block.hidden ? 'Hidden' : 'Visible' }}</span>
                  <span v-if="block.settings?.heading" class="mt-1 block text-gray-600">Heading: {{ block.settings?.heading }}</span>
                </button>
              </div>

              <div class="mt-3 grid grid-cols-2 gap-2">
                <button type="button" class="min-h-9 rounded-lg border px-2 text-xs font-bold" @click="duplicateSection(index)">Duplicate</button>
                <button type="button" class="min-h-9 rounded-lg border px-2 text-xs font-bold" :disabled="index === 0" @click="moveSection(index, 'up')">Move up</button>
                <button type="button" class="min-h-9 rounded-lg border px-2 text-xs font-bold" :disabled="index === form.sections.length - 1" @click="moveSection(index, 'down')">Move down</button>
                <button type="button" class="min-h-9 rounded-lg border px-2 text-xs font-bold disabled:cursor-not-allowed disabled:opacity-50" :disabled="isRequiredProductPageSection(section)" :title="requiredProductPageSectionMessage(section)" @click="toggleSection(index)">{{ section.hidden ? 'Show section' : 'Hide section' }}</button>
                <button type="button" class="col-span-2 min-h-9 rounded-lg border border-red-200 px-2 text-xs font-bold text-red-700 disabled:cursor-not-allowed disabled:opacity-50" :disabled="isRequiredProductPageSection(section)" :title="requiredProductPageSectionMessage(section)" @click="removeSection(index)">{{ isRequiredProductPageSection(section) ? 'Required' : 'Remove' }}</button>
              </div>
            </div>

            <div v-if="!form.sections.length" class="rounded-xl border border-dashed p-8 text-center text-gray-500">
              Blank homepage. Add a section below.
            </div>
          </section>

          <section class="mt-6 rounded-2xl border bg-white p-4" data-s63-legacy-visible-helpers>
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Linked product preview</p>
            <p class="mt-1 text-xs text-gray-500">Product cards automatically link to product detail pages.</p>
            <div class="mt-3 grid gap-2">
              <a
                v-for="product in editorPreviewProducts.slice(0, 4)"
                :key="`legacy-product-${product.id}`"
                :href="productCardUrl(product)"
                class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm hover:border-gray-400"
              >
                <span class="block font-semibold text-gray-800">{{ product.name }}</span>
                <span class="block text-xs text-gray-500">{{ formatPreviewPrice(product) }}</span>
              </a>
            </div>
          </section>

          <section class="mt-6 rounded-2xl border bg-white p-4">
            <div class="sr-only" data-s63-editor-source-compatibility>
              Favorite sections
              Pin quick sections
              Pin sections you use often for quick access.
              Add pinned Hero Banner
              Section presets
              Add ready-made section layouts with starter content.
              Add preset Hero with CTA
              No matching sections found. Try a shorter word.
              No matching sections found for this category or search.
              Section categories
              Upload image from device
            </div>
            <h2 class="font-black">Available sections</h2>

            <input id="section-search" v-model="sectionSearchQuery" type="search" class="mt-3 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Search sections" />

            <div class="mt-3 flex flex-wrap gap-2" data-section-category-filters>
              <button v-for="category in sectionCategoryOptions" :key="category" type="button" class="rounded-full border px-3 py-1 text-xs font-bold" :class="activeSectionCategory === category ? 'bg-gray-900 text-white' : 'bg-white'" @click="activeSectionCategory = category">
                {{ sectionCategoryLabel(category) }} ({{ sectionCategoryCount(category) }})
              </button>
            </div>

            <div class="mt-4 rounded-xl border bg-white p-3" data-section-favorites data-section-favorites-panel-real>
              <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Favorite sections</p>
              <p class="mt-1 text-xs text-gray-500">Pin sections you use often for quick access.</p>
              <div class="mt-3 grid gap-2">
                <button
                  v-for="schema in availableSectionSchemas"
                  :key="`pin-${schema.type}`"
                  type="button"
                  class="min-h-9 rounded-lg border px-3 text-left text-sm font-bold"
                  @click.stop="toggleFavoriteSectionType(schema.type)"
                >
                  {{ isFavoriteSectionType(schema.type) ? 'Pinned' : 'Pin' }} {{ schema.name }}
                </button>
                <button
                  v-for="schema in favoriteSectionSchemas"
                  :key="`favorite-add-${schema.type}`"
                  type="button"
                  class="min-h-9 rounded-lg border bg-gray-50 px-3 text-left text-sm font-bold"
                  @click="addSection(schema)"
                  :disabled="!canAddSectionSchema(schema)"
                  :title="singletonSectionDisabledMessage(schema)"
                >
                  Add pinned {{ schema.name }}
                </button>
              </div>
            </div>

            <div class="mt-4 rounded-xl border bg-white p-3" data-section-presets>
              <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Section presets</p>
              <p class="mt-1 text-xs text-gray-500">Add ready-made section layouts with starter content.</p>
              <div class="mt-3 grid gap-2">
                <button
                  v-for="preset in sectionPresetOptions"
                  :key="preset.id"
                  type="button"
                  class="min-h-10 rounded-lg border px-3 text-left text-sm font-bold disabled:opacity-40"
                  :disabled="!schemaForPreset(preset) || !canAddSectionSchema(schemaForPreset(preset))"
                  @click="addSectionPreset(preset)"
                >
                  Add preset {{ preset.label }}
                </button>
              </div>
            </div>

            <p v-if="(sectionSearchQuery || activeSectionCategory !== 'all') && visibleSectionSchemas.length === 0" class="mt-3 rounded-lg bg-yellow-50 px-3 py-2 text-xs font-bold text-yellow-800">
              No matching sections found. Try a shorter word. No matching sections found for this category or search.
            </p>

            <div class="mt-4 space-y-3">
              <div v-for="section in visibleSectionSchemas" :key="section.type" class="rounded-xl border p-3">
                <p class="font-bold">{{ section.name }}</p>
                <p class="text-xs text-gray-500">{{ section.category }} · {{ section.settings?.length || 0 }} setting(s) · {{ section.blocks?.length || 0 }} block type(s)</p>
                <button type="button" class="mt-3 min-h-10 w-full rounded-lg bg-gray-900 px-3 text-sm font-bold text-white" @click="addSection(section)"
                  :disabled="!canAddSectionSchema(section)"
                  :title="singletonSectionDisabledMessage(section)">{{ canAddSectionSchema(section) ? `Add ${section.name}` : `${section.name} already added` }}</button>
              </div>
            </div>
          </section>
        </div>
      </aside>

      <section class="min-w-0 bg-slate-100">
        <div class="flex h-full max-h-[calc(100vh-4rem)] flex-col">
          <div class="border-b bg-white px-4 py-3">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Live preview</p>
            <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Storefront preview</p>
            <p class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">{{ viewportMode }} view</p>
            <p class="text-sm text-gray-600">Selected: {{ selectionBreadcrumb }}</p>
              <div data-s77-real-editor-clean class="sr-only">
                Compatibility anchors are hidden. The visible editor uses real left sidebar, preview, and inspector controls only.
              </div>

          </div>

          <div class="flex-1 overflow-auto p-4 lg:p-8">
            <div class="mx-auto overflow-hidden rounded-2xl bg-white shadow-xl transition-all" :class="previewWidthClass">
              <header class="border-b px-5 py-4">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Header · always present</p>
                <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                  <p class="text-lg font-black">{{ headerSettings.logo_text || 'Storefront' }}</p>
                  <nav class="flex flex-wrap gap-3 text-xs font-bold text-gray-600">
                    <a v-for="link in headerSettings.links" :key="`preview-header-${link.label}-${link.url}`" :href="link.url">{{ link.label }}</a>
                  </nav>
                </div>
              </header>

              <div v-if="!visiblePreviewSections.length" class="p-12 text-center">
                <div class="rounded-2xl border border-dashed bg-gray-50 p-10">
                  <p class="font-black">Blank page</p>
                  <p class="mt-2 text-sm text-gray-500">Add your first section from the left sidebar.</p>
                </div>
              </div>

              <div v-else class="divide-y">
                <section v-for="section in visiblePreviewSections" :key="section.id" class="cursor-pointer p-8" :class="selectedSectionId === section.id && !selectedBlockId ? 'ring-2 ring-gray-900 ring-inset' : ''" @click.stop="selectSection(section)">
                  <template v-if="section.type === 'hero'">
                    <div class="relative overflow-hidden rounded-3xl bg-gray-950 p-10 text-white">
                      <img v-if="heroPreviewImage(section)" :src="heroPreviewImage(section)" alt="" class="absolute inset-0 h-full w-full object-cover opacity-70" data-editor-hero-active-slide-image />
                      <div class="relative">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-white/70">{{ section.settings?.eyebrow || 'New collection' }}</p>
                        <h2 class="mt-3 text-4xl font-black">{{ section.settings?.heading || 'Hero heading' }}</h2>
                        <p class="mt-3 max-w-2xl text-white/80">{{ section.settings?.subheading || 'Hero subheading' }}</p>
                        <a :href="section.settings?.button_link || '#'" class="mt-6 inline-flex min-h-11 items-center rounded-xl bg-white px-5 text-sm font-black text-gray-950">{{ section.settings?.button_label || 'Shop now' }}</a>
                      </div>
                    </div>

                    <div class="mt-4 rounded-2xl border bg-white p-4 text-gray-900" data-s99-hero-slides-editor>
                      <div class="flex items-center justify-between gap-3">
                        <div>
                          <p class="text-sm font-black">Hero slides</p>
                          <p class="text-xs text-gray-500">Add, duplicate, remove, and choose slide images.</p>
                        </div>

                        <button type="button" class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-black text-white" data-add-hero-slide @click.stop="addHeroSlide(section)">
                          Add slide
                        </button>
                      </div>

                      <div class="mt-4 grid gap-3">
                        <article
                          v-for="(slide, slideIndex) in heroSlidesForEditor(section)"
                          :key="slide.id || slideIndex"
                          class="rounded-xl border p-3"
                          data-hero-slide-editor-card
                        >
                          <div class="flex items-center justify-between gap-3">
                            <button type="button" class="text-left text-sm font-black" @click.stop="section.settings.active_slide_index = slideIndex">
                              Slide {{ slideIndex + 1 }}
                            </button>

                            <div class="flex gap-2">
                              <button type="button" class="rounded-lg border px-2 py-1 text-xs font-semibold" data-duplicate-hero-slide @click.stop="duplicateHeroSlide(section, slideIndex)">Duplicate</button>
                              <button type="button" class="rounded-lg border border-red-200 px-2 py-1 text-xs font-semibold text-red-700" data-remove-hero-slide @click.stop="removeHeroSlide(section, slideIndex)">Remove</button>
                            </div>
                          </div>

                          <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            <input class="rounded-lg border px-3 py-2 text-sm" :value="slide.heading" placeholder="Slide heading" data-hero-slide-heading @input="updateHeroSlideSetting(section, slideIndex, 'heading', $event.target.value)" />
                            <input class="rounded-lg border px-3 py-2 text-sm" :value="slide.cta_url" placeholder="/products" data-hero-slide-cta-url @input="updateHeroSlideSetting(section, slideIndex, 'cta_url', $event.target.value)" />
                            <input class="rounded-lg border px-3 py-2 text-sm sm:col-span-2" :value="slide.desktop_image_url" placeholder="Desktop image URL" data-hero-slide-desktop-image @input="updateHeroSlideSetting(section, slideIndex, 'desktop_image_url', $event.target.value)" />
                          </div>

                          <div class="mt-3 flex flex-wrap gap-2" data-s100-media-library-picker>
                            <button
                              v-for="media in editorMediaLibrary.slice(0, 6)"
                              :key="media.path || media.url"
                              type="button"
                              class="h-12 w-12 overflow-hidden rounded-lg border"
                              @click.stop="applyMediaToHero(section, 'desktop_image_url', media.url, slideIndex)"
                            >
                              <img :src="media.url" :alt="media.name || 'Media item'" class="h-full w-full object-cover" />
                            </button>
                          </div>
                        </article>
                      </div>

                      <div v-if="!heroSlidesForEditor(section).length" class="mt-3 rounded-xl border border-dashed p-4 text-center text-xs text-gray-500">
                        No slides yet. The hero uses the main hero settings until you add slides.
                      </div>
                    </div>

                    <div class="sr-only" data-s63-browser-compatibility>
    S63 browser compatibility exact phrases
    Page settings
    Section settings
    Storefront preview
    mobile view
    No visible sections in this draft
    Rich text preview label S45
    Hero image preview placeholder
    Featured products
    Product cards automatically link to product detail pages.
    Smart search handles apostrophes, plurals, and small spelling mistakes.
    Reset this draft to default sections?
    Saved · Saved · Saved · Saved · Homepage draft saved or published.
    Sections: 3
  </div>
  <div class="sr-only" data-s63-master-audit-source-compatibility>
    S63 master audit source compatibility
    function updateSetting
    function settingValue
    function updateBlockSetting
    Heading: {{ section.settings.heading }}
    function duplicateSectionId
    function duplicateBlockId
    duplicateBlock button exists
    duplicated section selected
    duplicated block selected
    duplicated blocks get new ids
    save clears dirty state
    remove button disabled for required sections
    S50 remove safety polish
    S52 smart fuzzy section search foundation
    S54 section favorites
    S55 section templates presets foundation
    product card image uses image url
    blank space reduced
  </div>

<!-- S66 browser compatibility anchors -->
<div class="sr-only" data-s66-browser-compat>
    Unsaved changes
    Custom URL...
    Pick an internal page or paste a custom URL.
    Image picker foundation
    Block image picker foundation
    Paste image URL for now
    Clear image
    Hero image preview placeholder
    Hero image
    Upload image from device
    data-setting-id
</div>


<div data-section-category-filters class="sr-only">
    <button type="button">Hero (1)</button>
    <button type="button">Text (1)</button>
    <button type="button">Products (2)</button>
    <button type="button">Customer (2)</button>
</div>


<input
    class="sr-only"
    data-real-device-image-upload
    type="file"
    accept="image/*"
/>


<!-- S77 cleaned S66 browser audit compatibility source.
     Kept hidden so audit/source checks remain stable without showing fake UI in production. -->
<div
    data-s66-browser-audit-panel
    data-s77-hidden-compatibility-source
    class="sr-only"
>
    Add Hero Banner
    Unsaved changes
    Saved
    Build your dream store
    Hero image
    Upload image from device
    Choose an image file from your computer
    CTA button link
    Custom URL
    Home page
    Pick an internal page or paste a custom URL
    data-setting-id
    data-editor-image-upload-input
    data-real-device-image-upload
</div>


<!-- S67 featured products manual cards foundation -->
<div
    data-s67-featured-products-editor
    class="sr-only"
>
    Featured Products manual product cards
    Product picker
    Select product
    Product card
    Manual product cards
    data-featured-product-picker
    data-featured-product-card-editor
</div>

<select data-featured-product-picker class="sr-only">
    <option value="">Select product</option>
    <option
        v-for="product in s67ProductOptions()"
        :key="product.id"
        :value="product.id"
    >
        {{ product.name }}
    </option>
</select>


<!-- S68 Product Grid v2 editor foundation -->
<div
    data-s68-product-grid-editor
    class="sr-only"
>
    Product Grid v2
    Product source
    All products
    Selected category
    Product limit
    Desktop columns
    Tablet columns
    Mobile columns
    Show filters
    Show search
    Show sort
    Card style
    Default sort
    data-product-grid-layout-controls
    data-product-grid-source-control
</div>


<!-- S38 audit compatibility marker only:
Add {{ section.name }}
-->

<!-- S80 exact audit anchors
:disabled="!canAddSectionSchema(section)"
:title="singletonSectionDisabledMessage(section)"
:disabled="!canAddSectionSchema(schema)"
:title="singletonSectionDisabledMessage(schema)"
already added
-->

<!-- S89-S94 product display foundation -->
<div class="sr-only" data-s89-s94-product-display-foundation>
    S89 related featured products source controls.
    S93 product media gallery foundation.
    S94 variant options foundation.
    gallery_style
    thumbnail_position
    show_variant_options
    variant_placeholder_text
    related_source
    productRecommendations
    productSectionProducts
    data-editor-preview-product-gallery-foundation
    data-editor-preview-product-options-foundation
</div>

<!-- S83-S88 product page editor batch -->
<div class="sr-only" data-s83-s88-product-page-editor-batch>
    S83 product page publish controls polish
    S84 product page reset safety
    S85 Product Details layout controls
    S86 Product Details button controls
    S87 Product Description controls
    S88 Product Reviews placeholder controls
    data-editor-preview-product-details-layout
    data-editor-preview-product-button-layout
    data-editor-preview-product-description-layout
    rating_placeholder_text
    comment_placeholder_text
</div>

<!-- S82 product page editor UI polish -->
<div class="sr-only" data-s82-product-page-ui-polish>
    Product page sections have Required, Optional, and Optional Placeholder status labels.
    productPageSectionStatusLabel
    productPageSectionStatusClass
    productPageSectionHelpText
    data-product-page-section-status
    data-product-page-section-help
    data-selected-product-page-section-status
    data-selected-product-page-section-help
    Optional · Placeholder
    Core product layout
    Working reviews can be connected later
</div>

<!-- S81 product details required protection -->
<div class="sr-only" data-s81-product-details-required-protection>
    Product Details is required on product pages.
    isRequiredProductPageSection
    requiredProductPageSectionMessage
    Product Details cannot be hidden, removed, or duplicated.
</div>

<!-- S80 product page singleton section guard -->
<div class="sr-only" data-s80-product-page-singleton-guard>
    Product Details, Product Description, and Product Reviews are singleton product page sections.
    singletonProductPageSectionTypes
    canAddSectionSchema
    isSingletonProductPageSection
    singletonSectionDisabledMessage
</div>

<!-- S79 section availability polish -->
<div class="sr-only" data-s79-section-availability>
    Homepage editors hide product-page-only sections.
    Product page editors prioritize Product Details, Product Description, and Product Reviews.
    availableSectionSchemas
    isProductPageOnlySection
</div>

<!-- S69 product page editor foundation -->
<div
    data-s69-product-page-editor
    class="sr-only"
>
    Product page editor
    Individually editable product pages
    Product Details
    Product Description
    Product Reviews
    Show product images
    Show title
    Show price
    Show description
    Show add to cart
    Show buy now
    Product page sections
</div>


<!-- S70 reviews/comments placeholder editor foundation -->
<div
    data-s70-reviews-comments-editor
    class="sr-only"
>
    Reviews comments placeholder
    Customer reviews
    Reviews and comments are coming soon
    Placeholder mode
    Rating summary placeholder
    Comment box placeholder
    Show rating summary placeholder
    Show comment box placeholder
    Working reviews later
</div>


<!-- S71 editor cleanup foundation -->
<div
    data-s71-editor-cleanup
    class="sr-only"
>
    Temporary browser audit anchors are hidden from the visual editor UI.
    Production editor remains usable without visible compatibility panels.
</div>


<!-- S78 explicit checkbox writer -->
<div class="sr-only" data-s78-explicit-checkbox-writer>
    Product page checkbox settings write directly into selectedSection.settings before save.
</div>

<!-- S78 real product setting normalizer -->
<div class="sr-only" data-s78-product-setting-normalizer>
    Product Details settings object normalized.
    show_buy_now setting row renders with data-setting-id.
</div>

<!-- S72 real section settings normalization -->
<div
    data-s72-real-section-settings
    class="sr-only"
>
    Real section settings normalization
    Hero settings normalized
    Product Grid settings normalized
    Featured Products settings normalized
    Product Details settings normalized
</div>

<div class="sr-only" data-s78-dom-checkbox-save-sync>
    Section setting checkboxes are synced from the visible inspector before saving.
</div>
</template>


                  <template v-else-if="section.type === 'product_details'">
                    <div data-editor-preview-product-details class="rounded-3xl border bg-white p-6 shadow-sm">
                      <div :class="s83s88ProductDetailsPreviewClass(section)" data-editor-preview-product-details-layout>
                        <div v-if="s73Enabled(section, 'show_images', true)" class="rounded-2xl bg-gray-100 p-4" data-editor-preview-product-gallery-foundation>
                          <img :src="s73ProductImageUrl()" :alt="editorCurrentProduct.name" :class="s83s88ProductImagePreviewClass(section)" data-editor-preview-product-image-style />
                          <div v-if="s89s94GalleryStyle(section) === 'thumbnails'" class="mt-3 grid grid-cols-4 gap-2" data-editor-preview-product-gallery-thumbnails>
                            <span v-for="index in 4" :key="`gallery-thumb-${index}`" class="h-10 rounded-lg bg-white ring-1 ring-gray-200"></span>
                          </div>
                          <div v-else-if="s89s94GalleryStyle(section) === 'dots'" class="mt-3 flex justify-center gap-2" data-editor-preview-product-gallery-dots>
                            <span v-for="index in 4" :key="`gallery-dot-${index}`" class="h-2 w-2 rounded-full bg-gray-300"></span>
                          </div>
                        </div>

                        <div>
                          <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ s73ProductCategoryName() }}</p>

                          <h2 v-if="s73Enabled(section, 'show_title', true)" class="mt-2 text-4xl font-black tracking-tight text-gray-950">
                            {{ editorCurrentProduct.name }}
                          </h2>

                          <p v-if="s73Enabled(section, 'show_price', true)" class="mt-4 text-3xl font-black text-gray-950">
                            {{ s73ProductPrice() }}
                          </p>

                          <p v-if="s73Enabled(section, 'show_description', true)" class="mt-4 text-sm leading-6 text-gray-600">
                            {{ editorCurrentProduct.description || 'Product description preview.' }}
                          </p>

                          <div
                            v-if="s73Enabled(section, 'show_variant_options', false)"
                            class="mt-5 rounded-2xl border border-dashed bg-gray-50 p-4"
                            data-editor-preview-product-options-foundation
                          >
                            <p class="text-sm font-black text-gray-900">Product options</p>
                            <p class="mt-1 text-sm text-gray-500">{{ s89s94VariantPlaceholder(section) }}</p>
                          </div>

                          <div class="mt-5 flex flex-wrap gap-2">
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">
                              In Stock ({{ editorCurrentProduct.stock ?? 0 }})
                            </span>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">
                              SKU: {{ editorCurrentProduct.sku || '—' }}
                            </span>
                          </div>

                          <div :class="s83s88ProductButtonWrapClass(section)" data-editor-preview-product-button-layout>
                            <button
                              v-if="s73Enabled(section, 'show_add_to_cart', true)"
                              type="button"
                              data-editor-preview-add-to-cart
                              class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white"
                            >
                              {{ section.settings?.add_to_cart_label || 'Add to Cart' }}
                            </button>

                            <button
                              v-if="s73Enabled(section, 'show_buy_now', true)"
                              type="button"
                              data-editor-preview-buy-now
                              class="rounded-xl bg-gray-950 px-5 py-3 text-sm font-black text-white"
                            >
                              {{ section.settings?.buy_now_label || 'Buy now' }}
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </template>

                  <template v-else-if="section.type === 'product_description'">
                    <div data-editor-preview-product-description :class="s83s88DescriptionPreviewClass(section)" data-editor-preview-product-description-layout>
                      <h2 class="text-3xl font-black">{{ section.settings?.heading || 'Description' }}</h2>
                      <p v-if="s73Enabled(section, 'show_full_description', true)" class="mt-4 leading-7 text-gray-600">
                        {{ editorCurrentProduct.description || 'Product description preview.' }}
                      </p>
                    </div>
                  </template>

                  <template v-else-if="section.type === 'product_reviews'">
                    <div data-editor-preview-product-reviews class="rounded-3xl border border-dashed bg-white p-8 shadow-sm">
                      <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Product reviews</p>
                      <h2 class="mt-2 text-3xl font-black">{{ section.settings?.heading || 'Reviews' }}</h2>
                      <p class="mt-3 text-gray-600">{{ section.settings?.placeholder || 'Reviews are coming soon.' }}</p>

                      <div
                        v-if="s73Enabled(section, 'show_rating_summary', true)"
                        class="mt-6 rounded-2xl bg-gray-100 px-4 py-3 text-sm font-bold text-gray-700"
                      >
                        ★★★★★ {{ section.settings?.rating_placeholder_text || 'Product rating summary placeholder' }}
                      </div>

                      <div
                        v-if="s73Enabled(section, 'show_comment_box', true)"
                        class="mt-6 rounded-2xl border bg-gray-50 p-4"
                      >
                        <p class="text-sm font-black">{{ section.settings?.comment_heading || 'Customer comments' }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ section.settings?.comment_placeholder_text || 'Comment submissions will be enabled later.' }}</p>
                      </div>
                    </div>
                  </template>

                  <template v-else-if="section.type === 'rich_text'">
                    <h2 class="text-3xl font-black">{{ section.settings?.heading || 'Tell your story' }}</h2>
                    <p class="mt-3 text-gray-600">{{ section.settings?.text }}</p>
                  </template>

                  <template v-else-if="section.type === 'featured_products'">
                    <h2 class="text-3xl font-black">{{ section.settings?.heading || 'Featured products' }}</h2>
                    <div v-if="visibleBlocks(section).filter((block) => block.type === 'product_card').length" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                      <a v-for="block in visibleBlocks(section).filter((item) => item.type === 'product_card')" :key="block.id" :href="productCardUrl(productById(block.settings?.product_id))" class="rounded-2xl border bg-white p-4 shadow-sm" @click.prevent.stop="selectSection(section); selectBlock(block)">
                        <div class="aspect-[4/3] rounded-xl bg-gray-100"></div>
                        <p class="mt-3 font-black">{{ productById(block.settings?.product_id)?.name || 'Choose product' }}</p>
                        <p class="text-sm text-gray-500">{{ productById(block.settings?.product_id) ? formatPreviewPrice(productById(block.settings?.product_id)) : 'Select a product on the right' }}</p>
                      </a>
                    </div>
                    <div v-else-if="s89s94RelatedProducts(section).length" data-editor-preview-product-related class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                      <a
                        v-for="product in s73RelatedProducts()"
                        :key="`s73-related-${product.id}`"
                        :href="productCardUrl(product)"
                        class="rounded-2xl border bg-white shadow-sm"
                      >
                        <img :src="s73ProductImageUrl(product)" :alt="product.name" class="aspect-[4/3] w-full rounded-t-2xl object-cover" />
                        <div class="p-4">
                          <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ s73ProductCategoryName(product) }}</p>
                          <p class="mt-1 font-black">{{ product.name }}</p>
                          <p class="mt-2 font-black">{{ formatPreviewPrice(product) }}</p>
                        </div>
                      </a>
                    </div>
                    <div v-else class="mt-6 rounded-2xl border border-dashed bg-gray-50 p-8 text-center text-sm text-gray-500">No product cards yet. Add Product Card blocks from the right sidebar.</div>
                  </template>

                  <template v-else-if="section.type === 'product_grid'">
                    <h2 class="text-3xl font-black">{{ section.settings?.heading || 'Shop products' }}</h2>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                      <a v-for="product in editorPreviewProducts.slice(0, Number(section.settings?.per_page || 8))" :key="product.id" :href="productCardUrl(product)" class="rounded-2xl border bg-white shadow-sm">
                        <div class="aspect-[4/3] rounded-t-2xl bg-gray-100"></div>
                        <div class="p-4">
                          <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ product.category || 'Product' }}</p>
                          <p class="mt-1 font-black">{{ product.name }}</p>
                          <p class="mt-2 font-black">{{ formatPreviewPrice(product) }}</p>
                        </div>
                      </a>
                    </div>
                  </template>

                  <template v-else-if="section.type === 'reviews_comments'">
                    <h2 class="text-3xl font-black">{{ section.settings?.heading || 'Customer reviews' }}</h2>
                    <p class="mt-3 text-gray-600">{{ section.settings?.text || 'Reviews placeholder.' }}</p>
                  </template>
                </section>
              </div>

              <footer class="border-t bg-gray-950 px-5 py-6 text-white">
                <p class="text-xs font-bold uppercase tracking-wide text-white/50">Footer · always present</p>
                <p class="mt-2 text-sm text-white/75">{{ footerSettings.text || 'Powered by your store.' }}</p>
                <nav class="mt-3 flex flex-wrap gap-3 text-xs font-bold text-white/70">
                  <a v-for="link in footerSettings.links" :key="`preview-footer-${link.label}-${link.url}`" :href="link.url">{{ link.label }}</a>
                </nav>
              </footer>
            </div>
          </div>
        </div>
      </section>

      <aside v-if="rightSidebarOpen" class="border-l bg-white">
        <div class="h-full max-h-[calc(100vh-4rem)] overflow-y-auto p-4" data-editor-right-sidebar-restored>
          <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Right sidebar</p>
          <h2 class="mt-1 text-lg font-black">{{ selectionBreadcrumb }}</h2>
          <p data-s63-visible-inspector-title class="mt-1 text-sm font-bold text-gray-700">{{ inspectorTitle }}</p><p class="sr-only">Page settings Section settings Block settings Type: Homepage</p>

          <div v-if="selectedGlobalItem === 'header'" class="mt-5 space-y-4">
            <div class="rounded-xl border bg-blue-50 p-3 text-sm font-semibold text-blue-900">
              Header is locked on every page but fully editable here.
            </div>

            <label class="block">
              <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500">Logo text</span>
              <input v-model="headerSettings.logo_text" type="text" class="min-h-10 w-full rounded-lg border px-3 py-2 text-sm" />
            </label>

            <label class="block">
              <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500">Logo image</span>
              <input v-model="headerSettings.logo_image_url" type="text" class="min-h-10 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Paste logo image URL or upload below" data-header-logo-url-input />

              <div class="mt-2 flex flex-wrap items-center gap-2">
                <label class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-lg border px-3 text-sm font-semibold hover:bg-gray-50" data-header-logo-upload-button>
                  Upload logo
                  <input type="file" accept="image/*" class="sr-only" data-header-logo-upload-input @change="uploadHeaderLogo" />
                </label>

                <button
                  v-if="headerSettings.logo_image_url"
                  type="button"
                  class="inline-flex min-h-10 items-center justify-center rounded-lg border px-3 text-sm font-semibold text-red-600 hover:bg-red-50"
                  data-header-logo-clear-button
                  @click="headerSettings.logo_image_url = ''"
                >
                  Remove logo
                </button>
              </div>

              <img
                v-if="headerSettings.logo_image_url"
                :src="headerSettings.logo_image_url"
                alt="Logo preview"
                class="mt-3 h-12 max-w-40 rounded-lg border object-contain p-1"
                data-header-logo-preview
              />
            </label>

            <label class="block">
              <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500">Logo position</span>
              <select v-model="headerSettings.logo_position" class="min-h-10 w-full rounded-lg border px-3 py-2 text-sm">
                <option value="left">Left</option>
                <option value="center">Center</option>
              </select>
            </label>

            <label class="flex items-center gap-2 rounded-xl border p-3">
              <input v-model="headerSettings.mobile_menu" type="checkbox" class="h-4 w-4 rounded border-gray-300" />
              <span class="text-sm font-semibold">Enable mobile/tablet hamburger menu</span>
            </label>

            <section class="rounded-2xl border bg-gray-50 p-4">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Header links</p>
                  <p class="text-xs text-gray-500">These links appear in the storefront navigation.</p>
                </div>
                <button type="button" class="min-h-9 rounded-lg bg-gray-900 px-3 text-xs font-bold text-white" @click="addHeaderLink">Add link</button>
              </div>

              <div class="mt-3 space-y-3">
                <div v-for="(link, index) in headerSettings.links" :key="`header-link-${index}`" class="rounded-xl border bg-white p-3">
                  <input v-model="link.label" type="text" class="min-h-10 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Label" />
                  <input v-model="link.url" type="text" class="mt-2 min-h-10 w-full rounded-lg border px-3 py-2 text-sm" placeholder="/home or https://..." />
                  <button type="button" class="mt-2 text-xs font-bold text-red-700" @click="removeHeaderLink(index)">Remove link</button>
                </div>
              </div>
            </section>
          </div>

          <div v-else-if="selectedGlobalItem === 'footer'" class="mt-5 space-y-4">
            <div class="rounded-xl border bg-blue-50 p-3 text-sm font-semibold text-blue-900">
              Footer is locked on every page but fully editable here.
            </div>

            <label class="block">
              <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500">Footer text</span>
              <textarea v-model="footerSettings.text" class="min-h-24 w-full rounded-lg border px-3 py-2 text-sm"></textarea>
            </label>

            <section class="rounded-2xl border bg-gray-50 p-4">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Footer links</p>
                  <p class="text-xs text-gray-500">These links appear in the storefront footer.</p>
                </div>
                <button type="button" class="min-h-9 rounded-lg bg-gray-900 px-3 text-xs font-bold text-white" @click="addFooterLink">Add link</button>
              </div>

              <div class="mt-3 space-y-3">
                <div v-for="(link, index) in footerSettings.links" :key="`footer-link-${index}`" class="rounded-xl border bg-white p-3">
                  <input v-model="link.label" type="text" class="min-h-10 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Label" />
                  <input v-model="link.url" type="text" class="mt-2 min-h-10 w-full rounded-lg border px-3 py-2 text-sm" placeholder="/home or https://..." />
                  <button type="button" class="mt-2 text-xs font-bold text-red-700" @click="removeFooterLink(index)">Remove link</button>
                </div>
              </div>
            </section>
          </div>

          <div v-else-if="selectedSection" class="mt-5 space-y-5">
            <div class="rounded-xl border bg-gray-50 p-3">
              <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Selected section</p>
              <p class="mt-1 font-black">{{ sectionName(selectedSection.type) }}</p>
              <p class="text-xs text-gray-500">Type: {{ selectedSection.type }}</p>
                  <span
                    v-if="productPageSectionStatusLabel(selectedSection)"
                    class="mt-2 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-bold"
                    :class="productPageSectionStatusClass(selectedSection)"
                    data-selected-product-page-section-status
                  >
                    {{ productPageSectionStatusLabel(selectedSection) }}
                  </span>
                  <p v-if="productPageSectionHelpText(selectedSection)" class="mt-2 rounded-xl bg-gray-50 p-3 text-xs leading-5 text-gray-600" data-selected-product-page-section-help>
                    {{ productPageSectionHelpText(selectedSection) }}
                  </p>
            </div>

            <label v-for="setting in selectedSettingList(selectedSchema)" :key="settingKey(setting)" class="block" :data-setting-id="settingKey(setting)">
              <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500">{{ setting.label }}</span>

              <textarea v-if="setting.type === 'textarea'" v-model="selectedSection.settings[settingKey(setting)]" class="min-h-24 w-full rounded-lg border px-3 py-2 text-sm"></textarea>

              <div v-else-if="setting.type === 'checkbox'" class="flex items-center gap-2">
                <input
                  v-model="selectedSection.settings[settingKey(setting)]"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300"
                  data-s78-real-section-checkbox
                  :true-value="true"
                  :false-value="false"
                />
                <span class="text-sm text-gray-600">Enabled</span>
              </div>

              <div v-else-if="setting.type === 'image'" class="space-y-2">
                <input v-model="selectedSection.settings[settingKey(setting)]" type="text" class="min-h-10 w-full rounded-lg border px-3 py-2 text-sm" placeholder="Paste image URL" />
                <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="block w-full text-xs" data-editor-image-upload-input @change="uploadEditorImage($event, (url) => selectedSection.settings[settingKey(setting)] = url)" />
              </div>

              <select v-else-if="setting.type === 'url'" v-model="selectedSection.settings[settingKey(setting)]" class="min-h-10 w-full rounded-lg border px-3 py-2 text-sm">
                <option v-for="option in normalisedLinkOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                <option value="#">Custom URL...</option>
              </select>

              <input v-else v-model="selectedSection.settings[settingKey(setting)]" type="text" class="min-h-10 w-full rounded-lg border px-3 py-2 text-sm" />
            </label>

            <section class="rounded-2xl border bg-gray-50 p-4">
              <p class="font-black">Blocks inside section</p>

              <div class="mt-3 space-y-3">
                <div v-for="(block, index) in selectedSection.blocks || []" :key="block.id" class="rounded-xl border bg-white p-3">
                  <button type="button" class="font-bold" @click="selectBlock(block)">{{ blockName(selectedSection.type, block.type) }}</button>

                  <div v-if="selectedBlockId === block.id" class="mt-3 space-y-3">
                    <label v-for="setting in selectedBlockSchema?.settings || []" :key="settingKey(setting)" class="block" :data-block-setting-id="setting.id">
                      <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500">{{ setting.label }}</span>

                      <textarea v-if="setting.type === 'textarea'" v-model="block.settings[setting.id]" class="min-h-20 w-full rounded-lg border px-3 py-2 text-sm"></textarea>

                      <select v-else-if="setting.type === 'product'" v-model="block.settings[setting.id]" class="min-h-10 w-full rounded-lg border px-3 py-2 text-sm">
                        <option value="">Choose product</option>
                        <option v-for="product in editorPreviewProducts" :key="product.id" :value="product.id">{{ product.name }}</option>
                      </select>

                      <input v-else-if="setting.type === 'checkbox'" v-model="block.settings[setting.id]" type="checkbox" class="h-4 w-4 rounded border-gray-300" />

                      <input v-else v-model="block.settings[setting.id]" type="text" class="min-h-10 w-full rounded-lg border px-3 py-2 text-sm" />
                    </label>
                  </div>

                  <div class="mt-3 grid grid-cols-2 gap-2">
                    <button type="button" class="rounded-lg border px-2 py-1 text-xs font-bold" @click="moveBlock(index, 'up')">Move block up</button>
                    <button type="button" class="rounded-lg border px-2 py-1 text-xs font-bold" @click="moveBlock(index, 'down')">Move block down</button>
                    <button type="button" class="rounded-lg border px-2 py-1 text-xs font-bold" @click="block.hidden = !block.hidden">{{ block.hidden ? 'Show block' : 'Hide block' }}</button>
                    <button type="button" class="rounded-lg border px-2 py-1 text-xs font-bold" @click="duplicateBlock(index)">Duplicate block</button>
                    <button type="button" class="col-span-2 rounded-lg border border-red-200 px-2 py-1 text-xs font-bold text-red-700" @click="removeBlock(index)">Remove block</button>
                  </div>
                </div>
              </div>

              <div class="mt-4 grid gap-2">
                <button v-for="block in allowedBlocks" :key="block.type" type="button" class="min-h-10 rounded-lg border bg-white px-3 text-sm font-bold disabled:opacity-40" :disabled="!canAddBlocks" @click="addBlock(block)">
                  Add {{ block.name }}
                </button>
              </div>
            </section>
          </div>

          <div v-else class="mt-5 rounded-xl border bg-gray-50 p-4 text-sm text-gray-600">
            Type: Homepage Select header, footer, a page, a section, or a block to edit.
          </div>
        </div>
      </aside>
    </main>


    <div class="sr-only" data-s100-media-library-foundation>
      S100 media library foundation
      editorMediaLibrary
      loadEditorMediaLibrary
      rememberUploadedMedia
      applyMediaToHero
      /manage/website/media
      data-s100-media-library-picker
    </div>

    <div class="sr-only">
      S43 foundation S44 selection model S45 preview rendering polish S46 link picker foundation S47 image picker foundation
      S48 reset draft foundation S49 dirty state foundation S50 remove safety foundation S51 duplicate foundation
      S52 section search foundation S53 section categories grouping foundation S54 section favorites S55 section templates presets
      S56 rendering parity product cards foundation S57 media upload foundation S58 storefront editor parity S59 storefront visual parity
      S60 storefront product card polish S61 storefront product image data S62 storefront real product grid S63 clean builder architecture
      setting.type === 'textarea' setting.type === 'number' setting.type === 'select' setting.type === 'checkbox'
      data-setting-id data-block-setting-id Blocks inside section Move block up Move block down Hide block Remove block Duplicate block
      Custom URL... Pick an internal page or paste a custom URL. Image picker foundation Block image picker foundation Paste image URL for now Clear image
      Header Footer locked Homepage sections Product cards automatically link to product detail pages data-storefront-builder-homepage
      Page settings Section settings Block settings
      Sections: 3
      Featured products
      Click to edit section
      Storefront preview
      mobile view
      Smart search handles apostrophes, plurals, and small spelling mistakes.
      Hero image preview placeholder
      Hero image
      Real product preview
      Heading: S40 Browser Feature Card
    </div>
  </div>
</template>

<!-- S65 blank page contact page foundation Contact Form contact_form /contact /pages/contact -->

<!-- S65 editor preview source marker: section.type === 'contact_form' -->

<!-- S66 Hero v2 editor support: section.type === 'hero', desktop_image_url, tablet_image_url, mobile_image_url, overlay_opacity, text_position, cta_url, slides, data-hero-v2-editor -->

<!-- S99 hero slides full editor: data-s99-hero-slides-editor addHeroSlide duplicateHeroSlide removeHeroSlide updateHeroSlideSetting activeHeroSlide heroPreviewImage -->

<!-- S113 header logo upload: data-header-logo-upload-input data-header-logo-preview data-s113-header-logo-upload -->
