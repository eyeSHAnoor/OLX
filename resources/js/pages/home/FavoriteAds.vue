<template>
    <OlxLayout>
        <div class="max-w-9/11 mx-auto px-3 sm:px-4 py-4 md:py-6">
            <!-- Header Section -->
            <div class="mb-6 md:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">My Favorite Ads</h1>
                        <p class="text-sm text-gray-600 mt-1">View and manage your saved listings</p>
                    </div>

                    <!-- Clear All Button (only shows if there are favorites) -->
                    <!-- <button v-if="favoriteAds?.total > 0" @click="confirmClearAll"
                        class="inline-flex items-center justify-center gap-2 bg-red-500 text-white px-4 py-2.5 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:bg-red-600 text-sm font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Clear All Favorites
                    </button> -->
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="p-4 md:p-5">
                    <!-- Search and Filter Row -->
                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- Search Bar -->
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input v-model="searchQuery" type="text"
                                placeholder="Search by title, description, location..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm" />
                        </div>

                        <!-- Filter Buttons -->
                        <div class="flex flex-wrap gap-2">
                            <!-- Category Filter -->
                            <select v-model="categoryFilter"
                                class="px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm min-w-[140px]">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>

                            <!-- Brand Filter -->
                            <select v-model="brandFilter"
                                class="px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm min-w-[140px]">
                                <option value="">All Brands</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                    {{ brand.name }}
                                </option>
                            </select>

                            <!-- Price Range -->
                            <div class="flex items-center gap-1">
                                <input v-model="minPrice" type="number" placeholder="Min"
                                    class="w-20 px-2 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm" />
                                <span class="text-gray-500">-</span>
                                <input v-model="maxPrice" type="number" placeholder="Max"
                                    class="w-20 px-2 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm" />
                            </div>

                            <!-- Apply Filters Button -->
                            <button @click="applyFilters"
                                class="px-4 py-2.5 bg-brand-teal text-white rounded-lg hover:from-brand-teal/90 hover:to-brand-blue/90 transition text-sm font-medium">
                                Apply
                            </button>

                            <!-- Reset Filters -->
                            <button @click="resetFilters" v-if="isFiltered"
                                class="px-4 py-2.5 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Active Filters Tags -->
                    <div v-if="isFiltered" class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
                        <span v-if="categoryFilter"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-teal/10 text-brand-teal rounded-full text-xs">
                            Category: {{ getCategoryName(categoryFilter) }}
                            <button @click="categoryFilter = ''" class="hover:text-brand-teal/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="brandFilter"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-blue/10 text-brand-blue rounded-full text-xs">
                            Brand: {{ getBrandName(brandFilter) }}
                            <button @click="brandFilter = ''" class="hover:text-brand-blue/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="minPrice || maxPrice"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-teal/10 text-brand-teal rounded-full text-xs">
                            Price: {{ minPrice || '0' }} - {{ maxPrice || 'Any' }}
                            <button @click="minPrice = ''; maxPrice = ''" class="hover:text-brand-teal/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="searchQuery"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-teal/10 text-brand-teal rounded-full text-xs">
                            Search: "{{ searchQuery }}"
                            <button @click="searchQuery = ''" class="hover:text-brand-teal/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Results Count -->
            <div class="mb-4 text-sm text-gray-600">
                Showing {{ favoriteAds?.from || 0 }} - {{ favoriteAds?.to || 0 }} of {{ favoriteAds?.total || 0 }}
                favorite ads
            </div>

            <!-- Favorite Ads Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5">
                <AdCard v-for="ad in favoriteAds?.data || []" :key="ad.id" :ad="ad"
                    @favorite-removed="handleFavoriteRemoved" />
            </div>

            <!-- No Favorites State -->
            <div v-if="favoriteAds?.data?.length === 0"
                class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">No favorite ads yet</h3>
                <p class="text-sm text-gray-600 mb-4">Start exploring and save ads you're interested in</p>
                <Link :href="route('home')"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-teal to-brand-blue text-white px-4 py-2 rounded-lg hover:from-brand-teal/90 hover:to-brand-blue/90 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Browse Ads
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="favoriteAds && favoriteAds.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
                <button @click="changePage(favoriteAds.current_page - 1)" :disabled="!favoriteAds.prev_page_url"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition">
                    Previous
                </button>

                <div class="flex gap-1">
                    <button v-for="page in favoriteAds.last_page" :key="page" @click="changePage(page)"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-sm transition" :class="page === favoriteAds.current_page
                            ? 'bg-gradient-to-r from-brand-teal to-brand-blue text-white'
                            : 'border border-gray-300 hover:bg-gray-50'">
                        {{ page }}
                    </button>
                </div>

                <button @click="changePage(favoriteAds.current_page + 1)" :disabled="!favoriteAds.next_page_url"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition">
                    Next
                </button>
            </div>
        </div>
    </OlxLayout>

    <!-- Clear All Confirmation Modal -->
    <Teleport to="body">
        <div v-if="showClearAllModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" @click="showClearAllModal = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-4 pb-3 sm:p-5 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100 sm:mx-0 sm:h-8 sm:w-8">
                                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-2 text-center sm:mt-0 sm:ml-3 sm:text-left">
                                <h3 class="text-base font-medium text-gray-900">Clear All Favorites</h3>
                                <div class="mt-1">
                                    <p class="text-xs text-gray-500">
                                        Are you sure you want to remove all ads from your favorites? This action cannot
                                        be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-2 sm:px-5 sm:flex sm:flex-row-reverse">
                        <button @click="clearAllFavorites" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-3 py-1.5 bg-red-600 text-xs font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-2 sm:w-auto">
                            Clear All
                        </button>
                        <button @click="showClearAllModal = false" type="button"
                            class="mt-2 sm:mt-0 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-3 py-1.5 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue sm:w-auto">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import AdCard from '@/components/AdCard.vue'
import axios from 'axios'

interface Props {
    favoriteAds: {
        data: any[]
        current_page: number
        last_page: number
        total: number
        from: number
        to: number
        next_page_url: string | null
        prev_page_url: string | null
    }
    categories: any[]
    brands: any[]
    filters: {
        search?: string
        category?: string
        brand?: string
        min_price?: string
        max_price?: string
    }
}

const props = defineProps<Props>()

// Filter states
const searchQuery = ref(props.filters?.search || '')
const categoryFilter = ref(props.filters?.category || '')
const brandFilter = ref(props.filters?.brand || '')
const minPrice = ref(props.filters?.min_price || '')
const maxPrice = ref(props.filters?.max_price || '')

// Modal states
const showClearAllModal = ref(false)

// Check if any filters are applied
const isFiltered = computed(() => {
    return searchQuery.value || categoryFilter.value || brandFilter.value || minPrice.value || maxPrice.value
})

// Helper functions
const getCategoryName = (id: string) => {
    return props.categories?.find(c => c.id == id)?.name || id
}

const getBrandName = (id: string) => {
    return props.brands?.find(b => b.id == id)?.name || id
}

// Handle favorite removed from AdCard
const handleFavoriteRemoved = (adId: number) => {
    const index = props.favoriteAds.data.findIndex(ad => ad.id === adId)
    if (index !== -1) {
        props.favoriteAds.data.splice(index, 1)
        props.favoriteAds.total--
    }
}

// Actions
const applyFilters = () => {
    router.get(route('user.favorites'), {
        search: searchQuery.value,
        category: categoryFilter.value,
        brand: brandFilter.value,
        min_price: minPrice.value,
        max_price: maxPrice.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const resetFilters = () => {
    searchQuery.value = ''
    categoryFilter.value = ''
    brandFilter.value = ''
    minPrice.value = ''
    maxPrice.value = ''
    applyFilters()
}

const changePage = (page: number) => {
    router.get(route('user.favorites'), {
        page,
        search: searchQuery.value,
        category: categoryFilter.value,
        brand: brandFilter.value,
        min_price: minPrice.value,
        max_price: maxPrice.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

const confirmClearAll = () => {
    showClearAllModal.value = true
}

const clearAllFavorites = async () => {
    try {
        const response = await axios.delete(route('user.favorites.clear'))

        if (response.data.success) {
            // Clear all ads from the list
            props.favoriteAds.data = []
            props.favoriteAds.total = 0
            showClearAllModal.value = false
        }
    } catch (error) {
        console.error('Error clearing favorites:', error)
    }
}

// Debounce search
let searchTimeout: NodeJS.Timeout
watch(searchQuery, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 500)
})
useForceTheme('light');
// Watch other filters for immediate update
watch([categoryFilter, brandFilter, minPrice, maxPrice], () => {
    applyFilters()
})
</script>