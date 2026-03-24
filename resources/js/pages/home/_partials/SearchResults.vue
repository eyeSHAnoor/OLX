<template>
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
                </span>
                <span class="text-gray-500">
                    {{ showMobileFilters ? '▲' : '▼' }}
                </span>
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">

            <!-- Sidebar - Hidden on mobile unless toggled -->
            <aside class="lg:col-span-1 space-y-6" :class="showMobileFilters ? 'block' : 'hidden lg:block'">

                <!-- Search Input - Mobile/Desktop -->
                <div class="bg-white rounded-xl shadow-sm p-5 md:p-6">
                    <h3 class="font-semibold text-base md:text-lg text-gray-800 mb-4">Search</h3>
                    <input type="text" v-model="searchTerm" @keyup.enter="applyFilters" placeholder="Search ads..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none transition" />
                </div>

                <!-- Category Filter -->
                <div class="bg-white rounded-xl shadow-sm p-5 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-base md:text-lg text-gray-800">Categories</h3>
                        <button @click="showAllCategories = !showAllCategories" v-if="categories?.length > 5"
                            class="text-sm text-yellow-600 hover:text-yellow-700 md:hidden">
                            {{ showAllCategories ? 'Show Less' : 'Show All' }}
                        </button>
                    </div>
                    <div class="space-y-1 md:space-y-2 max-h-64 md:max-h-none overflow-y-auto">
                        <div v-for="(category, index) in categories" :key="category.id"
                            @click="selectCategory(category.id)" :class="[
                                'text-sm cursor-pointer py-2 md:py-2.5 px-3 rounded-lg transition-all duration-200',
                                'hover:bg-yellow-50 hover:text-yellow-600 hover:pl-4 md:hover:pl-5',
                                'border-l-4',
                                selectedCategoryId === category.id ? 'border-yellow-500 bg-yellow-50 text-yellow-600' : 'border-transparent hover:border-yellow-500',
                                index >= 5 && !showAllCategories ? 'hidden md:block' : 'block'
                            ]">
                            {{ category.name }}
                            <span class="text-xs text-gray-400 ml-2" v-if="category.ads_count">
                                ({{ category.ads_count }})
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Brand Filter -->
                <div class="bg-white rounded-xl shadow-sm p-5 md:p-6">
                    <h3 class="font-semibold text-base md:text-lg text-gray-800 mb-4">Brands</h3>
                    <div class="space-y-1 md:space-y-2 max-h-64 md:max-h-none overflow-y-auto">
                        <div v-for="brand in brands" :key="brand.id" @click="selectBrand(brand.id)" :class="[
                            'text-sm cursor-pointer py-2 md:py-2.5 px-3 rounded-lg transition-all duration-200',
                            'hover:bg-yellow-50 hover:text-yellow-600 hover:pl-4 md:hover:pl-5',
                            'border-l-4',
                            selectedBrandId === brand.id ? 'border-yellow-500 bg-yellow-50 text-yellow-600' : 'border-transparent hover:border-yellow-500'
                        ]">
                            {{ brand.name }}
                        </div>
                    </div>
                </div>

                <!-- Price Filter -->
                <div class="bg-white rounded-xl shadow-sm p-5 md:p-6">
                    <h3 class="font-semibold text-base md:text-lg text-gray-800 mb-4">Price Range</h3>
                    <div class="space-y-3 md:space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Min</label>
                                <input type="number" placeholder=" Min" v-model.number="minPrice"
                                    @change="applyPriceFilter"
                                    class="w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none transition text-sm md:text-base" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Max</label>
                                <input type="number" placeholder=" Max" v-model.number="maxPrice"
                                    @change="applyPriceFilter"
                                    class="w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none transition text-sm md:text-base" />
                            </div>
                        </div>
                        <button @click="applyPriceFilter"
                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2.5 md:py-3 rounded-lg transition-all duration-200 shadow-sm hover:shadow text-sm md:text-base">
                            Apply Filter
                        </button>
                    </div>
                </div>

                <!-- Mobile Filter Actions -->
                <div class="lg:hidden bg-white rounded-xl shadow-sm p-4 border-t border-gray-100">
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
                                {{ getPageTitle }}
                            </h1>
                            <p class="text-gray-600 text-sm md:text-base">
                                {{ ads?.data?.length || 0 }} ads found
                            </p>
                        </div>

                        <!-- Sort for Mobile -->
                        <div class="md:hidden">
                            <select v-model="sortBy" @change="applyFilters"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none transition text-sm">
                                <option value="newest">Newest First</option>
                                <option value="price_low">Price: Low to High</option>
                                <option value="price_high">Price: High to Low</option>
                                <option value="relevance">Most Relevant</option>
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
                                <option value="relevance">Most Relevant</option>
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

                <!-- Active Filters - Mobile -->
                <div v-if="activeFilterCount > 0" class="mb-4 md:hidden">
                    <div class="flex items-center flex-wrap gap-2">
                        <div v-if="selectedCategoryId"
                            class="inline-flex items-center bg-yellow-50 text-yellow-700 text-xs px-3 py-1.5 rounded-full">
                            Category: {{ getCategoryName(selectedCategoryId) }}
                            <button @click="clearCategoryFilter" class="ml-1.5 hover:text-yellow-900">
                                ×
                            </button>
                        </div>
                        <div v-if="selectedBrandId"
                            class="inline-flex items-center bg-yellow-50 text-yellow-700 text-xs px-3 py-1.5 rounded-full">
                            Brand: {{ getBrandName(selectedBrandId) }}
                            <button @click="clearBrandFilter" class="ml-1.5 hover:text-yellow-900">
                                ×
                            </button>
                        </div>
                        <div v-if="minPrice || maxPrice"
                            class="inline-flex items-center bg-yellow-50 text-yellow-700 text-xs px-3 py-1.5 rounded-full">
                            Price: {{ minPrice ? `${minPrice}` : 'Min' }} - {{ maxPrice ? `$${maxPrice}` : 'Max' }}
                            <button @click="clearPriceFilter" class="ml-1.5 hover:text-yellow-900">
                                ×
                            </button>
                        </div>
                        <button @click="resetFilters" class="text-xs text-gray-500 hover:text-gray-700">
                            Clear all
                        </button>
                    </div>
                </div>

                <!-- Results -->
                <div v-if="viewMode === 'grid' && ads?.data?.length"
                    class="grid grid-cols-1 xs:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
                    <AdCard v-for="ad in ads.data" :key="ad.id" :ad="ad" />
                </div>

                <div v-if="viewMode === 'list' && ads?.data?.length" class="space-y-3 md:space-y-4">
                    <AdListItem v-for="ad in ads.data" :key="ad.id" :ad="ad" />
                </div>

                <!-- No Results -->
                <div v-if="!ads?.data?.length" class="text-center py-12 md:py-16 bg-white rounded-xl shadow-sm">
                    <div class="max-w-md mx-auto px-4">
                        <svg class="w-16 h-16 md:w-20 md:h-20 text-gray-300 mx-auto mb-4 md:mb-6" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3">
                            {{ ads?.total ? 'No matching ads found' : 'No ads found' }}
                        </h3>
                        <p class="text-gray-600 text-sm md:text-base mb-6 md:mb-8">
                            {{ ads?.total ?
                                'Try adjusting your filters to find what you\'re looking for.'
                                : 'There are no ads in this category yet.' }}
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <button @click="resetFilters"
                                class="px-6 md:px-8 py-2.5 md:py-3 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition-colors duration-200 text-sm md:text-base">
                                Reset Filters
                            </button>
                            <button v-if="!ads?.total" @click="$router.push('/')"
                                class="px-6 md:px-8 py-2.5 md:py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-sm md:text-base">
                                Browse All Categories
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="ads?.last_page > 1" class="mt-8 flex justify-center">
                    <nav class="inline-flex rounded-md shadow">
                        <button v-for="page in ads.last_page" :key="page" @click="goToPage(page)" :class="[
                            'px-4 py-2 text-sm font-medium border',
                            page === ads.current_page
                                ? 'bg-yellow-600 text-white border-yellow-600'
                                : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                        ]">
                            {{ page }}
                        </button>
                    </nav>
                </div>

            </main>
        </div>

    </section>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AdCard from '@/components/AdCard.vue'
import AdListItem from '@/components/AdListItem.vue'

declare const useForceTheme: any;
useForceTheme('light');

// Props from Laravel/Inertia
const props = defineProps<{
    ads: {
        data: any[]
        current_page: number
        last_page: number
        total: number
        per_page: number
    }
    categories: any[]
    brands: any[]
    filters: {
        filter?: {
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

const viewMode = ref<'grid' | 'list'>('grid')
const showMobileFilters = ref<boolean>(false)
const showAllCategories = ref<boolean>(false)

// Filter states (initialized from props)
const selectedCategoryId = ref<number | null>(null)
const selectedBrandId = ref<number | null>(null)
const minPrice = ref<number | null>(null)
const maxPrice = ref<number | null>(null)
const sortBy = ref<string>('newest')
const searchTerm = ref<string>('')

// Initialize filters from props
const initFilters = () => {
    const filters = props.filters || {}

    // Initialize search
    if (filters.filter?.global) {
        searchTerm.value = filters.filter.global
    }

    // Initialize category
    if (filters.filter?.category) {
        selectedCategoryId.value = parseInt(filters.filter.category as string)
    }

    // Initialize brand
    if (filters.filter?.brand) {
        selectedBrandId.value = parseInt(filters.filter.brand as string)
    }

    // Initialize price
    if (filters.min_price) {
        minPrice.value = parseInt(filters.min_price as any)
    }
    if (filters.max_price) {
        maxPrice.value = parseInt(filters.max_price as any)
    }

    // Initialize sort
    if (filters.sort_by) {
        sortBy.value = filters.sort_by
    }
}

// Call init on mount
onMounted(() => {
    initFilters()
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})

// Computed properties
const getPageTitle = computed(() => {
    if (searchTerm.value) {
        return `Search results for "${searchTerm.value}"`
    }
    if (selectedCategoryId.value) {
        const category = props.categories.find(c => c.id === selectedCategoryId.value)
        return category ? category.name : 'All Categories'
    }
    return 'All Items'
})

// Helper functions to get names
const getCategoryName = (id: number) => {
    const category = props.categories.find(c => c.id === id)
    return category ? category.name : ''
}

const getBrandName = (id: number) => {
    const brand = props.brands.find(b => b.id === id)
    return brand ? brand.name : ''
}

// Count active filters
const activeFilterCount = computed(() => {
    let count = 0
    if (selectedCategoryId.value) count++
    if (selectedBrandId.value) count++
    if (minPrice.value !== null && minPrice.value !== undefined) count++
    if (maxPrice.value !== null && maxPrice.value !== undefined) count++
    if (searchTerm.value) count++
    return count
})

// Function to apply filters (navigate with new filters)
const applyFilters = () => {
    const params: any = {}

    // Build filter object
    if (searchTerm.value) {
        params.filter = { ...params.filter, global: searchTerm.value }
    }

    if (selectedCategoryId.value) {
        params.filter = { ...params.filter, category: selectedCategoryId.value }
    }

    if (selectedBrandId.value) {
        params.filter = { ...params.filter, brand: selectedBrandId.value }
    }

    if (minPrice.value !== null && minPrice.value !== undefined) {
        params.min_price = minPrice.value
    }

    if (maxPrice.value !== null && maxPrice.value !== undefined) {
        params.max_price = maxPrice.value
    }

    params.sort_by = sortBy.value || 'newest'

    // Use Inertia to navigate with new filters
    router.get('/all-items', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })

    // Close mobile filters
    if (window.innerWidth < 1024) {
        showMobileFilters.value = false
    }
}

// Category selection
const selectCategory = (categoryId: number) => {
    if (selectedCategoryId.value === categoryId) {
        // Toggle off if same category
        selectedCategoryId.value = null
    } else {
        selectedCategoryId.value = categoryId
    }
    applyFilters()
}

// Brand selection
const selectBrand = (brandId: number) => {
    if (selectedBrandId.value === brandId) {
        selectedBrandId.value = null
    } else {
        selectedBrandId.value = brandId
    }
    applyFilters()
}

// Clear individual filters
const clearCategoryFilter = () => {
    selectedCategoryId.value = null
    applyFilters()
}

const clearBrandFilter = () => {
    selectedBrandId.value = null
    applyFilters()
}

// Price filter
const applyPriceFilter = () => {
    applyFilters()
}

// Clear price filter
const clearPriceFilter = () => {
    minPrice.value = null
    maxPrice.value = null
    applyFilters()
}

// Reset all filters
const resetFilters = () => {
    selectedCategoryId.value = null
    selectedBrandId.value = null
    minPrice.value = null
    maxPrice.value = null
    sortBy.value = 'newest'
    searchTerm.value = ''
    showMobileFilters.value = false

    // Navigate without filters
    router.get('/all-items', {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

// Pagination
const goToPage = (page: number) => {
    const params: any = { page }

    // Preserve all current filters
    if (searchTerm.value) {
        params.filter = { ...params.filter, global: searchTerm.value }
    }
    if (selectedCategoryId.value) {
        params.filter = { ...params.filter, category: selectedCategoryId.value }
    }
    if (selectedBrandId.value) {
        params.filter = { ...params.filter, brand: selectedBrandId.value }
    }
    if (minPrice.value) params.min_price = minPrice.value
    if (maxPrice.value) params.max_price = maxPrice.value
    if (sortBy.value !== 'newest') params.sort_by = sortBy.value

    router.get('/all-items', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

// Handle sort change (already handled by watcher but keep for clarity)
watch(sortBy, () => {
    applyFilters()
})

// Handle click outside for mobile filters
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement
    if (!target.closest('aside') && !target.closest('[class*="mobile-filter-toggle"]')) {
        showMobileFilters.value = false
    }
}
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
</style>