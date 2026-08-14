<template>
    <OlxLayout>
        <TopCategoriesBar />
        <div class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3 py-4 md:py-6" :class="theme.bg">
            <div class="pb-2 sm:hidden visible">
                <button @click="goBack"
                    class="inline-flex items-center gap-1 px-3 py-2 rounded-md border text-sm transition"
                    :class="[theme.card, theme.border, theme.text, theme.hover]">
                    <Icon icon="mdi:arrow-left" class="text-base" />
                    Back
                </button>
            </div>
            <!-- Header Section -->
            <div class="mb-6 md:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold" :class="theme.text">My Favorite Ads</h1>
                        <p class="text-sm mt-1" :class="theme.textMuted">View and manage your saved listings</p>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="rounded-xl shadow-sm border mb-6" :class="[theme.card, theme.border]">
                <div class="p-4 md:p-5">
                    <!-- Search and Filter Row -->
                    <div class="flex flex-col gap-2">
                        <!-- Search -->
                        <div class="w-full">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5" :class="theme.textMuted" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>

                                <input v-model="filters.global" type="text"
                                    placeholder="Search by title, description..."
                                    class="w-full pl-10 pr-3 py-2.5 border rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm sm:text-xs"
                                    :class="theme.input" />
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="flex flex-wrap gap-2 items-center">
                            <!-- Category -->
                            <SelectInput v-model="filters.category" class="flex-1" placeholder="Categories">
                                <SelectContent>
                                    <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </SelectItem>
                                </SelectContent>
                            </SelectInput>

                            <!-- Brand -->
                            <SelectInput v-model="filters.brand" class="flex-1 sm:w-1/2" placeholder="Brands">
                                <SelectContent>
                                    <SelectItem v-for="brand in brands" :key="brand.id" :value="brand.id">
                                        {{ brand.name }}
                                    </SelectItem>
                                </SelectContent>
                            </SelectInput>

                            <!-- Sort -->
                            <SelectInput v-model="sort" class="flex-1 min-w-[100px] sm:flex-none sm:min-w-[120px]">
                                <SelectContent>
                                    <SelectItem value="newest">Newest First</SelectItem>
                                    <SelectItem value="price_low">Price: Low to High</SelectItem>
                                    <SelectItem value="price_high">Price: High to Low</SelectItem>
                                </SelectContent>
                            </SelectInput>

                            <!-- Apply -->
                            <button @click="applyFilters"
                                class="flex-1 sm:flex-none px-3 py-2 text-white rounded-lg transition text-sm sm:text-xs"
                                :class="theme.button">
                                Apply
                            </button>

                            <!-- Reset -->
                            <button v-if="isFiltered" @click="resetFilters"
                                class="flex-1 sm:flex-none px-3 py-2 border rounded-lg transition text-sm sm:text-xs"
                                :class="[theme.buttonOutline, theme.border]">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Active Filters Tags -->
                    <div v-if="isFiltered" class="flex flex-wrap gap-2 mt-3 pt-3 border-t" :class="theme.border">
                        <span v-if="filters.global"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs sm:text-[10px]"
                            :class="theme.badge">
                            Search: "{{ truncateText(filters.global, 30) }}"
                            <button @click="filters.global = ''; applyFilters()" class="hover:opacity-80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="filters.category"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs sm:text-[10px]"
                            :class="theme.badge">
                            Category: {{ getCategoryName(filters.category) }}
                            <button @click="filters.category = ''; applyFilters()" class="hover:opacity-80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="filters.brand"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs sm:text-[10px]"
                            :class="theme.badge">
                            Brand: {{ getBrandName(filters.brand) }}
                            <button @click="filters.brand = ''; applyFilters()" class="hover:opacity-80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="filters.min_price || filters.max_price"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs sm:text-[10px]"
                            :class="theme.badge">
                            Price: {{ formatPrice(filters.min_price) }} - {{ formatPrice(filters.max_price) }}
                            <button @click="filters.min_price = ''; filters.max_price = ''; applyFilters()"
                                class="hover:opacity-80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="sort !== 'newest'"
                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs sm:text-[10px]"
                            :class="theme.badge">
                            Sort: {{ getSortLabel(sort) }}
                            <button @click="sort = 'newest'; applyFilters()" class="hover:opacity-80">
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
            <div class="mb-4 text-sm" :class="theme.textMuted">
                Showing {{ allLoadedAds.length }} of {{ totalAds }} favorite ads
            </div>

            <!-- Loading Spinner -->
            <div v-if="loading && allLoadedAds.length === 0" class="text-center py-12">
                <svg class="animate-spin w-10 h-10 mx-auto mb-3" :class="theme.icon" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="text-sm" :class="theme.textMuted">Loading favorites...</p>
            </div>

            <!-- Favorite Ads Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5">
                <AdCard v-for="ad in allLoadedAds" :key="ad.id" :ad="ad" @favorite-removed="handleFavoriteRemoved" />
            </div>

            <!-- Loading indicator for infinite scroll -->
            <div v-if="loading && allLoadedAds.length > 0" class="text-center py-6">
                <div class="inline-flex items-center gap-2" :class="theme.textMuted">
                    <svg class="animate-spin h-5 w-5" :class="theme.icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-sm">Loading more favorites...</span>
                </div>
            </div>

            <!-- Infinite scroll trigger -->
            <div ref="loadMoreTrigger" v-if="hasMorePages && !loading && allLoadedAds.length > 0" class="h-10"></div>

            <!-- No more items message -->
            <div v-if="!hasMorePages && allLoadedAds.length > 0 && allLoadedAds.length === totalAds"
                class="text-center py-6">
                <p class="text-sm" :class="theme.textMuted">You've seen all {{ totalAds }} favorite ads</p>
            </div>

            <!-- No Favorites State -->
            <div v-if="allLoadedAds.length === 0 && !loading" class="text-center py-12 rounded-xl shadow-sm border"
                :class="[theme.card, theme.border]">
                <svg class="w-16 h-16 mx-auto mb-4" :class="theme.textMuted" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <h3 class="text-lg font-semibold mb-1" :class="theme.text">No favorite ads yet</h3>
                <p class="text-sm mb-4" :class="theme.textMuted">Start exploring and save ads you're interested in</p>
                <Link :href="route('home')"
                    class="inline-flex items-center gap-2 text-white px-4 py-2 rounded-lg transition text-sm font-medium"
                    :class="theme.button">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Browse Ads
                </Link>
            </div>
        </div>
    </OlxLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import AdCard from '@/components/AdCard.vue'
import axios from 'axios'
import debounce from 'lodash/debounce'
import { Icon } from '@iconify/vue'
import TopCategoriesBar from '@/components/TopCategoriesBar.vue'
import { useTheme } from '@/composables/useTheme'

// Theme
const { theme } = useTheme()

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
        filter: {
            global: string | null
            category: string | null
            brand: string | null
            min_price: string | null
            max_price: string | null
        }
        sort: string
    }
    priceRange: {
        min: number
        max: number
    }
}

const props = defineProps<Props>()

// Store all loaded favorites (accumulated from all pages)
const allLoadedAds = ref<any[]>([])
const currentPage = ref(1)
const totalPages = ref(1)
const totalAds = ref(0)
const loading = ref(false)

// Update all loaded favorites when initial data arrives
watch(() => props.favoriteAds, (newData) => {
    if (newData) {
        if (newData.current_page === 1) {
            allLoadedAds.value = [...newData.data]
        } else {
            allLoadedAds.value = [...allLoadedAds.value, ...newData.data]
        }
        currentPage.value = newData.current_page
        totalPages.value = newData.last_page
        totalAds.value = newData.total
        loading.value = false
    }
}, { immediate: true, deep: true })

const hasMorePages = computed(() => currentPage.value < totalPages.value)

// Filter states
const filters = ref({
    global: props.filters?.filter?.global || '',
    category: props.filters?.filter?.category || '',
    brand: props.filters?.filter?.brand || '',
    min_price: props.filters?.filter?.min_price || '',
    max_price: props.filters?.filter?.max_price || ''
})

const sort = ref(props.filters?.sort || 'newest')

// Infinite scroll states
const loadMoreTrigger = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

// Modal states
const showClearAllModal = ref(false)

// Check if any filters are applied
const isFiltered = computed(() => {
    return filters.value.global ||
        filters.value.category ||
        filters.value.brand ||
        filters.value.min_price ||
        filters.value.max_price ||
        sort.value !== 'newest'
})

// Helper functions
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

const formatPrice = (price: string | number) => {
    if (!price) return 'Any'
    const num = typeof price === 'string' ? parseFloat(price) : price
    if (num >= 100000) {
        return (num / 100000).toFixed(1) + 'L'
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K'
    }
    return num.toString()
}

// Handle favorite removed from AdCard
const handleFavoriteRemoved = (adId: number) => {
    const index = allLoadedAds.value.findIndex(ad => ad.id === adId)
    if (index !== -1) {
        allLoadedAds.value.splice(index, 1)
        totalAds.value--
    }
}

// Setup intersection observer for infinite scroll
const setupObserver = () => {
    if (observer) observer.disconnect()
    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !loading.value && hasMorePages.value) {
            loadMore()
        }
    }, { threshold: 0.1, rootMargin: '100px' })
    if (loadMoreTrigger.value) observer.observe(loadMoreTrigger.value)
}

// Load more favorites
const loadMore = () => {
    if (loading.value || !hasMorePages.value) return
    const nextPage = currentPage.value + 1
    if (nextPage > totalPages.value) return

    loading.value = true
    router.get(route('user.favorites'), {
        page: nextPage,
        filter: {
            global: filters.value.global,
            category: filters.value.category,
            brand: filters.value.brand,
            min_price: filters.value.min_price,
            max_price: filters.value.max_price
        },
        sort: sort.value
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['favoriteAds'],
        onSuccess: () => {
            loading.value = false
            setTimeout(setupObserver, 100)
        },
        onError: () => {
            loading.value = false
        }
    })
}

// Actions
const applyFilters = () => {
    allLoadedAds.value = []
    currentPage.value = 1
    loading.value = true

    router.get(route('user.favorites'), {
        filter: {
            global: filters.value.global,
            category: filters.value.category,
            brand: filters.value.brand,
            min_price: filters.value.min_price,
            max_price: filters.value.max_price
        },
        sort: sort.value,
        page: 1
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            // loading will be turned off in the watch on props.favoriteAds
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
        max_price: ''
    }
    sort.value = 'newest'
    applyFilters()
}

const confirmClearAll = () => {
    showClearAllModal.value = true
}

const clearAllFavorites = async () => {
    try {
        const response = await axios.delete(route('user.favorites.clear'))
        if (response.data.success) {
            allLoadedAds.value = []
            totalAds.value = 0
            showClearAllModal.value = false
        }
    } catch (error) {
        console.error('Error clearing favorites:', error)
    }
}

// Debounce search
const debouncedApplyFilters = debounce(() => {
    applyFilters()
}, 500)

watch(() => filters.value.global, () => {
    debouncedApplyFilters()
})

// Watch other filters for immediate update
watch([() => filters.value.category, () => filters.value.brand, () => filters.value.min_price, () => filters.value.max_price, sort], () => {
    applyFilters()
})

// Watch for ads changes to re-setup observer
watch(allLoadedAds, (newAds) => {
    if (hasMorePages.value && newAds.length > 0) {
        setTimeout(setupObserver, 100)
    }
}, { deep: true })

// Setup observer on mount
onMounted(() => {
    setTimeout(setupObserver, 100)
})

onUnmounted(() => {
    if (observer) observer.disconnect()
})

const goBack = () => {
    router.visit(route('account'), {
        preserveState: true,
        preserveScroll: true
    })
}
</script>