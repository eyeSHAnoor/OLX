<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import OlxLayout from '@/layouts/OlxLayout.vue'
import AdCard from '@/components/AdCard.vue'
import AdListItem from '@/components/AdListItem.vue'
import debounce from 'lodash/debounce'
import citiesList from '@/data/cities.json'

// Props definition
const props = defineProps<{
    category?: any
    categories: any[]
    brands: any[]
    allBrands?: any[]
    filters: any
    priceRange?: { min: number; max: number }
}>()

// Get page props including banners
const page = usePage()

// Banner display configuration
const BANNER_INTERVAL = 9 // Show banner after every 10 ads
const BANNER_OFFSET = 8     // Start after 9th ad (index 8)

// Get banners from page props
const banners = computed(() => {
    const bannerData = (page.props as any).banners
    return Array.isArray(bannerData) ? bannerData : []
})

const topBanner = computed(() => {
    return (page.props as any).topBanner || null
})

// View state
const viewMode = ref<'grid' | 'list'>('grid')
const showMobileFilters = ref(false)
const currentPage = ref(1)
const itemsPerPage = 12

// Filter states
const searchTerm = ref(props.filters?.filter?.global || '')
const selectedBrands = ref<number[]>([])
const minPrice = ref<number | null>(props.filters?.filter?.min_price || null)
const maxPrice = ref<number | null>(props.filters?.filter?.max_price || null)
const selectedCity = ref(props.filters?.filter?.city || 'Pakistan')
const sortBy = ref(props.filters?.sort || 'newest')

// Categories view more/less state
const showAllCategories = ref(false)
const initialCategoriesToShow = 5

// Cities list
const cities = ref<string[]>(citiesList || [])

// Quick price ranges
const priceRanges = [
    { label: 'Under $100', min: 0, max: 100 },
    { label: '$100 - $500', min: 100, max: 500 },
    { label: '$500 - $1000', min: 500, max: 1000 },
    { label: '$1000+', min: 1000, max: null }
]

// Computed properties
const topLevelCategories = computed(() => {
    return props.categories.filter(cat => !cat.parent_id)
})

const displayedCategories = computed(() => {
    if (showAllCategories.value) {
        return props.categories
    }
    return props.categories.filter((cat, index) => {
        // Always show all subcategories of visible parent categories
        if (cat.parent_id) {
            const parentIndex = topLevelCategories.value.findIndex(parent => parent.id === cat.parent_id)
            return parentIndex < initialCategoriesToShow
        }
        // Show only first 5 parent categories
        const parentIndex = topLevelCategories.value.findIndex(parent => parent.id === cat.id)
        return parentIndex < initialCategoriesToShow
    })
})

const activeFilterCount = computed(() => {
    let count = 0
    if (selectedBrands.value.length) count++
    if (minPrice.value !== null) count++
    if (maxPrice.value !== null) count++
    if (selectedCity.value !== 'Pakistan') count++
    return count
})

const filteredAds = computed(() => {
    let ads = props.category?.ads || []

    // Filter by selected brands
    if (selectedBrands.value.length) {
        ads = ads.filter(ad => selectedBrands.value.includes(ad.brand_id))
    }

    // Filter by price
    if (minPrice.value !== null) {
        ads = ads.filter(ad => ad.price >= minPrice.value!)
    }
    if (maxPrice.value !== null) {
        ads = ads.filter(ad => ad.price <= maxPrice.value!)
    }

    // Apply sorting
    if (sortBy.value === 'price_low') {
        ads = [...ads].sort((a, b) => a.price - b.price)
    } else if (sortBy.value === 'price_high') {
        ads = [...ads].sort((a, b) => b.price - a.price)
    } else {
        ads = [...ads].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
    }

    return ads
})

const paginatedAds = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    const end = start + itemsPerPage
    return filteredAds.value.slice(start, end)
})

const totalPages = computed(() =>
    Math.ceil(filteredAds.value.length / itemsPerPage)
)

const displayedPages = computed(() => {
    const delta = 2
    const range = []
    const rangeWithDots = []
    let l

    for (let i = 1; i <= totalPages.value; i++) {
        if (i === 1 || i === totalPages.value || (i >= currentPage.value - delta && i <= currentPage.value + delta)) {
            range.push(i)
        }
    }

    range.forEach((i) => {
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1)
            } else if (i - l !== 1) {
                rangeWithDots.push('...')
            }
        }
        rangeWithDots.push(i)
        l = i
    })

    return rangeWithDots
})

// Banner methods
const shouldShowBanner = (index: number) => {
    if (!banners.value || banners.value.length === 0) return false

    // Show banner after every BANNER_INTERVAL ads, starting from BANNER_OFFSET
    const position = index + 1 // Convert to 1-based position
    const isAtInterval = position >= BANNER_OFFSET && position % BANNER_INTERVAL === 0
    const notAtEnd = index < paginatedAds.value.length - 1 // Don't show at last item

    return isAtInterval && notAtEnd
}

const getBannerForPosition = (index: number) => {
    if (!banners.value || banners.value.length === 0) return null

    // Calculate which banner to show based on the position
    // This rotates through available banners
    const bannerIndex = Math.floor(index / BANNER_INTERVAL) % banners.value.length
    return banners.value[bannerIndex]
}

// Methods
const toggleCategoriesView = () => {
    showAllCategories.value = !showAllCategories.value
}

const getBrandAdCount = (brandId: number) => {
    return props.category?.ads?.filter((ad: any) => ad.brand_id === brandId).length || 0
}

const applyFilters = () => {
    const filters: any = {
        filter: {
            global: searchTerm.value,
            category: props.category?.id,
            brand: selectedBrands.value.join(','),
            min_price: minPrice.value,
            max_price: maxPrice.value,
            city: selectedCity.value,
        },
        sort: sortBy.value,
    }

    router.get(route('category.show', props.category?.slug), filters, {
        preserveState: true,
        preserveScroll: true,
    })

    showMobileFilters.value = false
}

const debouncedApplyPriceFilter = debounce(() => {
    applyFilters()
}, 500)

const applyBrandFilter = () => {
    applyFilters()
}

const applyCityFilter = () => {
    applyFilters()
}

const applySort = () => {
    currentPage.value = 1 // Reset to first page when sorting
    applyFilters()
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

// Watch for page changes
watch(currentPage, () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
})

// Watch for filter changes to reset pagination
watch([selectedBrands, minPrice, maxPrice, selectedCity, sortBy], () => {
    currentPage.value = 1
})

// Initialize selected brands from URL if present
onMounted(() => {
    if (props.filters?.filter?.brand) {
        selectedBrands.value = props.filters.filter.brand.split(',').map(Number)
    }
    console.log('Banners loaded:', banners.value)
    console.log('Top banner:', topBanner.value)
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
                                    <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
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
                                        <span>{{ filteredAds.length }} ads found</span>
                                        <span v-if="selectedCity !== 'Pakistan'" class="text-brand-teal">• in {{
                                            selectedCity }}</span>
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
                        <template v-if="filteredAds.length">
                            <!-- Grid View with Banners -->
                            <div v-if="viewMode === 'grid'">
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-3 md:gap-4">
                                    <template v-for="(ad, index) in paginatedAds" :key="ad.id">
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
                                <template v-for="(ad, index) in paginatedAds" :key="ad.id">
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

                            <!-- Pagination - Compact -->
                            <div v-if="totalPages > 1" class="mt-6 flex justify-center">
                                <div class="flex items-center gap-1.5">
                                    <button @click="currentPage--" :disabled="currentPage === 1"
                                        class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <Icon icon="mdi:chevron-left" class="text-sm" />
                                    </button>

                                    <button v-for="page in displayedPages" :key="page" @click="currentPage = page"
                                        class="w-8 h-8 rounded text-xs transition-colors"
                                        :class="[currentPage === page ? 'bg-brand-blue text-white' : 'hover:bg-gray-50']">
                                        {{ page }}
                                    </button>

                                    <button @click="currentPage++" :disabled="currentPage === totalPages"
                                        class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <Icon icon="mdi:chevron-right" class="text-sm" />
                                    </button>
                                </div>
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
                                    <button @click="resetAllFilters"
                                        class="px-4 py-2 bg-brand-blue text-white font-medium rounded hover:bg-brand-blue/90 transition-colors text-xs shadow-sm">
                                        Clear Filters
                                    </button>
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