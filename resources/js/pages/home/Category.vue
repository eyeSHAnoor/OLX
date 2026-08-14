<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import OlxLayout from '@/layouts/OlxLayout.vue'
import AdCard from '@/components/AdCard.vue'
import AdListItem from '@/components/AdListItem.vue'
import debounce from 'lodash/debounce'
import citiesList from '@/data/cities.json'
import TopCategoriesBar from '@/components/TopCategoriesBar.vue'
import { useTheme } from '@/Composables/useTheme'
// Shadcn/ui components for modal
import { Button } from '@/components/ui/button'
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

// Theme
const { theme } = useTheme()

// Props
const props = defineProps<{
    category?: any
    categories: any[]
    brands: any[]
    attributes?: any[]
    filters: any
    priceRange?: { min: number; max: number }
}>()

const page = usePage()
// ----------------------
// BANNERS (new logic)
// ----------------------
const allBanners = computed(() => (page.props as any).banners || [])

// Banners that are generic (target_category_id = null) – shown as top carousel
const genericBanners = computed(() =>
    allBanners.value.filter(b => b.target_category_id === null)
)

// Banners that target the current category – shown between ads
const categoryBanners = computed(() =>
    allBanners.value.filter(b => b.target_category_id === props.category?.id)
)

// Carousel state
const currentSlide = ref(0)
let slideInterval: ReturnType<typeof setInterval> | null = null

const nextSlide = () => {
    if (genericBanners.value.length > 1) {
        currentSlide.value = (currentSlide.value + 1) % genericBanners.value.length
    }
}
const prevSlide = () => {
    if (genericBanners.value.length > 1) {
        currentSlide.value = (currentSlide.value - 1 + genericBanners.value.length) % genericBanners.value.length
    }
}

// Auto‑rotate every 5 seconds
const startAutoRotate = () => {
    if (slideInterval) clearInterval(slideInterval)
    if (genericBanners.value.length > 1) {
        slideInterval = setInterval(nextSlide, 5000)
    }
}
const stopAutoRotate = () => {
    if (slideInterval) {
        clearInterval(slideInterval)
        slideInterval = null
    }
}

// Inline banner placement: after every 5 ads, or after the last ad if total ≤ 5
const BANNER_POSITION_INTERVAL = 9

const shouldShowInlineBanner = (index: number, totalAds: number) => {
    if (!categoryBanners.value.length) return false
    if (totalAds <= BANNER_POSITION_INTERVAL) {
        // Show only after the last ad
        return index === totalAds - 1
    }
    // Show after every N ads, but not after the last one (to avoid double banner after last)
    return (index + 1) % BANNER_POSITION_INTERVAL === 0 && index < totalAds - 1
}

const getInlineBanner = (position: number) => {
    const banners = categoryBanners.value
    if (!banners.length) return null
    // Cycle through available banners
    const bannerIndex = position % banners.length
    return banners[bannerIndex]
}

// ----------------------
// STATE
// ----------------------
const viewMode = ref<'grid' | 'list'>('grid')
const showMobileFilters = ref(false)
const isLoading = ref(false)

// Filters
const searchTerm = ref(props.filters?.filter?.global || '')
const selectedBrands = ref<number[]>([])
const selectedModels = ref<number[]>([])
const selectedMobileCategory = ref<number | null>(props.category?.id || null)
const minPrice = ref<number | null>(props.filters?.filter?.min_price || null)
const maxPrice = ref<number | null>(props.filters?.filter?.max_price || null)
const selectedCity = ref(
    (() => {
        const value = props.filters?.filter?.city || 'all'
        if (typeof value === 'string' && value.toLowerCase() === 'pakistan') return 'all'
        return value
    })()
)
const sortBy = ref(props.filters?.sort || 'newest')
const attributeFilters = ref<Record<string, any>>({})

// Categories UI (accordion)
const showAllCategories = ref(false)
const initialCategoriesToShow = 5
const expandedCategories = ref<Set<number>>(new Set())

// Toggle category expansion
const toggleCategory = (catId: number) => {
    if (expandedCategories.value.has(catId)) {
        expandedCategories.value.delete(catId)
    } else {
        expandedCategories.value.add(catId)
    }
}

// Attribute modal state – using Dialog's v-model:open
const showAttributeModal = ref(false)
const localAttributeFilters = ref<Record<string, any>>({})

const openAttributeModal = () => {
    // Deep copy current attribute filters
    localAttributeFilters.value = JSON.parse(JSON.stringify(attributeFilters.value))
    // Ensure every filterable attribute has an array as value
    if (props.attributes) {
        props.attributes.filter(attr => attr.is_filterable).forEach(attr => {
            const key = `attribute_${attr.id}`
            if (!Array.isArray(localAttributeFilters.value[key])) {
                localAttributeFilters.value[key] = []
            }
        })
    }
    showAttributeModal.value = true
}

const applyAttributeModal = () => {
    // Copy local changes back to main attributeFilters
    attributeFilters.value = JSON.parse(JSON.stringify(localAttributeFilters.value))
    applyFilters()
    showAttributeModal.value = false
}

const resetAttributeModal = () => {
    localAttributeFilters.value = {}
    // Re-initialize all filterable attributes as empty arrays
    if (props.attributes) {
        props.attributes.filter(attr => attr.is_filterable).forEach(attr => {
            const key = `attribute_${attr.id}`
            localAttributeFilters.value[key] = []
        })
    }
}

const cities = ref<string[]>(citiesList || [])

// ----------------------
// PAGINATION (FROM BACKEND)
// ----------------------
const adsData = computed(() => props.category?.ads || null)

// Store all loaded ads (accumulated from all pages)
const allLoadedAds = ref<any[]>([])
const currentPage = ref(1)
const totalPages = ref(1)
const totalAds = ref(0)

// Update all loaded ads when initial data arrives
watch(adsData, (newData) => {
    if (newData) {
        if (newData.current_page === 1) {
            allLoadedAds.value = [...newData.data]
        } else {
            allLoadedAds.value = [...allLoadedAds.value, ...newData.data]
        }
        currentPage.value = newData.current_page
        totalPages.value = newData.last_page
        totalAds.value = newData.total
        isLoading.value = false
    }
}, { immediate: true, deep: true })

const ads = computed(() => allLoadedAds.value)
const hasMorePages = computed(() => currentPage.value < totalPages.value)

// ----------------------
// INFINITE SCROLL
// ----------------------
const loadingMore = ref(false)
const loadMoreTrigger = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

const setupObserver = () => {
    if (observer) observer.disconnect()
    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !loadingMore.value && hasMorePages.value) {
            loadMore()
        }
    }, { threshold: 0.1, rootMargin: '100px' })
    if (loadMoreTrigger.value) observer.observe(loadMoreTrigger.value)
}

const hasAds = computed(() => ads.value && ads.value.length > 0)

const selectedCityLabel = computed(() => selectedCity.value === 'all' ? 'Pakistan' : selectedCity.value)

// ----------------------
// CATEGORIES
// ----------------------
const topLevelCategories = computed(() => props.categories.filter(cat => !cat.parent_id))

const displayedCategories = computed(() => {
    if (showAllCategories.value) return props.categories
    return props.categories.filter((cat) => {
        if (cat.parent_id) {
            const parentIndex = topLevelCategories.value.findIndex(p => p.id === cat.parent_id)
            return parentIndex < initialCategoriesToShow
        }
        const parentIndex = topLevelCategories.value.findIndex(p => p.id === cat.id)
        return parentIndex < initialCategoriesToShow
    })
})

const mobileCategoryGroups = computed(() => {
    const groups: any[] = []
    topLevelCategories.value.forEach(parent => {
        const group: any = {
            label: parent.name,
            options: [{ id: parent.id, name: parent.name }]
        }
        if (parent.children_recursive?.length) {
            parent.children_recursive.forEach(child => {
                group.options.push({ id: child.id, name: '└ ' + child.name })
            })
        }
        groups.push(group)
    })
    return groups
})

// ----------------------
// FILTER COUNT
// ----------------------
const activeFilterCount = computed(() => {
    let count = 0
    if (selectedBrands.value.length) count++
    if (selectedModels.value.length) count++
    if (minPrice.value !== null && minPrice.value > 0) count++
    if (maxPrice.value !== null && maxPrice.value > 0) count++
    if (selectedCity.value !== 'all') count++
    Object.values(attributeFilters.value).forEach(value => {
        if (value && (Array.isArray(value) ? value.length > 0 : true)) count++
    })
    return count
})

// ----------------------
// ROUTER ACTIONS
// ----------------------
const goBack = () => window.history.back()

const findCategoryById = (id: number) => {
    const searchCategories = (cats: any[]): any => {
        for (const cat of cats) {
            if (cat.id === id) return cat
            if (cat.children_recursive?.length) {
                const found = searchCategories(cat.children_recursive)
                if (found) return found
            }
        }
        return null
    }
    return searchCategories(props.categories)
}

const applyFilters = () => {
    allLoadedAds.value = []
    currentPage.value = 1
    isLoading.value = true

    const processedAttributeFilters: Record<string, any> = {}
    Object.entries(attributeFilters.value).forEach(([key, value]) => {
        if (Array.isArray(value) && value.length > 0) {
            processedAttributeFilters[key] = value.join(',')
        } else if (value && !Array.isArray(value)) {
            processedAttributeFilters[key] = value
        }
    })

    let targetRoute = route('category.show')
    if (selectedMobileCategory.value !== null) {
        const selectedCategory = findCategoryById(selectedMobileCategory.value)
        if (selectedCategory) targetRoute = route('category.show', selectedCategory.slug)
    } else if (props.category?.slug) {
        targetRoute = route('category.show', props.category.slug)
    }

    router.get(targetRoute, {
        filter: {
            global: searchTerm.value,
            category: selectedMobileCategory.value,
            brand: selectedBrands.value.join(','),
            model: selectedModels.value.join(','),
            min_price: minPrice.value,
            max_price: maxPrice.value,
            city: selectedCity.value,
            ...processedAttributeFilters,
        },
        sort: sortBy.value,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false
            loadingMore.value = false
        },
        onError: () => {
            isLoading.value = false
            loadingMore.value = false
        }
    })
}

const loadMore = () => {
    if (loadingMore.value || !hasMorePages.value) return
    const nextPage = currentPage.value + 1
    if (nextPage > totalPages.value) {
        loadingMore.value = false
        return
    }
    loadingMore.value = true

    const params: any = { ...props.filters, page: nextPage }
    if (params.filter) {
        const processedAttributeFilters: Record<string, any> = {}
        Object.entries(attributeFilters.value).forEach(([key, value]) => {
            if (Array.isArray(value) && value.length > 0) {
                processedAttributeFilters[key] = value.join(',')
            } else if (value && !Array.isArray(value)) {
                processedAttributeFilters[key] = value
            }
        })
        params.filter = {
            ...params.filter,
            global: searchTerm.value,
            category: props.category?.id,
            brand: selectedBrands.value.join(','),
            model: selectedModels.value.join(','),
            min_price: minPrice.value,
            max_price: maxPrice.value,
            city: selectedCity.value,
            ...processedAttributeFilters,
        }
    }
    params.sort = sortBy.value

    router.visit(route('category.show', props.category?.slug), {
        method: 'get',
        data: params,
        preserveState: true,
        preserveScroll: true,
        only: ['category'],
        onSuccess: () => {
            loadingMore.value = false
            setTimeout(setupObserver, 100)
        },
        onError: () => {
            loadingMore.value = false
        }
    })
}

// ----------------------
// FILTER HANDLERS
// ----------------------
const debouncedApplyPriceFilter = debounce(() => applyFilters(), 500)
const applyBrandFilter = () => applyFilters()
const applyCityFilter = () => applyFilters()

const applySort = () => {
    allLoadedAds.value = []
    currentPage.value = 1
    isLoading.value = true

    const processedAttributeFilters: Record<string, any> = {}
    Object.entries(attributeFilters.value).forEach(([key, value]) => {
        if (Array.isArray(value) && value.length > 0) {
            processedAttributeFilters[key] = value.join(',')
        } else if (value && !Array.isArray(value)) {
            processedAttributeFilters[key] = value
        }
    })

    const targetCategoryId = selectedMobileCategory.value !== null ? selectedMobileCategory.value : props.category?.id
    const targetCategorySlug = (selectedMobileCategory.value !== null && selectedMobileCategory.value !== props.category?.id)
        ? null
        : (props.category?.slug || null)

    router.get(targetCategorySlug ? route('category.show', targetCategorySlug) : route('category.show'), {
        filter: {
            global: searchTerm.value,
            category: targetCategoryId,
            brand: selectedBrands.value.join(','),
            model: selectedModels.value.join(','),
            min_price: minPrice.value,
            max_price: maxPrice.value,
            city: selectedCity.value,
            ...processedAttributeFilters,
        },
        sort: sortBy.value,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false
            loadingMore.value = false
        },
        onError: () => {
            isLoading.value = false
            loadingMore.value = false
        }
    })
}

const setQuickPriceRange = (min: number | null, max: number | null) => {
    minPrice.value = min
    maxPrice.value = max
    applyFilters()
}
const clearPriceFilter = () => {
    minPrice.value = null
    maxPrice.value = null
    applyFilters()
}
const clearBrandFilter = () => {
    selectedBrands.value = []
    applyFilters()
}
const clearModelFilter = () => {
    selectedModels.value = []
    applyFilters()
}
const resetCityFilter = () => {
    selectedCity.value = 'all'
    applyFilters()
}
const resetAllFilters = () => {
    selectedBrands.value = []
    selectedModels.value = []
    minPrice.value = null
    maxPrice.value = null
    selectedCity.value = 'all'
    sortBy.value = 'newest'
    attributeFilters.value = {}
    applyFilters()
}

const getBrandAdCount = (brandId: number) => ads.value.filter((ad: any) => ad.brand_id === brandId).length

// Helper for attribute checkboxes (used only in mobile)
const ensureAttributeArray = (attrId: number) => {
    const key = `attribute_${attrId}`
    if (!Array.isArray(attributeFilters.value[key])) {
        attributeFilters.value[key] = []
    }
}

// ----------------------
// INIT & CLEANUP
// ----------------------
onMounted(() => {
    selectedMobileCategory.value = props.category?.id || null
    if (props.filters?.filter?.brand) {
        selectedBrands.value = props.filters.filter.brand.split(',').map(Number)
    }
    if (props.filters?.filter?.model) {
        selectedModels.value = props.filters.filter.model.split(',').map(Number)
    }
    if (props.filters?.attributeFilters) {
        attributeFilters.value = {}
        Object.entries(props.filters.attributeFilters).forEach(([key, value]) => {
            if (typeof value === 'string' && value.includes(',')) {
                attributeFilters.value[key] = value.split(',')
            } else {
                attributeFilters.value[key] = value
            }
        })
    }
    setTimeout(setupObserver, 100)
    startAutoRotate()
})

watch(() => props.category?.id, (newCategoryId) => {
    selectedMobileCategory.value = newCategoryId || null
})

watch(ads, (newAds) => {
    if (hasMorePages.value && newAds.length > 0) {
        setTimeout(setupObserver, 100)
    }
}, { deep: true })

watch(showMobileFilters, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
})

onUnmounted(() => {
    if (observer) observer.disconnect()
    document.body.style.overflow = ''
    stopAutoRotate()
})

const toggleCategoriesView = () => {
    showAllCategories.value = !showAllCategories.value
}

const priceRanges = [
    { label: 'Under 100', min: 0, max: 100 },
    { label: '100 - 500', min: 100, max: 500 },
    { label: '500 - 1000', min: 500, max: 1000 },
    { label: 'Above 1000', min: 1000, max: null }
]
</script>

<template>
    <OlxLayout>
        <TopCategoriesBar />

        <!-- Top Carousel for Generic Banners -->
        <section v-if="genericBanners.length" class="relative h-[300px] md:h-[400px] lg:h-[500px] overflow-hidden"
            :class="theme.bgLight">
            <div v-for="(banner, index) in genericBanners" :key="banner.id"
                class="absolute inset-0 transition-opacity duration-700"
                :class="{ 'opacity-100 z-10': currentSlide === index, 'opacity-0': currentSlide !== index }">
                <a :href="banner.link || '#'" :target="banner.link ? '_blank' : '_self'" class="block w-full h-full">
                    <img :src="banner.image_url" :alt="banner.title" class="w-full h-full object-contain" />
                </a>
            </div>

            <!-- Navigation Buttons -->
            <button v-if="genericBanners.length > 1" @click="prevSlide"
                class="absolute left-4 top-1/2 -translate-y-1/2 z-20 rounded-full p-3 shadow-md transition"
                :class="theme.card">
                <Icon icon="mdi:chevron-left" class="text-2xl" :class="theme.text" />
            </button>
            <button v-if="genericBanners.length > 1" @click="nextSlide"
                class="absolute right-4 top-1/2 -translate-y-1/2 z-20 rounded-full p-3 shadow-md transition"
                :class="theme.card">
                <Icon icon="mdi:chevron-right" class="text-2xl" :class="theme.text" />
            </button>

            <!-- Dots -->
            <div v-if="genericBanners.length > 1" class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                <button v-for="(_, idx) in genericBanners" :key="idx" @click="currentSlide = idx"
                    class="h-2 rounded-full transition-all"
                    :class="currentSlide === idx ? 'w-8 bg-yellow-500' : 'w-2 bg-white/70'">
                </button>
            </div>
        </section>

        <div :class="theme.bg">
            <section class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3 sm:px-4 py-4 md:py-6">
                <div class="py-3">
                    <button @click="goBack"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-md border text-sm transition"
                        :class="[theme.card, theme.border, theme.text, theme.hover]">
                        <Icon icon="mdi:arrow-left" class="text-base" />
                        Back
                    </button>
                </div>

                <!-- Mobile Filter Toggle -->
                <div class="lg:hidden mb-3">
                    <button @click="showMobileFilters = true"
                        class="w-full flex items-center justify-between p-3 rounded-lg shadow-sm border"
                        :class="[theme.card, theme.border]">
                        <span class="flex items-center gap-2 text-sm font-medium" :class="theme.text">
                            <Icon icon="mdi:filter-outline" class="text-lg" :class="theme.icon" />
                            Filters
                            <span v-if="activeFilterCount > 0"
                                class="bg-brand-blue text-white text-xs px-2 py-0.5 rounded-full">
                                {{ activeFilterCount }}
                            </span>
                        </span>
                        <Icon icon="mdi:chevron-right" class="text-lg" :class="theme.textMuted" />
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-5">
                    <!-- Sidebar Filters (Desktop) -->
                    <aside class="lg:col-span-1 space-y-4 hidden lg:block">
                        <!-- Categories Filter (Accordion) -->
                        <div class="rounded-lg shadow-sm p-4" :class="theme.card">
                            <div class="flex items-center gap-1.5 text-xs pb-3 border-b"
                                :class="[theme.textMuted, theme.border]">
                                <Link :href="route('home')" class="hover:text-brand-teal">Home</Link>
                                <Icon icon="mdi:chevron-right" class="text-gray-400 text-sm" />
                                <span v-if="category" class="font-medium text-xs" :class="theme.text">{{ category.name
                                }}</span>
                                <span v-else class="font-medium text-xs" :class="theme.text">All Categories</span>
                            </div>
                            <div class="mt-3">
                                <h3 class="font-medium text-sm mb-3 flex items-center gap-1.5" :class="theme.text">
                                    <Icon icon="mdi:folder-outline" class="text-base" :class="theme.icon" />
                                    Categories
                                </h3>
                                <div class="space-y-1">
                                    <Link :href="route('category.show')"
                                        class="block text-xs py-1.5 px-2 rounded transition-all duration-200"
                                        :class="[!category ? 'bg-brand-blue/10 text-brand-blue font-medium border-l-2 border-brand-blue' : theme.hover]">
                                        All Categories
                                    </Link>

                                    <!-- Parent categories only (filtered by showAllCategories) -->
                                    <div v-for="parent in topLevelCategories" :key="parent.id"
                                        v-show="topLevelCategories.indexOf(parent) < (showAllCategories ? Infinity : initialCategoriesToShow)">
                                        <div class="flex items-center justify-between group">
                                            <Link :href="route('category.show', parent.slug)"
                                                class="block flex-1 text-xs py-1.5 px-2 rounded transition-all duration-200"
                                                :class="[category?.id === parent.id ? 'bg-brand-blue/10 text-brand-blue font-medium border-l-2 border-brand-blue' : theme.hover, theme.text]">
                                                {{ parent.name }}
                                            </Link>
                                            <button v-if="parent.children_recursive?.length"
                                                @click="toggleCategory(parent.id)" class="p-1 mr-1 rounded transition"
                                                :class="theme.hover">
                                                <Icon
                                                    :icon="expandedCategories.has(parent.id) ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                                                    class="text-sm" :class="theme.textMuted" />
                                            </button>
                                        </div>

                                        <!-- Child categories (visible only if expanded) -->
                                        <div v-if="parent.children_recursive?.length && expandedCategories.has(parent.id)"
                                            class="ml-4 space-y-0.5 mt-0.5 border-l pl-2" :class="theme.border">
                                            <Link v-for="subCat in parent.children_recursive" :key="subCat.id"
                                                :href="route('category.show', subCat.slug)"
                                                class="block text-xs py-1 px-2 rounded transition-all duration-200"
                                                :class="[category?.id === subCat.id ? 'bg-brand-blue/10 text-brand-blue' : theme.hover, theme.textMuted]">
                                                {{ subCat.name }}
                                            </Link>
                                        </div>
                                    </div>

                                    <!-- View More / View Less button -->
                                    <button v-if="topLevelCategories.length > initialCategoriesToShow"
                                        @click="toggleCategoriesView"
                                        class="w-full mt-2 text-xs font-medium flex items-center justify-center gap-1 py-1.5 border-t"
                                        :class="[theme.border, theme.textAccent]">
                                        <Icon :icon="showAllCategories ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                                            class="text-sm" />
                                        {{ showAllCategories ? 'View Less' : `View ${topLevelCategories.length -
                                            initialCategoriesToShow} More` }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Brand Filter -->
                        <div class="rounded-lg shadow-sm p-4" :class="theme.card" v-if="brands.length">
                            <h3 class="font-medium text-sm mb-3 flex items-center gap-1.5" :class="theme.text">
                                <Icon icon="mdi:tag-outline" class="text-base" :class="theme.icon" />
                                Brands
                            </h3>
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                <label v-for="brand in brands" :key="brand.id"
                                    class="flex items-center gap-2 p-1.5 rounded cursor-pointer" :class="theme.hover">
                                    <input type="checkbox" :value="brand.id" v-model="selectedBrands"
                                        @change="applyBrandFilter"
                                        class="w-3.5 h-3.5 rounded border-gray-300 text-brand-teal focus:ring-brand-teal">
                                    <span class="text-xs flex-1" :class="theme.text">{{ brand.name }}</span>
                                    <span class="text-[10px]" :class="theme.textMuted">{{ getBrandAdCount(brand.id)
                                    }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Model Filter -->
                        <div class="rounded-lg shadow-sm p-4" :class="theme.card"
                            v-if="brands.some(b => b.models?.length)">
                            <h3 class="font-medium text-sm mb-3 flex items-center gap-1.5" :class="theme.text">
                                <Icon icon="mdi:car-outline" class="text-base" :class="theme.icon" />
                                Models
                            </h3>
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                <template v-for="brand in brands" :key="brand.id">
                                    <div v-if="brand.models?.length">
                                        <div class="text-xs font-medium mb-1" :class="theme.textMuted">{{ brand.name }}
                                        </div>
                                        <div class="ml-2 space-y-0.5">
                                            <label v-for="model in brand.models" :key="model.id"
                                                class="flex items-center gap-2 p-1 rounded cursor-pointer"
                                                :class="theme.hover">
                                                <input type="checkbox" :value="model.id" v-model="selectedModels"
                                                    @change="applyFilters"
                                                    class="w-3 h-3 rounded border-gray-300 text-brand-teal focus:ring-brand-teal">
                                                <span class="text-xs flex-1" :class="theme.text">{{ model.name }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Price Filter -->
                        <div class="rounded-lg shadow-sm p-4" :class="theme.card">
                            <h3 class="font-medium text-sm mb-3 flex items-center gap-1.5" :class="theme.text">
                                <Icon icon="mdi:currency-usd" class="text-base" :class="theme.icon" />
                                Price Range
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between text-xs" :class="theme.textMuted">
                                    <span>Min: {{ minPrice || priceRange?.min || 0 }}</span>
                                    <span>Max: {{ maxPrice || priceRange?.max || 10000 }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] mb-0.5" :class="theme.textMuted">Minimum</label>
                                        <input type="number" placeholder="Min" v-model.number="minPrice"
                                            @input="debouncedApplyPriceFilter"
                                            class="w-full px-2 py-1.5 border rounded focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none text-xs"
                                            :class="theme.input" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] mb-0.5" :class="theme.textMuted">Maximum</label>
                                        <input type="number" placeholder="Max" v-model.number="maxPrice"
                                            @input="debouncedApplyPriceFilter"
                                            class="w-full px-2 py-1.5 border rounded focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none text-xs"
                                            :class="theme.input" />
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <button v-for="range in priceRanges" :key="range.label"
                                        @click="setQuickPriceRange(range.min, range.max)"
                                        class="text-[10px] px-2 py-1 rounded-full transition"
                                        :class="[theme.badge, theme.hover]">
                                        {{ range.label }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Location Filter -->
                        <div class="rounded-lg shadow-sm p-4" :class="theme.card">
                            <h3 class="font-medium text-sm mb-3 flex items-center gap-1.5" :class="theme.text">
                                <Icon icon="mdi:map-marker-outline" class="text-base" :class="theme.icon" />
                                Location
                            </h3>
                            <div class="relative">
                                <SelectInput v-model="selectedCity" @update:modelValue="applyCityFilter"
                                    placeholder="Select City">
                                    <SelectContent>
                                        <SelectItem v-for="city in [
                                            { label: 'All Pakistan', value: 'all' },
                                            ...cities.map(c => ({ label: c.name, value: c.name }))
                                        ]" :key="city.value" :value="city.value">
                                            {{ city.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </SelectInput>
                                <Icon icon="mdi:chevron-down" class="absolute right-2 top-1/2 -translate-y-1/2 text-sm"
                                    :class="theme.textMuted" />
                            </div>
                        </div>

                        <!-- More Filters Button (opens attribute modal) -->
                        <div class="rounded-lg shadow-sm p-4" :class="theme.card">
                            <button @click="openAttributeModal"
                                class="w-full flex items-center justify-center gap-2 font-medium py-2.5 rounded-lg transition"
                                :class="theme.button">
                                <Icon icon="mdi:filter-variant" class="text-lg" />
                                More Filters
                                <span
                                    v-if="Object.values(attributeFilters).some(v => v && (Array.isArray(v) ? v.length : true))"
                                    class="ml-1 text-xs px-1.5 py-0.5 rounded-full" :class="theme.badge">
                                    {{Object.values(attributeFilters).filter(v => v && (Array.isArray(v) ? v.length :
                                        true)).length}}
                                </span>
                            </button>
                        </div>

                        <!-- Active Filters Summary -->
                        <div v-if="activeFilterCount > 0" class="rounded-lg p-3" :class="theme.card">
                            <h4 class="text-xs font-medium mb-2" :class="theme.text">Active Filters:</h4>
                            <div class="flex flex-wrap gap-1.5">
                                <span v-if="selectedBrands.length"
                                    class="inline-flex items-center gap-1 text-[10px] px-2 py-1 rounded-full shadow-sm"
                                    :class="theme.badge">
                                    {{ selectedBrands.length }} brands <button @click="clearBrandFilter"
                                        class="ml-0.5 hover:text-brand-teal">×</button>
                                </span>
                                <span v-if="selectedModels.length"
                                    class="inline-flex items-center gap-1 text-[10px] px-2 py-1 rounded-full shadow-sm"
                                    :class="theme.badge">
                                    {{ selectedModels.length }} models <button @click="clearModelFilter"
                                        class="ml-0.5 hover:text-brand-teal">×</button>
                                </span>
                                <span v-if="minPrice || maxPrice"
                                    class="inline-flex items-center gap-1 text-[10px] px-2 py-1 rounded-full shadow-sm"
                                    :class="theme.badge">
                                    {{ minPrice || 0 }} - {{ maxPrice || '∞' }} <button @click="clearPriceFilter"
                                        class="ml-0.5 hover:text-brand-teal">×</button>
                                </span>
                                <span v-if="selectedCity !== 'all'"
                                    class="inline-flex items-center gap-1 text-[10px] px-2 py-1 rounded-full shadow-sm"
                                    :class="theme.badge">
                                    {{ selectedCityLabel }} <button @click="resetCityFilter"
                                        class="ml-0.5 hover:text-brand-teal">×</button>
                                </span>
                                <template v-for="(value, key) in attributeFilters" :key="key">
                                    <span v-if="value && (Array.isArray(value) ? value.length > 0 : true)"
                                        class="inline-flex items-center gap-1 text-[10px] px-2 py-1 rounded-full shadow-sm"
                                        :class="theme.badge">
                                        {{attributes?.find(attr => `attribute_${attr.id}` === key)?.name ||
                                            key.replace('attribute_', '')}}
                                        <button
                                            @click="attributeFilters[key] = Array.isArray(value) ? [] : ''; applyFilters()"
                                            class="ml-0.5 hover:text-brand-teal">×</button>
                                    </span>
                                </template>
                            </div>
                            <button @click="resetAllFilters" class="mt-2 text-xs font-medium"
                                :class="theme.textAccent">Clear all
                                filters</button>
                        </div>
                    </aside>

                    <!-- Main Content -->
                    <main class="lg:col-span-2">
                        <!-- Header -->
                        <div class="mb-4 md:mb-5">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <h1 class="text-xl md:text-2xl font-semibold mb-1" :class="theme.text">
                                        {{ category?.name || 'All Categories' }}</h1>
                                    <p class="text-xs md:text-sm flex items-center gap-1.5" :class="theme.textMuted">
                                        <span>{{ totalAds }} ads found</span>
                                        <span v-if="selectedCity !== 'all'" :class="theme.textAccent">• in {{
                                            selectedCityLabel }}</span>
                                        <span v-if="allLoadedAds.length > 0" :class="theme.textAccent">• Showing {{
                                            allLoadedAds.length }}
                                            of {{ totalAds }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Toolbar -->
                        <div class="rounded-lg shadow-sm p-3 mb-4" :class="theme.card">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center justify-between sm:justify-start">
                                    <div class="flex items-center space-x-1">
                                        <button @click="viewMode = 'grid'"
                                            :class="viewMode === 'grid' ? 'bg-brand-blue/10 text-brand-blue' : theme.textMuted"
                                            class="p-1.5 rounded transition">
                                            <Icon icon="mdi:grid-large" class="w-4 h-4" />
                                        </button>
                                        <button @click="viewMode = 'list'"
                                            :class="viewMode === 'list' ? 'bg-brand-blue/10 text-brand-blue' : theme.textMuted"
                                            class="p-1.5 rounded transition">
                                            <Icon icon="mdi:format-list-bulleted" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs hidden sm:block" :class="theme.textMuted">Sort:</span>
                                    <SelectInput v-model="sortBy" @update:modelValue="applySort" placeholder="Sort By"
                                        class="min-w-[140px]">
                                        <SelectContent>
                                            <SelectItem value="newest">Newest First</SelectItem>
                                            <SelectItem value="price_low">Price: Low to High</SelectItem>
                                            <SelectItem value="price_high">Price: High to Low</SelectItem>
                                        </SelectContent>
                                    </SelectInput>
                                </div>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div v-if="isLoading && !hasAds" class="text-center py-12">
                            <svg class="animate-spin w-10 h-10 mx-auto mb-3" :class="theme.icon" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <p class="text-sm" :class="theme.textMuted">Loading ads...</p>
                        </div>

                        <!-- Results with Inline Banners -->
                        <template v-else-if="hasAds">
                            <!-- Grid View -->
                            <div v-if="viewMode === 'grid'">
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-3 md:gap-4">
                                    <template v-for="(ad, idx) in ads" :key="ad.id">
                                        <AdCard :ad="ad" />
                                        <!-- Insert category banner after interval -->
                                        <div v-if="shouldShowInlineBanner(idx, ads.length)"
                                            :key="'inline-banner-' + idx"
                                            class="col-span-1 sm:col-span-2 lg:col-span-2 xl:col-span-3 my-2">
                                            <a :href="getInlineBanner(idx)?.link" target="_blank"
                                                rel="noopener noreferrer" class="block">
                                                <img :src="getInlineBanner(idx)?.image_url"
                                                    :alt="getInlineBanner(idx)?.title"
                                                    class="w-full rounded-lg shadow-sm hover:shadow-md transition-shadow" />
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- List View -->
                            <div v-if="viewMode === 'list'" class="space-y-2 md:space-y-3">
                                <template v-for="(ad, idx) in ads" :key="ad.id">
                                    <AdListItem :ad="ad" />
                                    <div v-if="shouldShowInlineBanner(idx, ads.length)" :key="'inline-banner-' + idx"
                                        class="my-2">
                                        <a :href="getInlineBanner(idx)?.link" target="_blank" rel="noopener noreferrer"
                                            class="block">
                                            <img :src="getInlineBanner(idx)?.image_url"
                                                :alt="getInlineBanner(idx)?.title"
                                                class="w-full rounded-lg shadow-sm hover:shadow-md transition-shadow" />
                                        </a>
                                    </div>
                                </template>
                            </div>

                            <!-- Infinite Scroll Loading -->
                            <div v-if="loadingMore" class="text-center py-4">
                                <Icon icon="mdi:loading" class="animate-spin text-2xl mx-auto" :class="theme.icon" />
                                <p class="text-xs mt-2" :class="theme.textMuted">Loading more ads...</p>
                            </div>
                            <div ref="loadMoreTrigger" v-if="hasMorePages && !loadingMore && hasAds" class="h-10"></div>
                            <div v-if="!hasMorePages && hasAds && allLoadedAds.length === totalAds"
                                class="text-center py-4">
                                <p class="text-xs" :class="theme.textMuted">You've seen all {{ totalAds }} ads</p>
                            </div>
                        </template>

                        <!-- No Results -->
                        <div v-else-if="!isLoading && !hasAds" class="text-center py-8 md:py-10 rounded-lg shadow-sm"
                            :class="theme.card">
                            <div class="max-w-md mx-auto px-4">
                                <Icon icon="mdi:package-variant-closed" class="text-4xl mx-auto mb-3"
                                    :class="theme.textMuted" />
                                <h3 class="text-lg md:text-xl font-semibold mb-2" :class="theme.text">No ads found</h3>
                                <p class="text-xs md:text-sm mb-4" :class="theme.textMuted">Try adjusting your filters
                                    or browse other
                                    categories</p>
                                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                                    <Link :href="route('home')" class="px-4 py-2 font-medium rounded text-xs shadow-sm"
                                        :class="theme.button">
                                        Go To Home</Link>
                                    <Link :href="route('home')" class="px-4 py-2 border font-medium rounded text-xs"
                                        :class="[theme.buttonOutline, theme.border]">
                                        Browse All</Link>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </section>
        </div>

        <!-- Mobile Filters -->
        <div v-if="showMobileFilters" class="fixed inset-0 z-50 overflow-y-auto lg:hidden" :class="theme.bg">
            <div class="sticky top-0 border-b" :class="[theme.card, theme.border]">
                <div class="flex items-center justify-between p-4">
                    <button @click="showMobileFilters = false" class="p-1 -ml-1">
                        <Icon icon="mdi:arrow-left" class="text-2xl" :class="theme.text" />
                    </button>
                    <h2 class="font-semibold text-lg" :class="theme.text">Filters</h2>
                    <button @click="resetAllFilters" class="text-sm font-medium px-2 py-1"
                        :class="theme.textAccent">Reset
                        all</button>
                </div>
            </div>
            <div class="divide-y pb-24" :class="theme.border">
                <!-- Mobile filter sections with theme classes -->
                <div class="p-4">
                    <p class="text-sm font-semibold mb-3" :class="theme.text">Category</p>
                    <SelectInput v-model="selectedMobileCategory"
                        @update:modelValue="() => { selectedBrands = []; selectedModels = []; applyFilters() }"
                        placeholder="All Categories">
                        <SelectContent>
                            <SelectItem :value="null">All Categories</SelectItem>
                            <template v-for="group in mobileCategoryGroups" :key="group.label">
                                <div class="px-2 py-1 text-xs font-semibold" :class="theme.textMuted">{{ group.label }}
                                </div>
                                <SelectItem v-for="cat in group.options" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </SelectItem>
                            </template>
                        </SelectContent>
                    </SelectInput>
                </div>
                <div class="p-4" v-if="brands.length">
                    <p class="text-sm font-semibold mb-3" :class="theme.text">Brand</p>
                    <div class="space-y-1 max-h-48 overflow-y-auto">
                        <label v-for="brand in brands" :key="brand.id" class="flex items-center gap-2 p-1.5 rounded"
                            :class="theme.hover">
                            <input type="checkbox" :value="brand.id" v-model="selectedBrands" @change="applyBrandFilter"
                                class="w-3.5 h-3.5 rounded border-gray-300 text-brand-teal">
                            <span class="text-xs flex-1" :class="theme.text">{{ brand.name }}</span>
                            <span class="text-[10px]" :class="theme.textMuted">{{ getBrandAdCount(brand.id) }}</span>
                        </label>
                    </div>
                </div>
                <div class="p-4" v-if="brands.some(b => b.models?.length)">
                    <p class="text-sm font-semibold mb-3" :class="theme.text">Model</p>
                    <div class="space-y-1 max-h-48 overflow-y-auto">
                        <template v-for="brand in brands" :key="brand.id">
                            <div v-if="brand.models?.length">
                                <div class="text-xs font-medium mb-1" :class="theme.textMuted">{{ brand.name }}</div>
                                <div class="ml-2 space-y-0.5">
                                    <label v-for="model in brand.models" :key="model.id"
                                        class="flex items-center gap-2 p-1 rounded" :class="theme.hover">
                                        <input type="checkbox" :value="model.id" v-model="selectedModels"
                                            @change="applyFilters"
                                            class="w-3 h-3 rounded border-gray-300 text-brand-teal">
                                        <span class="text-xs flex-1" :class="theme.text">{{ model.name }}</span>
                                    </label>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-sm font-semibold mb-3" :class="theme.text">Location</p>
                    <SelectInput v-model="selectedCity" @update:modelValue="applyCityFilter" placeholder="Select City">
                        <SelectContent>
                            <SelectItem v-for="city in [
                                { label: 'All Pakistan', value: 'all' },
                                ...cities.map(c => ({ label: c.name, value: c.name }))
                            ]" :key="city.value" :value="city.value">
                                {{ city.label }}
                            </SelectItem>
                        </SelectContent>
                    </SelectInput>
                </div>
                <div class="p-4">
                    <p class="text-sm font-semibold mb-3" :class="theme.text">Price Range</p>
                    <div class="flex gap-3 mb-4">
                        <div class="flex-1">
                            <label class="block text-xs mb-1" :class="theme.textMuted">Min (Pkr)</label>
                            <input type="number" placeholder="Min" v-model.number="minPrice"
                                @input="debouncedApplyPriceFilter"
                                class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-brand-teal focus:border-transparent"
                                :class="theme.input" />
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs mb-1" :class="theme.textMuted">Max (Pkr)</label>
                            <input type="number" placeholder="Max" v-model.number="maxPrice"
                                @input="debouncedApplyPriceFilter"
                                class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-brand-teal focus:border-transparent"
                                :class="theme.input" />
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button v-for="range in priceRanges" :key="range.label"
                            @click="setQuickPriceRange(range.min, range.max)"
                            class="px-3 py-1.5 text-xs rounded-full transition" :class="[theme.badge, theme.hover]">
                            {{ range.label }}
                        </button>
                    </div>
                </div>
                <div v-if="attributes?.filter(attr => attr.is_filterable).length" class="p-4">
                    <div v-for="attribute in (attributes?.filter(attr => attr.is_filterable) || [])" :key="attribute.id"
                        class="mb-6 last:mb-0">
                        <p class="text-sm font-semibold mb-3" :class="theme.text">{{ attribute.name }}</p>
                        <div v-if="attribute.type === 'select' && attribute.options?.length"
                            class="flex flex-wrap gap-2">
                            <button v-for="option in attribute.options" :key="option.id" @click="() => {
                                ensureAttributeArray(attribute.id)
                                const key = `attribute_${attribute.id}`
                                const arr = attributeFilters[key]
                                if (arr.includes(option.id)) attributeFilters[key] = arr.filter((v: number) => v !== option.id)
                                else attributeFilters[key].push(option.id)
                                applyFilters()
                            }" :class="[
                                'px-4 py-2 rounded-full text-sm font-medium transition-all',
                                attributeFilters[`attribute_${attribute.id}`]?.includes(option.id)
                                    ? 'bg-brand-teal text-white shadow-sm'
                                    : theme.badge
                            ]">
                                {{ option.value }}
                            </button>
                        </div>
                        <div v-else-if="attribute.type === 'text'">
                            <input type="text" :placeholder="`Enter ${attribute.name.toLowerCase()}`"
                                v-model="attributeFilters[`attribute_${attribute.id}`]" @input="applyFilters"
                                class="w-full border rounded-lg p-2 text-sm focus:ring-2 focus:ring-brand-teal focus:border-transparent"
                                :class="theme.input" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="fixed bottom-0 left-0 right-0 border-t p-4 shadow-lg" :class="[theme.card, theme.border]">
                <button @click="showMobileFilters = false"
                    class="w-full py-3 rounded-lg font-semibold text-base shadow-md transition" :class="theme.button">
                    Show {{ totalAds }} results
                </button>
            </div>
        </div>

        <!-- Attribute Filters Modal (Desktop) - Using shadcn/ui Dialog -->
        <Dialog v-model:open="showAttributeModal">
            <DialogContent class="flex max-h-[90vh] w-full max-w-[90vw] flex-col sm:max-w-[700px]">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-lg font-semibold sm:text-xl">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-teal/10">
                            <Icon icon="mdi:filter-variant" class="h-4 w-4 text-brand-teal" />
                        </div>
                        <span>More Filters</span>
                    </DialogTitle>
                    <p class="text-sm text-muted-foreground mt-1">Narrow down your results by selecting specifications
                    </p>
                </DialogHeader>

                <!-- Scrollable content -->
                <div class="flex-1 overflow-y-auto py-2 px-2 pr-3 space-y-5 max-h-[60vh]">
                    <div v-for="attribute in attributes?.filter(attr => attr.is_filterable)" :key="attribute.id">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ attribute.name }}</label>

                        <!-- Select / checkbox type -->
                        <div v-if="attribute.type === 'select' && attribute.options?.length" class="space-y-2">
                            <div v-for="option in attribute.options" :key="option.id" class="flex items-center">
                                <input type="checkbox" :id="`modal_attr_${attribute.id}_${option.id}`"
                                    :value="option.id" v-model="localAttributeFilters[`attribute_${attribute.id}`]"
                                    class="h-4 w-4 rounded border-gray-300 text-brand-teal focus:ring-brand-teal" />
                                <label :for="`modal_attr_${attribute.id}_${option.id}`"
                                    class="ml-2 text-sm text-gray-700">
                                    {{ option.value }}
                                </label>
                            </div>
                        </div>

                        <!-- Text input type -->
                        <div v-else-if="attribute.type === 'text'">
                            <input type="text" :placeholder="`Enter ${attribute.name.toLowerCase()}`"
                                v-model="localAttributeFilters[`attribute_${attribute.id}`]"
                                class="w-full border border-gray-300 rounded-md p-2 text-sm focus:ring-brand-teal focus:border-brand-teal" />
                        </div>
                    </div>

                    <div v-if="!attributes?.filter(attr => attr.is_filterable).length"
                        class="text-center text-gray-500 py-6">
                        No additional filters available.
                    </div>
                </div>

                <DialogFooter class="flex flex-col-reverse gap-2 border-t px-6 sm:flex-row sm:justify-end pt-4">
                    <Button @click="resetAttributeModal" variant="outline" class="sm:w-auto w-full">
                        Reset
                    </Button>
                    <Button @click="applyAttributeModal"
                        class="w-full bg-brand-teal text-white hover:bg-brand-teal/90 sm:w-auto">
                        Apply Filters
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </OlxLayout>
</template>

<style>
.fixed.inset-0.bg-white {
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }

    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>