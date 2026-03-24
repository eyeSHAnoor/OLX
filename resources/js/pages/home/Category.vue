<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import OlxLayout from '@/layouts/OlxLayout.vue'
import AdCard from '@/components/AdCard.vue'
import AdListItem from '@/components/AdListItem.vue'
import debounce from 'lodash/debounce'
import citiesList from '@/data/cities.json'

// Props
const props = defineProps<{
    category?: any
    categories: any[]
    brands: any[]
    allBrands?: any[]
    filters: any
    priceRange?: { min: number; max: number }
}>()

const page = usePage()

// ----------------------
// BANNERS
// ----------------------
const BANNER_INTERVAL = 9
const BANNER_OFFSET = 8

const banners = computed(() => {
    const bannerData = (page.props as any).banners
    return Array.isArray(bannerData) ? bannerData : []
})

const topBanner = computed(() => {
    return (page.props as any).topBanner || null
})

// ----------------------
// STATE
// ----------------------
const viewMode = ref<'grid' | 'list'>('grid')
const showMobileFilters = ref(false)

// Filters
const searchTerm = ref(props.filters?.filter?.global || '')
const selectedBrands = ref<number[]>([])
const minPrice = ref<number | null>(props.filters?.filter?.min_price || null)
const maxPrice = ref<number | null>(props.filters?.filter?.max_price || null)
const selectedCity = ref(props.filters?.filter?.city || 'Pakistan')
const sortBy = ref(props.filters?.sort || 'newest')

// Categories UI
const showAllCategories = ref(false)
const initialCategoriesToShow = 5

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
        // For first load or filter change, replace all ads
        if (newData.current_page === 1) {
            allLoadedAds.value = [...newData.data]
        } else {
            // For subsequent pages, append new ads
            allLoadedAds.value = [...allLoadedAds.value, ...newData.data]
        }
        currentPage.value = newData.current_page
        totalPages.value = newData.last_page
        totalAds.value = newData.total
    }
}, { immediate: true, deep: true })

// Computed ads for display (all loaded ads)
const ads = computed(() => allLoadedAds.value)

const hasMorePages = computed(() => currentPage.value < totalPages.value)

// ----------------------
// INFINITE SCROLL
// ----------------------
const loading = ref(false)
const loadMoreTrigger = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

// Function to setup observer
const setupObserver = () => {
    if (observer) {
        observer.disconnect()
    }

    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !loading.value && hasMorePages.value) {
            loadMore()
        }
    }, { threshold: 0.1, rootMargin: '100px' })

    if (loadMoreTrigger.value) {
        observer.observe(loadMoreTrigger.value)
    }
}

// Check if we have ads
const hasAds = computed(() => {
    return ads.value && ads.value.length > 0
})

// ----------------------
// CATEGORIES
// ----------------------
const topLevelCategories = computed(() => {
    return props.categories.filter(cat => !cat.parent_id)
})

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

// ----------------------
// FILTER COUNT
// ----------------------
const activeFilterCount = computed(() => {
    let count = 0
    if (selectedBrands.value.length) count++
    if (minPrice.value !== null) count++
    if (maxPrice.value !== null) count++
    if (selectedCity.value !== 'Pakistan') count++
    return count
})

// ----------------------
// BANNERS LOGIC
// ----------------------
const shouldShowBanner = (index: number) => {
    if (!banners.value.length) return false

    const position = index + 1
    return position >= BANNER_OFFSET &&
        position % BANNER_INTERVAL === 0 &&
        index < ads.value.length - 1
}

const getBannerForPosition = (index: number) => {
    if (!banners.value.length) return null
    const bannerIndex = Math.floor(index / BANNER_INTERVAL) % banners.value.length
    return banners.value[bannerIndex]
}

// ----------------------
// ROUTER ACTIONS
// ----------------------
const applyFilters = () => {
    // Reset all loaded ads and pagination when filters change
    allLoadedAds.value = []
    currentPage.value = 1

    router.get(route('category.show', props.category?.slug), {
        filter: {
            global: searchTerm.value,
            category: props.category?.id,
            brand: selectedBrands.value.join(','),
            min_price: minPrice.value,
            max_price: maxPrice.value,
            city: selectedCity.value,
        },
        sort: sortBy.value,
        page: 1, // Reset to first page
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false
        }
    })

    showMobileFilters.value = false
}

const loadMore = () => {
    // Don't load if already loading or no more pages
    if (loading.value || !hasMorePages.value) return

    // Check if next page exists
    const nextPage = currentPage.value + 1
    if (nextPage > totalPages.value) {
        loading.value = false
        return
    }

    loading.value = true

    const params = {
        ...props.filters,
        page: nextPage
    }

    // Ensure filter parameters are properly structured
    if (params.filter) {
        params.filter = {
            ...params.filter,
            global: searchTerm.value,
            category: props.category?.id,
            brand: selectedBrands.value.join(','),
            min_price: minPrice.value,
            max_price: maxPrice.value,
            city: selectedCity.value,
        }
    }

    params.sort = sortBy.value

    // Use router.visit to preserve existing data
    router.visit(route('category.show', props.category?.slug), {
        method: 'get',
        data: params,
        preserveState: true,
        preserveScroll: true,
        only: ['category'], // Only update the category prop
        onSuccess: () => {
            loading.value = false
            // Re-setup observer after new content loads
            setTimeout(() => {
                setupObserver()
            }, 100)
        },
        onError: () => {
            loading.value = false
        }
    })
}

const goToPage = (page: number) => {
    if (page < 1 || page > totalPages.value) return

    // Reset loaded ads when manually changing page
    allLoadedAds.value = []
    currentPage.value = page

    router.get(route('category.show', props.category?.slug), {
        ...props.filters,
        page: page
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false
        }
    })
}

// ----------------------
// FILTER HANDLERS
// ----------------------
const debouncedApplyPriceFilter = debounce(applyFilters, 500)

const applyBrandFilter = () => applyFilters()
const applyCityFilter = () => applyFilters()

const applySort = () => {
    // Reset loaded ads when sorting changes
    allLoadedAds.value = []
    currentPage.value = 1

    router.get(route('category.show', props.category?.slug), {
        filter: {
            global: searchTerm.value,
            category: props.category?.id,
            brand: selectedBrands.value.join(','),
            min_price: minPrice.value,
            max_price: maxPrice.value,
            city: selectedCity.value,
        },
        sort: sortBy.value,
        page: 1,
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false
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

const resetCityFilter = () => {
    selectedCity.value = 'Pakistan'
    applyFilters()
}

const resetAllFilters = () => {
    selectedBrands.value = []
    minPrice.value = null
    maxPrice.value = null
    selectedCity.value = 'Pakistan'
    sortBy.value = 'newest'
    applyFilters()
}

// ----------------------
// HELPERS
// ----------------------
const getBrandAdCount = (brandId: number) => {
    return ads.value.filter((ad: any) => ad.brand_id === brandId).length
}

// ----------------------
// INIT
// ----------------------
onMounted(() => {
    if (props.filters?.filter?.brand) {
        selectedBrands.value = props.filters.filter.brand.split(',').map(Number)
    }

    // Setup infinite scroll observer after DOM is ready
    setTimeout(() => {
        setupObserver()
    }, 100)
})

// Watch for ads changes to re-setup observer
watch(ads, (newAds) => {
    // Re-setup observer after DOM updates if there are more pages
    if (hasMorePages.value && newAds.length > 0) {
        setTimeout(() => {
            setupObserver()
        }, 100)
    }
}, { deep: true })

onUnmounted(() => {
    if (observer) observer.disconnect()
})

const toggleCategoriesView = () => {
    showAllCategories.value = !showAllCategories.value
}

const priceRanges = [
    { label: 'Under $100', min: 0, max: 100 },
    { label: '$100 - $500', min: 100, max: 500 },
    { label: '$500 - $1000', min: 500, max: 1000 },
    { label: 'Above $1000', min: 1000, max: null }
]

const displayedPages = computed(() => {
    const pages = []
    for (let i = 1; i <= totalPages.value; i++) {
        pages.push(i)
    }
    return pages
})
</script>

<template>
    <OlxLayout>
        <TopCategoriesBar />
        <!-- Top Banner - Above everything -->
        <div v-if="topBanner" class="relative h-[300px] md:h-[400px] lg:h-[500px] overflow-hidden">
            <a :href="topBanner.link" target="_blank" rel="noopener noreferrer" class="block">
                <img :src="topBanner.image_url" :alt="topBanner.title"
                    class="w-full rounded-lg shadow-sm hover:shadow-md transition-shadow" />
            </a>
        </div>
        <div>
            <section class="max-w-9/11 mx-auto px-3 sm:px-4 py-4 md:py-6">

                <!-- Mobile Filter Toggle - Compact -->
                <div class="lg:hidden mb-3">
                    <button @click="showMobileFilters = !showMobileFilters"
                        class="w-full flex items-center justify-between bg-white p-3 rounded-lg shadow-sm border border-gray-200 hover:border-brand-teal transition-colors">
                        <span class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                            <Icon icon="mdi:filter-outline" class="text-lg" :style="{ color: 'var(--brand-teal)' }" />
                            Filters
                            <span v-if="activeFilterCount > 0"
                                class="ml-1.5 bg-brand-blue text-white text-[10px] px-1.5 py-0.5 rounded-full">
                                {{ activeFilterCount }}
                            </span>
                        </span>
                        <Icon icon="mdi:chevron-down" class="text-lg text-gray-500"
                            :class="{ 'rotate-180': showMobileFilters }" />
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-5">
                    <!-- Sidebar - Filters -->
                    <aside class="lg:col-span-1 space-y-4"
                        :class="showMobileFilters ? 'block mobile-filter-sidebar' : 'hidden lg:block'">

                        <!-- Close button for mobile -->
                        <div class="lg:hidden flex items-center justify-between mb-3">
                            <h2 class="font-semibold text-base">Filters</h2>
                            <button @click="showMobileFilters = false" class="p-1.5 hover:bg-gray-100 rounded-lg">
                                <Icon icon="mdi:close" class="text-xl" />
                            </button>
                        </div>

                        <!-- Categories Filter - Compact -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <div class="flex items-center gap-1.5 text-xs text-gray-600 pb-3 border-b border-gray-100">
                                <Link :href="route('home')" class="hover:text-brand-teal transition-colors">Home
                                </Link>
                                <Icon icon="mdi:chevron-right" class="text-gray-400 text-sm" />
                                <span v-if="category" class="text-gray-900 font-medium text-xs">{{ category.name
                                }}</span>
                                <span v-else class="text-gray-900 font-medium text-xs">All Categories</span>
                            </div>

                            <div class="mt-3">
                                <h3 class="font-medium text-sm text-gray-800 mb-3 flex items-center gap-1.5">
                                    <Icon icon="mdi:folder-outline" class="text-base"
                                        :style="{ color: 'var(--brand-teal)' }" />
                                    Categories
                                </h3>
                                <div class="space-y-0.5">
                                    <Link :href="route('category.show')"
                                        class="block text-xs py-1.5 px-2 rounded transition-all duration-200"
                                        :class="[!category ? 'bg-brand-blue/10 text-brand-blue font-medium border-l-2 border-brand-blue' : 'hover:bg-gray-50 hover:pl-3']">
                                        All Categories
                                    </Link>

                                    <!-- Display limited or all categories -->
                                    <template v-for="(cat, index) in displayedCategories" :key="cat.id">
                                        <div v-if="!cat.parent_id" class="space-y-0.5">
                                            <Link :href="route('category.show', cat.slug)"
                                                class="block text-xs py-1.5 px-2 rounded transition-all duration-200 font-medium"
                                                :class="[category?.id === cat.id ? 'bg-brand-blue/10 text-brand-blue border-l-2 border-brand-blue' : 'hover:bg-gray-50 hover:pl-3']">
                                                {{ cat.name }}
                                            </Link>

                                            <!-- Subcategories -->
                                            <div v-if="cat.children_recursive?.length" class="ml-3 space-y-0.5">
                                                <Link v-for="subCat in cat.children_recursive" :key="subCat.id"
                                                    :href="route('category.show', subCat.slug)"
                                                    class="block text-xs py-1 px-2 rounded transition-all duration-200"
                                                    :class="[category?.id === subCat.id ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900']">
                                                    {{ subCat.name }}
                                                </Link>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- View More / View Less button -->
                                    <button v-if="topLevelCategories.length > initialCategoriesToShow"
                                        @click="toggleCategoriesView"
                                        class="w-full mt-2 text-xs text-brand-teal hover:text-brand-teal/80 font-medium flex items-center justify-center gap-1 py-1.5 border-t border-gray-100">
                                        <Icon :icon="showAllCategories ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                                            class="text-sm" />
                                        {{ showAllCategories ? 'View Less' : `View ${topLevelCategories.length -
                                            initialCategoriesToShow} More` }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Brand Filter - Compact -->
                        <div class="bg-white rounded-lg shadow-sm p-4" v-if="brands.length">
                            <h3 class="font-medium text-sm text-gray-800 mb-3 flex items-center gap-1.5">
                                <Icon icon="mdi:tag-outline" class="text-base"
                                    :style="{ color: 'var(--brand-teal)' }" />
                                Brands
                            </h3>
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                <label v-for="brand in brands" :key="brand.id"
                                    class="flex items-center gap-2 p-1.5 rounded hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="checkbox" :value="brand.id" v-model="selectedBrands"
                                        @change="applyBrandFilter"
                                        class="w-3.5 h-3.5 rounded border-gray-300 text-brand-teal focus:ring-brand-teal">
                                    <span class="text-xs text-gray-700 flex-1">{{ brand.name }}</span>
                                    <span class="text-[10px] text-gray-500">{{ getBrandAdCount(brand.id) }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Price Filter - Compact -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h3 class="font-medium text-sm text-gray-800 mb-3 flex items-center gap-1.5">
                                <Icon icon="mdi:currency-usd" class="text-base"
                                    :style="{ color: 'var(--brand-teal)' }" />
                                Price Range
                            </h3>
                            <div class="space-y-3">
                                <!-- Price slider preview -->
                                <div class="flex items-center justify-between text-xs text-gray-600">
                                    <span>Min: ${{ minPrice || priceRange?.min || 0 }}</span>
                                    <span>Max: ${{ maxPrice || priceRange?.max || 10000 }}</span>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-0.5">Minimum</label>
                                        <input type="number" placeholder="Min" v-model.number="minPrice"
                                            @input="debouncedApplyPriceFilter"
                                            class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-gray-500 mb-0.5">Maximum</label>
                                        <input type="number" placeholder="Max" v-model.number="maxPrice"
                                            @input="debouncedApplyPriceFilter"
                                            class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs" />
                                    </div>
                                </div>

                                <!-- Quick price suggestions -->
                                <div class="flex flex-wrap gap-1.5">
                                    <button v-for="range in priceRanges" :key="range.label"
                                        @click="setQuickPriceRange(range.min, range.max)"
                                        class="text-[10px] px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                        {{ range.label }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Location Filter - Compact -->
                        <div class="bg-white rounded-lg shadow-sm p-4">
                            <h3 class="font-medium text-sm text-gray-800 mb-3 flex items-center gap-1.5">
                                <Icon icon="mdi:map-marker-outline" class="text-base"
                                    :style="{ color: 'var(--brand-teal)' }" />
                                Location
                            </h3>
                            <div class="relative">
                                <select v-model="selectedCity" @change="applyCityFilter"
                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none appearance-none bg-white text-xs">
                                    <option value="Pakistan">All Pakistan</option>
                                    <option v-for="city in cities" :key="city.lng" :value="city.name">{{ city.name }}
                                    </option>
                                </select>
                                <Icon icon="mdi:chevron-down"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm" />
                            </div>
                        </div>

                        <!-- Active Filters Summary - Compact -->
                        <div v-if="activeFilterCount > 0" class="bg-brand-blue/5 rounded-lg p-3">
                            <h4 class="text-xs font-medium text-gray-700 mb-2">Active Filters:</h4>
                            <div class="flex flex-wrap gap-1.5">
                                <span v-if="selectedBrands.length"
                                    class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                    {{ selectedBrands.length }} brands
                                    <button @click="clearBrandFilter" class="ml-0.5 hover:text-brand-teal">×</button>
                                </span>
                                <span v-if="minPrice || maxPrice"
                                    class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                    ${{ minPrice || 0 }} - ${{ maxPrice || '∞' }}
                                    <button @click="clearPriceFilter" class="ml-0.5 hover:text-brand-teal">×</button>
                                </span>
                                <span v-if="selectedCity !== 'Pakistan'"
                                    class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                    {{ selectedCity }}
                                    <button @click="resetCityFilter" class="ml-0.5 hover:text-brand-teal">×</button>
                                </span>
                            </div>

                            <button @click="resetAllFilters"
                                class="mt-2 text-xs text-brand-teal hover:text-brand-teal/80 font-medium">
                                Clear all filters
                            </button>
                        </div>

                        <!-- Mobile Filter Actions - Compact -->
                        <div
                            class="lg:hidden bg-white rounded-lg shadow-sm p-3 border-t border-gray-100 sticky bottom-0">
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="resetAllFilters"
                                    class="py-2 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50 transition-colors text-xs">
                                    Reset All
                                </button>
                                <button @click="applyFilters"
                                    class="py-2 bg-brand-blue text-white font-medium rounded hover:bg-brand-blue/90 transition-colors text-xs shadow-sm">
                                    Show Results
                                </button>
                            </div>
                        </div>
                    </aside>

                    <!-- Main Content - Compact -->
                    <main class="lg:col-span-2">
                        <!-- Header - Compact -->
                        <div class="mb-4 md:mb-5">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <h1 class="text-xl md:text-2xl font-semibold text-gray-900 mb-1">
                                        {{ category?.name || 'All Categories' }}
                                    </h1>
                                    <p class="text-gray-600 text-xs md:text-sm flex items-center gap-1.5">
                                        <span>{{ totalAds }} ads found</span>
                                        <span v-if="selectedCity !== 'Pakistan'" class="text-brand-teal">• in {{
                                            selectedCity }}</span>
                                        <span v-if="allLoadedAds.length > 0" class="text-brand-teal">• Showing {{
                                            allLoadedAds.length }} of
                                            {{ totalAds }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Toolbar - Compact -->
                        <div class="bg-white rounded-lg shadow-sm p-3 mb-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center justify-between sm:justify-start">
                                    <div class="flex items-center space-x-1">
                                        <button @click="viewMode = 'grid'"
                                            :class="viewMode === 'grid' ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-400 hover:text-gray-600'"
                                            class="p-1.5 rounded transition-all duration-200">
                                            <Icon icon="mdi:grid-large" class="w-4 h-4" />
                                        </button>
                                        <button @click="viewMode = 'list'"
                                            :class="viewMode === 'list' ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-400 hover:text-gray-600'"
                                            class="p-1.5 rounded transition-all duration-200">
                                            <Icon icon="mdi:format-list-bulleted" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Sort Dropdown -->
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-600 text-xs hidden sm:block">Sort:</span>
                                    <select v-model="sortBy" @change="applySort"
                                        class="border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs min-w-[140px]">
                                        <option value="newest">Newest First</option>
                                        <option value="price_low">Price: Low to High</option>
                                        <option value="price_high">Price: High to Low</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Results Grid/List with Banners -->
                        <template v-if="hasAds">
                            <!-- Grid View with Banners -->
                            <div v-if="viewMode === 'grid'">
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-3 md:gap-4">
                                    <template v-for="(ad, index) in ads" :key="ad.id">
                                        <!-- Ad Card -->
                                        <AdCard :ad="ad" />

                                        <!-- Insert mid banners after every 9-10 ads -->
                                        <div v-if="shouldShowBanner(index)" :key="'banner-' + index"
                                            class="col-span-1 sm:col-span-2 lg:col-span-2 xl:col-span-3 my-2">
                                            <a :href="getBannerForPosition(index).link" target="_blank"
                                                rel="noopener noreferrer" class="block">
                                                <img :src="getBannerForPosition(index).image_url"
                                                    :alt="getBannerForPosition(index).title"
                                                    class="w-full rounded-lg shadow-sm hover:shadow-md transition-shadow" />
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- List View with Banners -->
                            <div v-if="viewMode === 'list'" class="space-y-2 md:space-y-3">
                                <template v-for="(ad, index) in ads" :key="ad.id">
                                    <!-- Ad List Item -->
                                    <AdListItem :ad="ad" />

                                    <!-- Insert mid banners after every 9-10 ads -->
                                    <div v-if="shouldShowBanner(index)" :key="'banner-' + index" class="my-2">
                                        <a :href="getBannerForPosition(index).link" target="_blank"
                                            rel="noopener noreferrer" class="block">
                                            <img :src="getBannerForPosition(index).image_url"
                                                :alt="getBannerForPosition(index).title"
                                                class="w-full rounded-lg shadow-sm hover:shadow-md transition-shadow" />
                                        </a>
                                    </div>
                                </template>
                            </div>

                            <!-- Loading indicator for infinite scroll -->
                            <div v-if="loading" class="text-center py-4">
                                <Icon icon="mdi:loading" class="animate-spin text-2xl text-brand-teal mx-auto" />
                                <p class="text-xs text-gray-500 mt-2">Loading more ads...</p>
                            </div>

                            <!-- Infinite scroll trigger - only show if there are more pages -->
                            <div ref="loadMoreTrigger" v-if="hasMorePages && !loading && hasAds" class="h-10"></div>

                            <!-- No more items message - only show if we have ads and no more pages -->
                            <div v-if="!hasMorePages && hasAds && allLoadedAds.length === totalAds"
                                class="text-center py-4">
                                <p class="text-xs text-gray-400">You've seen all {{ totalAds }} ads</p>
                            </div>
                        </template>

                        <!-- No Results - Compact -->
                        <div v-else class="text-center py-8 md:py-10 bg-white rounded-lg shadow-sm">
                            <div class="max-w-md mx-auto px-4">
                                <Icon icon="mdi:package-variant-closed" class="text-4xl text-gray-300 mx-auto mb-3" />
                                <h3 class="text-lg md:text-xl font-semibold text-gray-900 mb-2">No ads found</h3>
                                <p class="text-gray-600 text-xs md:text-sm mb-4">
                                    Try adjusting your filters or browse other categories
                                </p>
                                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                                    <Link :href="route('home')"
                                        class="px-4 py-2 bg-brand-blue text-white font-medium rounded hover:bg-brand-blue/90 transition-colors text-xs shadow-sm">
                                        Go To Home
                                    </Link>
                                    <Link :href="route('home')"
                                        class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50 transition-colors text-xs">
                                        Browse All
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </section>
        </div>
    </OlxLayout>
</template>