<template>
    <OlxLayout>
        <section class="max-w-9/11 mx-auto px-3 sm:px-4 py-6 md:py-8">

            <!-- Mobile Filter Toggle -->
            <div class="lg:hidden mb-4">
                <button @click="showMobileFilters = !showMobileFilters"
                    class="w-full flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                    <span class="font-medium text-gray-800">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filters
                        <span v-if="activeFilterCount > 0"
                            class="ml-2 bg-yellow-600 text-white text-xs px-2 py-0.5 rounded-full">
                            {{ activeFilterCount }}
                        </span>
                    </span>
                    <span class="text-gray-500">
                        {{ showMobileFilters ? '▲' : '▼' }}
                    </span>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">

                <!-- Sidebar - Hidden on mobile unless toggled -->
                <aside class="lg:col-span-1 space-y-6" :class="showMobileFilters ? 'block' : 'hidden lg:block'">

                    <!-- Close button for mobile -->
                    <div class="lg:hidden flex items-center justify-between mb-4">
                        <h2 class="font-bold text-lg">Filters</h2>
                        <button @click="showMobileFilters = false" class="p-2 hover:bg-gray-100 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Category Filter -->
                    <div class="bg-white rounded-xl shadow-sm p-5 md:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-base md:text-lg text-gray-800">Categories</h3>
                            <button @click="showAllCategories = !showAllCategories" v-if="categories?.length > 5"
                                class="text-sm text-yellow-600 hover:text-yellow-700">
                                {{ showAllCategories ? 'Show Less' : 'Show All' }}
                            </button>
                        </div>
                        <div class="space-y-1 md:space-y-2 max-h-64 md:max-h-none overflow-y-auto">
                            <div v-for="(category, index) in categories" :key="category.id"
                                @click="selectCategory(category)" :class="[
                                    'text-sm cursor-pointer py-2 md:py-2.5 px-3 rounded-lg transition-all duration-200',
                                    'hover:bg-yellow-50 hover:text-yellow-600 hover:pl-4 md:hover:pl-5',
                                    'border-l-4 transition-colors',
                                    selectedCategoryId === category.id
                                        ? 'bg-yellow-50 text-yellow-600 border-yellow-500'
                                        : 'border-transparent hover:border-yellow-500',
                                    index >= 5 && !showAllCategories ? 'hidden' : 'block'
                                ]">
                                {{ category.name }}
                                <span class="text-xs text-gray-400 ml-1">({{ getCategoryAdCount(category) }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Price Filter -->
                    <div class="bg-white rounded-xl shadow-sm p-5 md:p-6">
                        <h3 class="font-semibold text-base md:text-lg text-gray-800 mb-4">Price Range</h3>
                        <div class="space-y-3 md:space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Min ($)</label>
                                    <input type="number" placeholder="Min" v-model.number="minPrice"
                                        @input="debouncedApplyFilters"
                                        class="w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none transition text-sm md:text-base" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Max ($)</label>
                                    <input type="number" placeholder="Max" v-model.number="maxPrice"
                                        @input="debouncedApplyFilters"
                                        class="w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none transition text-sm md:text-base" />
                                </div>
                            </div>

                            <!-- Quick price suggestions -->
                            <div class="flex flex-wrap gap-2">
                                <button v-for="range in priceRanges" :key="range.label"
                                    @click="setQuickPriceRange(range.min, range.max)"
                                    class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-full transition-colors">
                                    {{ range.label }}
                                </button>
                            </div>

                            <button @click="applyFilters"
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2.5 md:py-3 rounded-lg transition-all duration-200 shadow-sm hover:shadow text-sm md:text-base">
                                Apply Filter
                            </button>
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div class="bg-white rounded-xl shadow-sm p-5 md:p-6" v-if="brands?.length">
                        <h3 class="font-semibold text-base md:text-lg text-gray-800 mb-4">Brands</h3>
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            <div v-for="brand in filteredBrands" :key="brand.id" @click="toggleBrand(brand.id)" :class="[
                                'flex items-center justify-between cursor-pointer py-2 px-3 rounded-lg transition-colors',
                                'hover:bg-yellow-50',
                                selectedBrands.includes(brand.id) ? 'bg-yellow-50 text-yellow-600' : ''
                            ]">
                                <span class="text-sm">{{ brand.name }}</span>
                                <span class="text-xs text-gray-400">{{ getBrandAdCount(brand.id) }}</span>
                            </div>
                        </div>

                        <!-- Show all brands toggle if many -->
                        <button v-if="brands.length > 10" @click="showAllBrands = !showAllBrands"
                            class="mt-3 text-sm text-yellow-600 hover:text-yellow-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    :d="showAllBrands ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" />
                            </svg>
                            {{ showAllBrands ? 'Show Less' : `Show All (${brands.length})` }}
                        </button>
                    </div>

                    <!-- Mobile Filter Actions -->
                    <div class="lg:hidden bg-white rounded-xl shadow-sm p-4 border-t border-gray-100 sticky bottom-0">
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="resetFilters"
                                class="py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-sm">
                                Reset All
                            </button>
                            <button @click="showMobileFilters = false"
                                class="py-3 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition-colors text-sm">
                                Show Results
                            </button>
                        </div>
                    </div>

                </aside>

                <!-- Main Content -->
                <main class="lg:col-span-2">
                    <!-- Header -->
                    <div class="mb-6 md:mb-8">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div>
                                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                                    All Items
                                </h1>
                                <p class="text-gray-600 text-sm md:text-base">
                                    {{ totalAds }} ads found
                                    <span v-if="selectedCategoryId" class="text-yellow-600">
                                        in {{ selectedCategoryName }}
                                    </span>
                                </p>
                            </div>

                            <!-- Sort for Mobile -->
                            <div class="md:hidden">
                                <select v-model="sortBy" @change="applyFilters"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none transition text-sm">
                                    <option value="newest">Newest First</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Toolbar -->
                    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <!-- View Toggle -->
                            <div class="flex items-center justify-between sm:justify-start">
                                <div class="flex items-center space-x-1 md:space-x-2">
                                    <button @click="viewMode = 'grid'"
                                        :class="viewMode === 'grid' ? 'bg-yellow-100 text-yellow-600' : 'text-gray-400 hover:text-gray-600'"
                                        class="p-2 md:p-2.5 rounded-lg transition-all duration-200">
                                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                        </svg>
                                    </button>
                                    <button @click="viewMode = 'list'"
                                        :class="viewMode === 'list' ? 'bg-yellow-100 text-yellow-600' : 'text-gray-400 hover:text-gray-600'"
                                        class="p-2 md:p-2.5 rounded-lg transition-all duration-200">
                                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Active Filters Badge - Mobile -->
                                <div class="sm:hidden text-sm text-gray-500">
                                    {{ activeFilterCount > 0 ? `${activeFilterCount} active` : '' }}
                                </div>
                            </div>

                            <!-- Sort for Desktop -->
                            <div class="hidden md:flex items-center space-x-4">
                                <span class="text-gray-600">Sort by:</span>
                                <select v-model="sortBy" @change="applyFilters"
                                    class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none transition min-w-[180px]">
                                    <option value="newest">Newest First</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                </select>
                            </div>

                            <!-- Reset Filters Button - Desktop -->
                            <button @click="resetFilters" v-if="activeFilterCount > 0"
                                class="hidden md:inline-flex items-center text-sm text-gray-600 hover:text-gray-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reset Filters
                            </button>
                        </div>
                    </div>

                    <!-- Active Filters -->
                    <div v-if="activeFilterCount > 0" class="mb-4">
                        <div class="flex items-center flex-wrap gap-2">
                            <div v-if="selectedCategoryId"
                                class="inline-flex items-center bg-yellow-50 text-yellow-700 text-xs px-3 py-1.5 rounded-full">
                                Category: {{ selectedCategoryName }}
                                <button @click="clearCategoryFilter" class="ml-1.5 hover:text-yellow-900">
                                    ×
                                </button>
                            </div>
                            <div v-if="minPrice || maxPrice"
                                class="inline-flex items-center bg-yellow-50 text-yellow-700 text-xs px-3 py-1.5 rounded-full">
                                Price: {{ minPrice ? `$${minPrice}` : '$0' }} - {{ maxPrice ? `$${maxPrice}` : 'Any' }}
                                <button @click="clearPriceFilter" class="ml-1.5 hover:text-yellow-900">
                                    ×
                                </button>
                            </div>
                            <div v-if="selectedBrands.length"
                                class="inline-flex items-center bg-yellow-50 text-yellow-700 text-xs px-3 py-1.5 rounded-full">
                                {{ selectedBrands.length }} {{ selectedBrands.length === 1 ? 'Brand' : 'Brands' }}
                                <button @click="clearBrandFilter" class="ml-1.5 hover:text-yellow-900">
                                    ×
                                </button>
                            </div>
                            <button @click="resetFilters" class="text-xs text-gray-500 hover:text-gray-700 underline">
                                Clear all
                            </button>
                        </div>
                    </div>

                    <!-- Results -->
                    <div v-if="ads.length > 0">
                        <div v-if="viewMode === 'grid'"
                            class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                            <AdCard v-for="ad in ads" :key="ad.id" :ad="ad" />
                        </div>

                        <div v-if="viewMode === 'list'" class="space-y-3 md:space-y-4">
                            <AdListItem v-for="ad in ads" :key="ad.id" :ad="ad" />
                        </div>

                        <!-- Pagination -->
                        <div v-if="ads.length > 0 && totalAds > ads.length" class="mt-8">
                            <div class="flex justify-center">
                                <Pagination :links="paginationLinks" />
                            </div>
                        </div>
                    </div>

                    <!-- No Results -->
                    <div v-else class="text-center py-12 md:py-16 bg-white rounded-xl shadow-sm">
                        <div class="max-w-md mx-auto px-4">
                            <svg class="w-16 h-16 md:w-20 md:h-20 text-gray-300 mx-auto mb-4 md:mb-6" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3">
                                No matching ads found
                            </h3>
                            <p class="text-gray-600 text-sm md:text-base mb-6 md:mb-8">
                                Try adjusting your filters to find what you're looking for.
                            </p>
                            <button @click="resetFilters"
                                class="px-6 md:px-8 py-2.5 md:py-3 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition-colors duration-200 text-sm md:text-base">
                                Reset Filters
                            </button>
                        </div>
                    </div>

                </main>
            </div>

        </section>
    </OlxLayout>
</template>

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