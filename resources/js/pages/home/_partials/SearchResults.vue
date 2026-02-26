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

                <!-- Category Filter -->
                <div class="bg-white rounded-xl shadow-sm p-5 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-base md:text-lg text-gray-800">Categories</h3>
                        <button @click="showAllCategories = !showAllCategories"
                            v-if="activeCategory?.children_recursive?.length > 5"
                            class="text-sm text-yellow-600 hover:text-yellow-700 md:hidden">
                            {{ showAllCategories ? 'Show Less' : 'Show All' }}
                        </button>
                    </div>
                    <div class="space-y-1 md:space-y-2 max-h-64 md:max-h-none overflow-y-auto">
                        <div v-for="(child, index) in activeCategory?.children_recursive" :key="child.id" :class="[
                            'text-sm cursor-pointer py-2 md:py-2.5 px-3 rounded-lg transition-all duration-200',
                            'hover:bg-yellow-50 hover:text-yellow-600 hover:pl-4 md:hover:pl-5',
                            'border-l-4 border-transparent hover:border-yellow-500',
                            index >= 5 && !showAllCategories ? 'hidden md:block' : 'block'
                        ]">
                            {{ child.name }}
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
                                <input type="number" placeholder="$ Min" v-model="minPrice"
                                    class="w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none transition text-sm md:text-base" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Max</label>
                                <input type="number" placeholder="$ Max" v-model="maxPrice"
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
                                Results for {{ activeCategory?.name || 'All Categories' }}
                            </h1>
                            <p class="text-gray-600 text-sm md:text-base">
                                {{ filteredAds.length }} ads found
                            </p>
                        </div>

                        <!-- Sort for Mobile -->
                        <div class="md:hidden">
                            <select v-model="sortBy"
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
                            <select v-model="sortBy"
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
                        <div v-if="minPrice || maxPrice"
                            class="inline-flex items-center bg-yellow-50 text-yellow-700 text-xs px-3 py-1.5 rounded-full">
                            Price: {{ minPrice ? `$${minPrice}` : 'Min' }} - {{ maxPrice ? `$${maxPrice}` : 'Max' }}
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
                <div v-if="viewMode === 'grid' && filteredAds.length"
                    class="grid grid-cols-1 xs:grid-cols-3 lg:grid-cols-3 gap-4 md:gap-6">
                    <AdCard v-for="ad in filteredAds" :key="ad.id" :ad="ad" />
                </div>

                <div v-if="viewMode === 'list' && filteredAds.length" class="space-y-3 md:space-y-4">
                    <AdListItem v-for="ad in filteredAds" :key="ad.id" :ad="ad" />
                </div>

                <!-- No Results -->
                <div v-if="!filteredAds.length" class="text-center py-12 md:py-16 bg-white rounded-xl shadow-sm">
                    <div class="max-w-md mx-auto px-4">
                        <svg class="w-16 h-16 md:w-20 md:h-20 text-gray-300 mx-auto mb-4 md:mb-6" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3">
                            {{ originalAds.length ? 'No matching ads found' : 'No ads found' }}
                        </h3>
                        <p class="text-gray-600 text-sm md:text-base mb-6 md:mb-8">
                            {{ originalAds.length ?
                                'Try adjusting your price filter to find what you\'re looking for.'
                                : 'There are no ads in this category yet.' }}
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <button @click="resetFilters"
                                class="px-6 md:px-8 py-2.5 md:py-3 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition-colors duration-200 text-sm md:text-base">
                                Reset Filters
                            </button>
                            <button v-if="!originalAds.length" @click="$router.push('/')"
                                class="px-6 md:px-8 py-2.5 md:py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-sm md:text-base">
                                Browse All Categories
                            </button>
                        </div>
                    </div>
                </div>

            </main>
        </div>

    </section>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import AdCard from '@/components/AdCard.vue'
import AdListItem from '@/components/AdListItem.vue'
useForceTheme('light');
// Interfaces based on your data structure
interface Ad {
    id: number
    ad_title: string
    price: number
    location: string
    created_at: string
    description?: string
    brand?: {
        id: number
        name: string
    }
    brand_id?: number
    category_id?: number
    city?: string
    is_featured?: number
    seller_name?: string
    seller_phone?: string
    user_id?: number
    images?: Array<{
        path: string
    }>
}

interface Category {
    id: number
    name: string
    slug?: string
    parent_id: number | null
    is_active: number
    created_at: string
    updated_at: string
    files?: Array<any>
    children_recursive?: Category[]
    ads?: Ad[]
}

interface Props {
    category?: Category | null
    categories?: Category[]
}

const props = withDefaults(defineProps<Props>(), {
    category: null,
    categories: () => []
})

const viewMode = ref<'grid' | 'list'>('grid')
const sortBy = ref<string>('newest')
const minPrice = ref<number | null>(null)
const maxPrice = ref<number | null>(null)
const showMobileFilters = ref<boolean>(false)
const showAllCategories = ref<boolean>(false)

const activeCategory = computed(() => {
    if (props.category?.ads_count > 0) {
        return props.category
    }

    return props.categories?.find(cat => cat.ads_count > 0) || null
})


// Get ads from the active category
const originalAds = computed(() => {
    return activeCategory.value?.ads || []
})


// Count active filters
const activeFilterCount = computed(() => {
    let count = 0
    if (minPrice.value !== null) count++
    if (maxPrice.value !== null) count++
    return count
})

// Filtered and sorted ads
const filteredAds = computed(() => {
    let ads = [...originalAds.value]

    // Apply price filter
    if (minPrice.value !== null) {
        ads = ads.filter(ad => ad.price >= minPrice.value!)
    }
    if (maxPrice.value !== null) {
        ads = ads.filter(ad => ad.price <= maxPrice.value!)
    }

    // Apply sorting
    switch (sortBy.value) {
        case 'price_low':
            ads.sort((a, b) => a.price - b.price)
            break
        case 'price_high':
            ads.sort((a, b) => b.price - a.price)
            break
        case 'newest':
            ads.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
            break
    }

    return ads
})

const applyPriceFilter = () => {
    console.log('Applying price filter:', { min: minPrice.value, max: maxPrice.value })
    // Auto-close mobile filters after applying
    if (window.innerWidth < 1024) {
        showMobileFilters.value = false
    }
}

const resetFilters = () => {
    minPrice.value = null
    maxPrice.value = null
    sortBy.value = 'newest'
    showMobileFilters.value = false
}

const clearPriceFilter = () => {
    minPrice.value = null
    maxPrice.value = null
}

// Auto-close mobile filters on larger screens
watch(() => window.innerWidth, (width) => {
    if (width >= 1024) {
        showMobileFilters.value = false
    }
})

// Close mobile filters when clicking outside (optional)
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement
    if (!target.closest('aside') && !target.closest('[class*="mobile-filter-toggle"]')) {
        showMobileFilters.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
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
</style>