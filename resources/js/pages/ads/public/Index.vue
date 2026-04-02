<template>
    <OlxLayout>
        <TopCategoriesBar />
        <div class="max-w-9/11 mx-auto px-3 sm:px-4 py-4 md:py-6">
            <!-- Header Section -->
            <div class="mb-6 md:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">My Ads</h1>
                        <p class="text-sm text-gray-600 mt-1">Manage and track all your marketplace listings</p>
                    </div>

                    <!-- Create New Ad Button -->
                    <Link :href="route('user.ads.create')"
                        class="inline-flex items-center justify-center gap-2 bg-brand-teal text-white px-4 py-2.5 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg text-sm font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Post New Ad
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mt-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ totalAds }}</p>
                            <p class="text-xs text-gray-600 mt-1">Total Ads</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ activeAds }}</p>
                            <p class="text-xs text-gray-600 mt-1">Active</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ pendingAds }}</p>
                            <p class="text-xs text-gray-600 mt-1">Pending</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ totalViews }}</p>
                            <p class="text-xs text-gray-600 mt-1">Total Views</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="p-4 md:p-5">
                    <!-- Search and Filter Row -->
                    <div class="flex flex-wrap gap-2 items-center">
                        <!-- Search Bar -->
                        <div class="flex-1 min-w-[150px] relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input v-model="filters.global" type="text"
                                placeholder="Search by title, description, location, or brand..."
                                class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm sm:text-xs" />
                        </div>

                        <!-- Category Filter -->
                        <select v-model="filters.category" @change="handleCategoryChange"
                            class="flex-shrink-0 px-2 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none text-sm sm:text-xs min-w-[110px]">
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>

                        <!-- Sort Filter -->
                        <select v-model="sort"
                            class="flex-shrink-0 px-2 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none text-sm sm:text-xs min-w-[120px]">
                            <option value="newest">Newest First</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                        </select>

                        <!-- Action Buttons -->
                        <button @click="applyFilters"
                            class="flex-1 md:flex-none px-3 py-2 bg-brand-teal text-white rounded-lg hover:bg-brand-teal/90 transition text-sm sm:text-xs">
                            Apply
                        </button>
                        <button v-if="isFiltered" @click="resetFilters"
                            class="flex-1 md:flex-none px-3 py-2 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm sm:text-xs">
                            Reset
                        </button>
                    </div>

                    <!-- Active Filters Tags -->
                    <div v-if="isFiltered" class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
                        <span v-if="filters.global"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-teal/10 text-brand-teal rounded-full text-xs sm:text-[10px]">
                            Search: "{{ truncateText(filters.global, 30) }}"
                            <button @click="filters.global = ''; applyFilters()" class="hover:text-brand-teal/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="filters.category"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-teal/10 text-brand-teal rounded-full text-xs sm:text-[10px]">
                            Category: {{ getCategoryName(filters.category) }}
                            <button @click="filters.category = ''; handleCategoryChange(); applyFilters()"
                                class="hover:text-brand-teal/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="filters.brand"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-blue/10 text-brand-blue rounded-full text-xs sm:text-[10px]">
                            Brand: {{ getBrandName(filters.brand) }}
                            <button @click="filters.brand = ''; applyFilters()" class="hover:text-brand-blue/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="filters.min_price || filters.max_price"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-teal/10 text-brand-teal rounded-full text-xs sm:text-[10px]">
                            Price: {{ formatPrice(filters.min_price) }} - {{ formatPrice(filters.max_price) }}
                            <button @click="filters.min_price = ''; filters.max_price = ''; applyFilters()"
                                class="hover:text-brand-teal/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="sort !== 'newest'"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs sm:text-[10px]">
                            Sort: {{ getSortLabel(sort) }}
                            <button @click="sort = 'newest'; applyFilters()" class="hover:text-gray-900">
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
                Showing {{ allLoadedAds.length }} of {{ totalAds }} ads
            </div>

            <!-- Status Tabs -->
            <!-- Status Tabs -->
            <div class="border-b border-gray-200 my-4 sm:my-6">

                <nav class="-mb-px flex overflow-x-auto no-scrollbar space-x-4 sm:space-x-6 px-1" aria-label="Tabs">

                    <button v-for="tab in statusTabs" :key="tab.value" @click="setStatusFilter(tab.value)" :class="[
                        'flex items-center whitespace-nowrap pb-3 px-2 border-b-2 font-medium text-xs sm:text-sm transition',
                        activeStatusTab === tab.value
                            ? 'border-brand-teal text-brand-teal'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                    ]">
                        {{ tab.label }}

                    </button>

                </nav>

            </div>

            <!-- Loading Spinner (when filters applied and no ads yet) -->
            <div v-if="loading && allLoadedAds.length === 0" class="text-center py-12">
                <svg class="animate-spin w-10 h-10 text-brand-teal mx-auto mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="text-sm text-gray-500">Loading ads...</p>
            </div>

            <!-- Ads Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5">
                <div v-for="ad in allLoadedAds" :key="ad.id"
                    class="group bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-200">

                    <!-- Image Container -->
                    <div class="relative aspect-square bg-gray-100">
                        <img v-if="ad.images?.length"
                            :src="`/storage/${ad.images.find(img => img.is_primary)?.path || ad.images[0].path}`"
                            :alt="ad.ad_title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <!-- Status Badge -->
                        <div class="absolute top-2 left-2">
                            <span :class="{
                                'bg-green-500': ad.status === 'active',
                                'bg-yellow-500': ad.status === 'pending',
                                'bg-gray-500': ad.status === 'sold' || ad.status === 'expired'
                            }" class="px-2 py-1 text-xs font-medium text-white rounded-md">
                                {{ ad.status || 'active' }}
                            </span>
                        </div>

                        <!-- Action Buttons Overlay -->
                        <div
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2">
                            <button @click="editAd(ad.id)"
                                class="p-2 bg-white rounded-lg hover:bg-brand-teal hover:text-white transition-colors shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button @click="viewAd(ad.id)"
                                class="p-2 bg-white rounded-lg hover:bg-brand-blue hover:text-white transition-colors shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <button @click="confirmDeleteAd(ad)"
                                class="p-2 bg-white rounded-lg hover:bg-red-600 hover:text-white transition-colors shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                            <button v-if="ad.status !== 'sold'" @click="toggleAdStatus(ad)"
                                class="p-2 bg-white rounded-lg hover:bg-yellow-500 hover:text-white transition-colors shadow-lg"
                                :title="ad.status === 'active' ? 'Deactivate' : 'Activate'">
                                <svg v-if="ad.status === 'active'" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                            <button v-else @click="reactivateSoldAd(ad)"
                                class="p-2 bg-white rounded-lg hover:bg-green-600 hover:text-white transition-colors shadow-lg"
                                title="Reactivate (make Active)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Ad Details -->
                    <div class="p-3">
                        <div class="text-lg font-bold text-brand-teal mb-1">
                            {{ formatPrice(ad.price) }}
                        </div>
                        <h3 class="font-medium text-gray-900 mb-1 line-clamp-2 text-sm">
                            {{ ad.ad_title }}
                        </h3>
                        <div class="flex flex-wrap gap-1 mb-2">
                            <span v-if="ad.category"
                                class="px-2 py-0.5 bg-brand-teal/10 text-brand-teal rounded text-xs">
                                {{ ad.category.name }}
                            </span>
                            <span v-if="ad.brand" class="px-2 py-0.5 bg-brand-blue/10 text-brand-blue rounded text-xs">
                                {{ ad.brand.name }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ ad.location || 'Location not set' }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ formatDate(ad.created_at) }}
                            </div>
                        </div>
                        <div class="flex items-center gap-1 mt-2 text-xs text-gray-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ ad.views || 0 }} views
                        </div>
                    </div>
                </div>
            </div>

            <!-- Infinite scroll loading indicator (bottom) -->
            <div v-if="loading && allLoadedAds.length > 0" class="text-center py-6">
                <div class="inline-flex items-center gap-2 text-gray-500">
                    <svg class="animate-spin h-5 w-5 text-brand-teal" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-sm">Loading more ads...</span>
                </div>
            </div>

            <!-- Infinite scroll trigger -->
            <div ref="loadMoreTrigger" v-if="hasMorePages && !loading && allLoadedAds.length > 0" class="h-10"></div>

            <!-- No more items message -->
            <div v-if="!hasMorePages && allLoadedAds.length > 0 && allLoadedAds.length === totalAds"
                class="text-center py-6">
                <p class="text-sm text-gray-400">You've seen all {{ totalAds }} ads</p>
            </div>

            <!-- No Ads State -->
            <div v-if="allLoadedAds.length === 0 && !loading"
                class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">No ads found</h3>
                <p class="text-sm text-gray-600 mb-4">Get started by posting your first ad</p>
                <Link :href="route('user.ads.create')"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-teal to-brand-blue text-white px-4 py-2 rounded-lg hover:from-brand-teal/90 hover:to-brand-blue/90 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Post Your First Ad
                </Link>
            </div>
        </div>
        <ShadcnAlertDialog />
    </OlxLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import debounce from 'lodash/debounce'
import { useShadcnAlert } from '@/composables/useShadcnAlert'
import ShadcnAlertDialog from '@/components/ShadcnAlertDialog.vue'
import TopCategoriesBar from '@/components/TopCategoriesBar.vue'

interface Props {
    ads: {
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
        filter: {
            global: string | null
            category: string | null
            brand: string | null
            min_price: string | null
            max_price: string | null
            status: string | null
        }
        sort: string
    }
}

const props = defineProps<Props>()

// ==================== ALL REFS (MUST BE DEFINED BEFORE ANY WATCH THAT USES THEM) ====================
const allLoadedAds = ref<any[]>([])
const currentPage = ref(1)
const totalPages = ref(1)
const totalAds = ref(0)
const loading = ref(false)           // <-- Moved up to fix ReferenceError

// Status tabs
const activeStatusTab = ref(props.filters?.filter?.status || 'all')
const statusTabs = [
    { label: 'All Ads', value: 'all' },
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
    { label: 'Sold', value: 'sold' },
]

// Filter states
const filters = ref({
    global: props.filters?.filter?.global || '',
    category: props.filters?.filter?.category || '',
    brand: props.filters?.filter?.brand || '',
    min_price: props.filters?.filter?.min_price || '',
    max_price: props.filters?.filter?.max_price || '',
    status: props.filters?.filter?.status || ''
})

const sort = ref(props.filters?.sort || 'newest')

// Infinite scroll refs
const loadMoreTrigger = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

// ==================== WATCHES ====================
// This watch now has access to 'loading' because it's defined above
watch(() => props.ads, (newData) => {
    if (newData) {
        if (newData.current_page === 1) {
            allLoadedAds.value = [...newData.data]
        } else {
            allLoadedAds.value = [...allLoadedAds.value, ...newData.data]
        }
        currentPage.value = newData.current_page
        totalPages.value = newData.last_page
        totalAds.value = newData.total
        loading.value = false   // Safe now
    }
}, { immediate: true, deep: true })

// ==================== COMPUTED ====================
const hasMorePages = computed(() => currentPage.value < totalPages.value)

const activeAds = computed(() => allLoadedAds.value.filter(ad => ad.status === 'active').length)
const pendingAds = computed(() => allLoadedAds.value.filter(ad => ad.status === 'pending').length)
const totalViews = computed(() => allLoadedAds.value.reduce((sum, ad) => sum + (ad.views || 0), 0))

const isFiltered = computed(() => {
    return filters.value.global ||
        filters.value.category ||
        filters.value.brand ||
        filters.value.min_price ||
        filters.value.max_price ||
        sort.value !== 'newest'
})

// ==================== METHODS ====================
const setStatusFilter = (status: string) => {
    activeStatusTab.value = status
    filters.value.status = status === 'all' ? '' : status
    applyFilters()
}

const getStatusCount = (status: string) => {
    if (status === 'all') return totalAds.value
    return allLoadedAds.value.filter(ad => ad.status === status).length
}

const toggleAdStatus = async (ad: any) => {
    const newStatus = ad.status === 'active' ? 'inactive' : 'active'
    await updateAdStatus(ad.id, newStatus)
}

const reactivateSoldAd = async (ad: any) => {
    await updateAdStatus(ad.id, 'active')
}

const updateAdStatus = async (adId: number, newStatus: string) => {
    router.patch(route('user.ads.status', { ad: adId }), { status: newStatus }, {
        preserveScroll: true,
        onSuccess: () => {
            const adIndex = allLoadedAds.value.findIndex(a => a.id === adId)
            if (adIndex !== -1) {
                allLoadedAds.value[adIndex].status = newStatus
            }
        }
    })
}

const formatPrice = (price: number | string) => {
    if (!price) return 'Any'
    const num = typeof price === 'string' ? parseFloat(price) : price
    if (num >= 100000) {
        return 'Rs. ' + (num / 100000).toFixed(1) + 'L'
    } else if (num >= 1000) {
        return 'Rs. ' + (num / 1000).toFixed(1) + 'K'
    }
    return 'Rs. ' + num.toString()
}

const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const getCategoryName = (id: string) => {
    const category = props.categories?.find(c => c.id == id)
    return category?.name || id
}

const getBrandName = (id: string) => {
    const brand = props.brands?.find(b => b.id == id)
    return brand?.name || id
}

const getSortLabel = (sortValue: string) => {
    const labels: Record<string, string> = {
        'newest': 'Newest First',
        'price_low': 'Price: Low to High',
        'price_high': 'Price: High to Low'
    }
    return labels[sortValue] || sortValue
}

const truncateText = (text: string, maxLength: number) => {
    if (!text) return ''
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
}

const handleCategoryChange = () => {
    filters.value.brand = ''
}

const setupObserver = () => {
    if (observer) observer.disconnect()
    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !loading.value && hasMorePages.value) {
            loadMore()
        }
    }, { threshold: 0.1, rootMargin: '100px' })
    if (loadMoreTrigger.value) observer.observe(loadMoreTrigger.value)
}

const loadMore = () => {
    if (loading.value || !hasMorePages.value) return
    const nextPage = currentPage.value + 1
    if (nextPage > totalPages.value) return

    loading.value = true
    router.get(route('user.ads'), {
        page: nextPage,
        filter: {
            global: filters.value.global,
            category: filters.value.category,
            brand: filters.value.brand,
            min_price: filters.value.min_price,
            max_price: filters.value.max_price,
            status: filters.value.status
        },
        sort: sort.value
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['ads'],
        onSuccess: () => {
            loading.value = false
            setTimeout(setupObserver, 100)
        },
        onError: () => {
            loading.value = false
        }
    })
}

const applyFilters = () => {
    allLoadedAds.value = []
    currentPage.value = 1
    loading.value = true

    router.get(route('user.ads'), {
        filter: {
            global: filters.value.global,
            category: filters.value.category,
            brand: filters.value.brand,
            min_price: filters.value.min_price,
            max_price: filters.value.max_price,
            status: filters.value.status
        },
        sort: sort.value,
        page: 1
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false
        },
        onError: () => {
            loading.value = false
        }
    })
}

const resetFilters = () => {
    filters.value = {
        global: '',
        category: '',
        brand: '',
        min_price: '',
        max_price: '',
        status: ''
    }
    sort.value = 'newest'
    applyFilters()
}

const editAd = (adId: number) => {
    router.get(route('user.ads.edit', { id: adId }))
}

const viewAd = (adId: number) => {
    router.get(route('ads.show', { id: adId }))
}

const alert = useShadcnAlert()
const confirmDeleteAd = async (ad: any) => {
    const confirmed = await alert.show({
        type: 'destructive',
        title: 'Delete Ad',
        description: `Are you sure you want to delete "${ad.ad_title}"? This action cannot be undone.`,
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel'
    })
    if (confirmed) {
        router.delete(route('ads.destroy', { id: ad.id }), {
            preserveScroll: true,
            onSuccess: () => {
                const index = allLoadedAds.value.findIndex(a => a.id === ad.id)
                if (index !== -1) {
                    allLoadedAds.value.splice(index, 1)
                    totalAds.value--
                }
            }
        })
    }
}

// ==================== WATCHERS (continued) ====================
const debouncedApplyFilters = debounce(() => {
    applyFilters()
}, 500)

watch(() => filters.value.global, () => {
    debouncedApplyFilters()
})

watch([() => filters.value.category, () => filters.value.brand, () => filters.value.min_price, () => filters.value.max_price, sort], () => {
    applyFilters()
})

watch(allLoadedAds, (newAds) => {
    if (hasMorePages.value && newAds.length > 0) {
        setTimeout(setupObserver, 100)
    }
}, { deep: true })

// ==================== LIFECYCLE ====================
onMounted(() => {
    setTimeout(setupObserver, 100)
})

onUnmounted(() => {
    if (observer) observer.disconnect()
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (hover: none) and (pointer: coarse) {
    .group .absolute {
        opacity: 1 !important;
        background: rgba(0, 0, 0, 0.6);
    }
}
</style>