<template>
    <OlxLayout>
        <section class="max-w-9/11 mx-auto px-3 sm:px-4 py-4 md:py-6">

            <!-- Mobile Filter Toggle - Compact -->
            <div class="lg:hidden mb-3">
                <button @click="showMobileFilters = !showMobileFilters"
                    class="w-full flex items-center justify-between bg-white p-3 rounded-lg shadow-sm border border-gray-200 hover:border-brand-teal transition-colors">
                    <span class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            :style="{ color: 'var(--brand-teal)' }">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filters
                        <span v-if="activeFilterCount > 0"
                            class="ml-1.5 bg-brand-blue text-white text-[10px] px-1.5 py-0.5 rounded-full">
                            {{ activeFilterCount }}
                        </span>
                    </span>
                    <svg class="w-4 h-4 text-gray-500 transition-transform" :class="{ 'rotate-180': showMobileFilters }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-5">

                <!-- Sidebar - Compact -->
                <aside class="lg:col-span-1 space-y-4"
                    :class="showMobileFilters ? 'block mobile-filter-sidebar' : 'hidden lg:block'">

                    <!-- Close button for mobile -->
                    <div class="lg:hidden flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-base">Filters</h2>
                        <button @click="showMobileFilters = false" class="p-1.5 hover:bg-gray-100 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Category Filter - Compact -->
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-medium text-sm text-gray-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    :style="{ color: 'var(--brand-teal)' }">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                Categories
                            </h3>
                            <button @click="showAllCategories = !showAllCategories" v-if="categories?.length > 5"
                                class="text-xs text-brand-teal hover:text-brand-teal/80">
                                {{ showAllCategories ? 'Show Less' : 'Show All' }}
                            </button>
                        </div>
                        <div class="space-y-0.5 max-h-60 overflow-y-auto">
                            <div v-for="(category, index) in categories" :key="category.id"
                                @click="selectCategory(category)" :class="[
                                    'text-xs cursor-pointer py-1.5 px-2 rounded transition-all duration-200',
                                    'hover:bg-brand-teal/5 hover:text-brand-teal hover:pl-3',
                                    'border-l-2 transition-colors',
                                    selectedCategoryId === category.id
                                        ? 'bg-brand-blue/10 text-brand-blue border-brand-blue'
                                        : 'border-transparent hover:border-brand-teal',
                                    index >= 5 && !showAllCategories ? 'hidden' : 'block'
                                ]">
                                {{ category.name }}
                                <span class="text-[10px] text-gray-400 ml-1">({{ getCategoryAdCount(category) }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Price Filter - Compact -->
                    <div class="bg-white rounded-lg shadow-sm p-4">
                        <h3 class="font-medium text-sm text-gray-800 mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                :style="{ color: 'var(--brand-teal)' }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Price Range
                        </h3>
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] text-gray-500 mb-0.5">Min ($)</label>
                                    <input type="number" placeholder="Min" v-model.number="minPrice"
                                        @input="debouncedApplyFilters"
                                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs" />
                                </div>
                                <div>
                                    <label class="block text-[10px] text-gray-500 mb-0.5">Max ($)</label>
                                    <input type="number" placeholder="Max" v-model.number="maxPrice"
                                        @input="debouncedApplyFilters"
                                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs" />
                                </div>
                            </div>

                            <!-- Quick price suggestions - Compact -->
                            <div class="flex flex-wrap gap-1.5">
                                <button v-for="range in priceRanges" :key="range.label"
                                    @click="setQuickPriceRange(range.min, range.max)"
                                    class="text-[10px] px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                    {{ range.label }}
                                </button>
                            </div>

                            <button @click="applyFilters"
                                class="w-full bg-brand-blue hover:bg-brand-blue/90 text-white font-medium py-2 rounded transition-all duration-200 shadow-sm hover:shadow text-xs">
                                Apply Filter
                            </button>
                        </div>
                    </div>

                    <!-- Brand Filter - Compact -->
                    <div class="bg-white rounded-lg shadow-sm p-4" v-if="brands?.length">
                        <h3 class="font-medium text-sm text-gray-800 mb-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                :style="{ color: 'var(--brand-teal)' }">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 4h2a2 2 0 012 2v2M16 4h-2a2 2 0 00-2 2v2m4-4v2a2 2 0 01-2 2h-2m4 8h2a2 2 0 002-2v-2m-2 0h-2a2 2 0 00-2 2v2m-4-4v2a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2h6a2 2 0 012 2z" />
                            </svg>
                            Brands
                        </h3>
                        <div class="space-y-1 max-h-60 overflow-y-auto">
                            <div v-for="brand in filteredBrands" :key="brand.id" @click="toggleBrand(brand.id)" :class="[
                                'flex items-center justify-between cursor-pointer py-1.5 px-2 rounded transition-colors text-xs',
                                'hover:bg-brand-teal/5',
                                selectedBrands.includes(brand.id) ? 'bg-brand-blue/10 text-brand-blue' : ''
                            ]">
                                <span>{{ brand.name }}</span>
                                <span class="text-[10px] text-gray-400">{{ getBrandAdCount(brand.id) }}</span>
                            </div>
                        </div>

                        <!-- Show all brands toggle -->
                        <button v-if="brands.length > 10" @click="showAllBrands = !showAllBrands"
                            class="mt-2 text-xs text-brand-teal hover:text-brand-teal/80 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    :d="showAllBrands ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                            </svg>
                            {{ showAllBrands ? 'Show Less' : `Show All (${brands.length})` }}
                        </button>
                    </div>

                    <!-- Active Filters Summary - Compact -->
                    <div v-if="activeFilterCount > 0" class="bg-brand-blue/5 rounded-lg p-3">
                        <h4 class="text-xs font-medium text-gray-700 mb-2">Active Filters:</h4>
                        <div class="flex flex-wrap gap-1.5">
                            <span v-if="selectedCategoryId"
                                class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                {{ selectedCategoryName }}
                                <button @click="clearCategoryFilter" class="ml-0.5 hover:text-brand-teal">×</button>
                            </span>
                            <span v-if="minPrice || maxPrice"
                                class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                ${{ minPrice || 0 }} - ${{ maxPrice || '∞' }}
                                <button @click="clearPriceFilter" class="ml-0.5 hover:text-brand-teal">×</button>
                            </span>
                            <span v-if="selectedBrands.length"
                                class="inline-flex items-center gap-1 bg-white text-[10px] px-2 py-1 rounded-full shadow-sm">
                                {{ selectedBrands.length }} {{ selectedBrands.length === 1 ? 'brand' : 'brands' }}
                                <button @click="clearBrandFilter" class="ml-0.5 hover:text-brand-teal">×</button>
                            </span>
                        </div>

                        <button @click="resetFilters"
                            class="mt-2 text-xs text-brand-teal hover:text-brand-teal/80 font-medium">
                            Clear all filters
                        </button>
                    </div>

                    <!-- Mobile Filter Actions - Compact -->
                    <div class="lg:hidden bg-white rounded-lg shadow-sm p-3 border-t border-gray-100 sticky bottom-0">
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="resetFilters"
                                class="py-2 border border-gray-300 text-gray-700 font-medium rounded hover:bg-gray-50 transition-colors text-xs">
                                Reset All
                            </button>
                            <button @click="showMobileFilters = false"
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
                                    All Items
                                </h1>
                                <p class="text-gray-600 text-xs md:text-sm">
                                    {{ totalAds }} ads found
                                    <span v-if="selectedCategoryId" class="text-brand-teal">
                                        in {{ selectedCategoryName }}
                                    </span>
                                </p>
                            </div>

                            <!-- Sort for Mobile -->
                            <div class="md:hidden">
                                <select v-model="sortBy" @change="applyFilters"
                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs">
                                    <option value="newest">Newest First</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Toolbar - Compact -->
                    <div class="bg-white rounded-lg shadow-sm p-3 mb-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <!-- View Toggle -->
                            <div class="flex items-center justify-between sm:justify-start">
                                <div class="flex items-center space-x-1">
                                    <button @click="viewMode = 'grid'"
                                        :class="viewMode === 'grid' ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-400 hover:text-gray-600'"
                                        class="p-1.5 rounded transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                        </svg>
                                    </button>
                                    <button @click="viewMode = 'list'"
                                        :class="viewMode === 'list' ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-400 hover:text-gray-600'"
                                        class="p-1.5 rounded transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Active Filters Badge - Mobile -->
                                <div class="sm:hidden text-xs text-gray-500">
                                    {{ activeFilterCount > 0 ? `${activeFilterCount} active` : '' }}
                                </div>
                            </div>

                            <!-- Sort for Desktop -->
                            <div class="hidden md:flex items-center space-x-3">
                                <span class="text-xs text-gray-600">Sort:</span>
                                <select v-model="sortBy" @change="applyFilters"
                                    class="border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs min-w-[140px]">
                                    <option value="newest">Newest First</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                </select>
                            </div>

                            <!-- Reset Filters Button - Desktop -->
                            <button @click="resetFilters" v-if="activeFilterCount > 0"
                                class="hidden md:inline-flex items-center text-xs text-gray-600 hover:text-gray-800">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reset Filters
                            </button>
                        </div>
                    </div>

                    <!-- Results -->
                    <div v-if="ads.length > 0">
                        <div v-if="viewMode === 'grid'"
                            class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                            <AdCard v-for="ad in ads" :key="ad.id" :ad="ad" />
                        </div>

                        <div v-if="viewMode === 'list'" class="space-y-2 md:space-y-3">
                            <AdListItem v-for="ad in ads" :key="ad.id" :ad="ad" />
                        </div>

                        <!-- Pagination - Compact -->
                        <div v-if="ads.length > 0 && totalAds > ads.length" class="mt-6">
                            <div class="flex justify-center">
                                <Pagination :links="paginationLinks" />
                            </div>
                        </div>
                    </div>

                    <!-- No Results - Compact -->
                    <div v-else class="text-center py-10 md:py-12 bg-white rounded-lg shadow-sm">
                        <div class="max-w-md mx-auto px-4">
                            <svg class="w-12 h-12 md:w-16 md:h-16 text-gray-300 mx-auto mb-3 md:mb-4" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <h3 class="text-lg md:text-xl font-semibold text-gray-900 mb-2">
                                No matching ads found
                            </h3>
                            <p class="text-gray-600 text-xs md:text-sm mb-4 md:mb-5">
                                Try adjusting your filters to find what you're looking for.
                            </p>
                            <button @click="resetFilters"
                                class="px-5 py-2 bg-brand-blue text-white font-medium rounded-lg hover:bg-brand-blue/90 transition-colors duration-200 text-xs shadow-sm">
                                Reset Filters
                            </button>
                        </div>
                    </div>

                </main>
            </div>

        </section>
    </OlxLayout>
</template>

<style scoped>
.mobile-filter-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: white;
    z-index: 50;
    overflow-y: auto;
    padding: 1rem;
    margin: 0;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
    }

    to {
        transform: translateX(0);
    }
}

.rotate-180 {
    transform: rotate(180deg);
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: var(--brand-teal);
    border-radius: 4px;
}
</style>
<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AdCard from '@/components/AdCard.vue'
import AdListItem from '@/components/AdListItem.vue'
import OlxLayout from '@/layouts/OlxLayout.vue'
import debounce from 'lodash/debounce'

const page = usePage()
useForceTheme('light');

// Props from controller
const props = defineProps<{
    ads: {
        data: any[]
        links: any[]
        total: number
    }
    categories: any[]
    brands: any[]
    filters: {
        filter: {
            global?: string
            category?: string
            brand?: string
        }
        min_price?: number
        max_price?: number
        sort_by?: string
    }
    totalAds: number
}>()

// Reactive state
const viewMode = ref<'grid' | 'list'>('grid')
const sortBy = ref(props.filters.sort_by || 'newest')
const minPrice = ref<number | null>(props.filters.min_price || null)
const maxPrice = ref<number | null>(props.filters.max_price || null)
const selectedCategoryId = ref<string | null>(props.filters.filter.category || null)
const selectedBrands = ref<string[]>(props.filters.filter.brand ? props.filters.filter.brand.split(',') : [])
const showMobileFilters = ref(false)
const showAllCategories = ref(false)
const showAllBrands = ref(false)

// Quick price ranges
const priceRanges = [
    { label: 'Under $100', min: 0, max: 100 },
    { label: '$100 - $500', min: 100, max: 500 },
    { label: '$500 - $1000', min: 500, max: 1000 },
    { label: '$1000+', min: 1000, max: null }
]

// Computed properties
const ads = computed(() => props.ads.data)
const paginationLinks = computed(() => props.ads.links)

const filteredBrands = computed(() => {
    let filtered = props.brands

    // Filter brands by selected category if any
    if (selectedCategoryId.value) {
        filtered = filtered.filter(brand =>
            brand.categories?.some((cat: any) => cat.id == selectedCategoryId.value)
        )
    }

    // Limit brands if not showing all
    if (!showAllBrands.value && filtered.length > 10) {
        filtered = filtered.slice(0, 10)
    }

    return filtered
})

const activeFilterCount = computed(() => {
    let count = 0
    if (minPrice.value !== null) count++
    if (maxPrice.value !== null) count++
    if (selectedCategoryId.value) count++
    if (selectedBrands.value.length) count++
    return count
})

const selectedCategoryName = computed(() => {
    if (!selectedCategoryId.value) return ''
    const category = props.categories.find(c => c.id == selectedCategoryId.value)
    return category?.name || ''
})

// Helper methods for counts
const getCategoryAdCount = (category: any) => {
    return category.ads_count || 0
}

const getBrandAdCount = (brandId: string) => {
    return props.ads.data.filter(ad => ad.brand_id == brandId).length || 0
}

// Methods
const selectCategory = (category: any) => {
    if (selectedCategoryId.value === category.id) {
        selectedCategoryId.value = null
    } else {
        selectedCategoryId.value = category.id
        // Reset showAllBrands when category changes
        showAllBrands.value = false
    }
    applyFilters()
}

const toggleBrand = (brandId: string) => {
    const index = selectedBrands.value.indexOf(brandId)
    if (index > -1) {
        selectedBrands.value.splice(index, 1)
    } else {
        selectedBrands.value.push(brandId)
    }
    applyFilters()
}

const setQuickPriceRange = (min: number | null, max: number | null) => {
    minPrice.value = min
    maxPrice.value = max
    applyFilters()
}

const applyFilters = () => {
    const params: any = {}

    if (minPrice.value !== null) params.min_price = minPrice.value
    if (maxPrice.value !== null) params.max_price = maxPrice.value
    if (selectedCategoryId.value) params['filter[category]'] = selectedCategoryId.value
    if (selectedBrands.value.length) params['filter[brand]'] = selectedBrands.value.join(',')
    if (sortBy.value) params.sort_by = sortBy.value

    // Preserve search term if exists
    if (props.filters.filter.global) {
        params['filter[global]'] = props.filters.filter.global
    }

    // Auto-close mobile filters after applying
    if (window.innerWidth < 1024) {
        showMobileFilters.value = false
    }

    router.visit(route('all.items'), {
        method: 'get',
        data: params,
        preserveScroll: true,
        preserveState: true
    })
}

const debouncedApplyFilters = debounce(applyFilters, 500)

const resetFilters = () => {
    minPrice.value = null
    maxPrice.value = null
    selectedCategoryId.value = null
    selectedBrands.value = []
    sortBy.value = 'newest'
    showAllCategories.value = false
    showAllBrands.value = false
    showMobileFilters.value = false

    const params: any = {}

    // Preserve search term if exists
    if (props.filters.filter.global) {
        params['filter[global]'] = props.filters.filter.global
    }

    router.visit(route('all.items'), {
        method: 'get',
        data: params,
        preserveScroll: true,
        preserveState: true
    })
}

const clearPriceFilter = () => {
    minPrice.value = null
    maxPrice.value = null
    applyFilters()
}

const clearCategoryFilter = () => {
    selectedCategoryId.value = null
    applyFilters()
}

const clearBrandFilter = () => {
    selectedBrands.value = []
    applyFilters()
}

// Auto-close mobile filters on larger screens
onMounted(() => {
    const handleResize = () => {
        if (window.innerWidth >= 1024) {
            showMobileFilters.value = false
        }
    }

    window.addEventListener('resize', handleResize)

    // Cleanup
    return () => {
        window.removeEventListener('resize', handleResize)
        debouncedApplyFilters.cancel()
    }
})

// Watch for sort changes to apply filters
onUnmounted(() => {
    debouncedApplyFilters.cancel()
})
</script>

<style scoped>
/* Custom breakpoint for very small screens */
@media (min-width: 475px) {
    .xs\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Improve scrolling on mobile */
@media (max-width: 1023px) {
    aside {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: white;
        z-index: 50;
        overflow-y: auto;
        padding: 1rem;
        margin: 0;
    }

    aside:not(.hidden) {
        animation: slideIn 0.3s ease-out;
    }
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
    }

    to {
        transform: translateX(0);
    }
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #eab308;
    border-radius: 4px;
}
</style>