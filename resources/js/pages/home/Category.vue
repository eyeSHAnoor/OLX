<template>
    <OlxLayout>
        <div>

            <section class="max-w-9/11 mx-auto px-3 sm:px-4 py-6 md:py-8">
                <!-- Mobile Filter Toggle -->
                <div class="lg:hidden mb-4">
                    <button @click="showMobileFilters = !showMobileFilters"
                        class="w-full flex items-center justify-between bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:border-brand-orange transition-colors">
                        <span class="font-medium text-gray-800 flex items-center gap-2">
                            <Icon icon="mdi:filter-outline" class="text-xl" :style="{ color: 'var(--brand-orange)' }" />
                            Filters
                            <span v-if="activeFilterCount > 0"
                                class="ml-2 bg-brand-blue text-white text-xs px-2 py-0.5 rounded-full">
                                {{ activeFilterCount }}
                            </span>
                        </span>
                        <Icon icon="mdi:chevron-down" class="text-xl text-gray-500"
                            :class="{ 'rotate-180': showMobileFilters }" />
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
                    <!-- Sidebar - Filters -->
                    <aside class="lg:col-span-1 space-y-6"
                        :class="showMobileFilters ? 'block mobile-filter-sidebar' : 'hidden lg:block'">

                        <!-- Close button for mobile -->
                        <div class="lg:hidden flex items-center justify-between mb-4">
                            <h2 class="font-bold text-lg">Filters</h2>
                            <button @click="showMobileFilters = false" class="p-2 hover:bg-gray-100 rounded-lg">
                                <Icon icon="mdi:close" class="text-2xl" />
                            </button>
                        </div>

                        <!-- Categories Filter -->
                        <div class=" rounded-xl shadow-sm p-5 md:p-6">
                            <div class="flex items-center gap-2 text-sm text-gray-600 py-6 px-4 ">
                                <Link :href="route('home')" class="hover:text-brand-orange transition-colors">Home
                                </Link>
                                <Icon icon="mdi:chevron-right" class="text-gray-400" />
                                <span v-if="category" class="text-gray-900 font-medium">{{ category.name }}</span>
                                <span v-else class="text-gray-900 font-medium">All Categories</span>
                            </div>
                            <div class="bg-white rounded-xl shadow-sm p-5 md:p-6">
                                <h3
                                    class="font-semibold text-base md:text-lg text-gray-800 mb-4 flex items-center gap-2">
                                    <Icon icon="mdi:folder-outline" :style="{ color: 'var(--brand-teal)' }" />
                                    Categories
                                </h3>
                                <div class="space-y-1">
                                    <Link :href="route('category.show')"
                                        class="block text-sm py-2 px-3 rounded-lg transition-all duration-200"
                                        :class="[!category ? 'bg-brand-blue/10 text-brand-blue font-medium border-l-4 border-brand-blue' : 'hover:bg-gray-50 hover:pl-4']">
                                        All Categories
                                    </Link>

                                    <!-- Display limited or all categories based on showAllCategories -->
                                    <template v-for="(cat, index) in displayedCategories" :key="cat.id">
                                        <div v-if="!cat.parent_id" class="space-y-1">
                                            <Link :href="route('category.show', cat.slug)"
                                                class="block text-sm py-2 px-3 rounded-lg transition-all duration-200 font-medium"
                                                :class="[category?.id === cat.id ? 'bg-brand-blue/10 text-brand-blue border-l-4 border-brand-blue' : 'hover:bg-gray-50 hover:pl-4']">
                                                {{ cat.name }}
                                            </Link>

                                            <!-- Subcategories -->
                                            <div v-if="cat.children_recursive?.length" class="ml-4 space-y-1">
                                                <Link v-for="subCat in cat.children_recursive" :key="subCat.id"
                                                    :href="route('category.show', subCat.slug)"
                                                    class="block text-sm py-1.5 px-3 rounded-lg transition-all duration-200"
                                                    :class="[category?.id === subCat.id ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900']">
                                                    {{ subCat.name }}

                                                </Link>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- View More / View Less button -->
                                    <button v-if="topLevelCategories.length > initialCategoriesToShow"
                                        @click="toggleCategoriesView"
                                        class="w-full mt-3 text-sm text-brand-orange hover:text-brand-orange/80 font-medium flex items-center justify-center gap-1 py-2 border-t border-gray-100">
                                        <Icon :icon="showAllCategories ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                                            class="text-lg" />
                                        {{ showAllCategories ? 'View Less Categories' : `View More Categories
                                        (${topLevelCategories.length -
                                            initialCategoriesToShow})` }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Brand Filter -->
                        <div class="bg-white rounded-xl shadow-sm p-5 md:p-6" v-if="brands.length">
                            <h3 class="font-semibold text-base md:text-lg text-gray-800 mb-4 flex items-center gap-2">
                                <Icon icon="mdi:tag-outline" :style="{ color: 'var(--brand-teal)' }" />
                                Brands
                            </h3>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                <label v-for="brand in brands" :key="brand.id"
                                    class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="checkbox" :value="brand.id" v-model="selectedBrands"
                                        @change="applyBrandFilter"
                                        class="w-4 h-4 rounded border-gray-300 text-brand-orange focus:ring-brand-orange">
                                    <span class="text-sm text-gray-700 flex-1">{{ brand.name }}</span>
                                    <span class="text-xs text-gray-500">{{ getBrandAdCount(brand.id) }}</span>
                                </label>
                            </div>
                        </div>

                        <!-- Price Filter -->
                        <div class="bg-white rounded-xl shadow-sm p-5 md:p-6">
                            <h3 class="font-semibold text-base md:text-lg text-gray-800 mb-4 flex items-center gap-2">
                                <Icon icon="mdi:currency-usd" :style="{ color: 'var(--brand-teal)' }" />
                                Price Range
                            </h3>
                            <div class="space-y-4">
                                <!-- Price slider preview -->
                                <div class="flex items-center justify-between text-sm text-gray-600">
                                    <span>Min: ${{ minPrice || priceRange?.min || 0 }}</span>
                                    <span>Max: ${{ maxPrice || priceRange?.max || 10000 }}</span>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Minimum</label>
                                        <input type="number" placeholder="$ Min" v-model.number="minPrice"
                                            @input="debouncedApplyPriceFilter"
                                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-orange focus:border-brand-orange outline-none transition text-sm" />
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Maximum</label>
                                        <input type="number" placeholder="$ Max" v-model.number="maxPrice"
                                            @input="debouncedApplyPriceFilter"
                                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-orange focus:border-brand-orange outline-none transition text-sm" />
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
                            </div>
                        </div>

                        <!-- Location Filter -->
                        <div class="bg-white rounded-xl shadow-sm p-5 md:p-6">
                            <h3 class="font-semibold text-base md:text-lg text-gray-800 mb-4 flex items-center gap-2">
                                <Icon icon="mdi:map-marker-outline" :style="{ color: 'var(--brand-teal)' }" />
                                Location
                            </h3>
                            <div class="relative">
                                <select v-model="selectedCity" @change="applyCityFilter"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-orange focus:border-brand-orange outline-none appearance-none bg-white">
                                    <option value="Pakistan">All Pakistan</option>
                                    <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
                                </select>
                                <Icon icon="mdi:chevron-down"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" />
                            </div>
                        </div>

                        <!-- Active Filters Summary -->
                        <div v-if="activeFilterCount > 0" class="bg-brand-blue/5 rounded-xl p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Active Filters:</h4>
                            <div class="flex flex-wrap gap-2">
                                <span v-if="selectedBrands.length"
                                    class="inline-flex items-center gap-1 bg-white text-xs px-3 py-1.5 rounded-full shadow-sm">
                                    {{ selectedBrands.length }} brands
                                    <button @click="clearBrandFilter" class="ml-1 hover:text-brand-blue">×</button>
                                </span>
                                <span v-if="minPrice || maxPrice"
                                    class="inline-flex items-center gap-1 bg-white text-xs px-3 py-1.5 rounded-full shadow-sm">
                                    ${{ minPrice || 0 }} - ${{ maxPrice || '∞' }}
                                    <button @click="clearPriceFilter" class="ml-1 hover:text-brand-orange">×</button>
                                </span>
                                <span v-if="selectedCity !== 'Pakistan'"
                                    class="inline-flex items-center gap-1 bg-white text-xs px-3 py-1.5 rounded-full shadow-sm">
                                    {{ selectedCity }}
                                    <button @click="resetCityFilter" class="ml-1 hover:text-brand-orange">×</button>
                                </span>
                            </div>

                            <button @click="resetAllFilters"
                                class="mt-3 text-sm text-brand-orange hover:text-brand-orange/80 font-medium">
                                Clear all filters
                            </button>
                        </div>

                        <!-- Mobile Filter Actions -->
                        <div
                            class="lg:hidden bg-white rounded-xl shadow-sm p-4 border-t border-gray-100 sticky bottom-0">
                            <div class="grid grid-cols-2 gap-3">
                                <button @click="resetAllFilters"
                                    class="py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-sm">
                                    Reset All
                                </button>
                                <button @click="applyFilters"
                                    class="py-3 text-white font-medium rounded-lg transition-colors text-sm shadow-md"
                                    :style="{ background: 'linear-gradient(135deg, var(--brand-orange), var(--brand-teal))' }">
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
                                        {{ category?.name || 'All Categories' }}
                                    </h1>
                                    <p class="text-gray-600 text-sm md:text-base flex items-center gap-2">
                                        <span>{{ filteredAds.length }} ads found</span>
                                        <span v-if="selectedCity !== 'Pakistan'" class="text-brand-teal">• in {{
                                            selectedCity }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Toolbar -->
                        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center justify-between sm:justify-start">
                                    <div class="flex items-center space-x-1 md:space-x-2">
                                        <button @click="viewMode = 'grid'"
                                            :class="viewMode === 'grid' ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-400 hover:text-gray-600'"
                                            class="p-2 rounded-lg transition-all duration-200">
                                            <Icon icon="mdi:grid-large" class="w-5 h-5" />
                                        </button>
                                        <button @click="viewMode = 'list'"
                                            :class="viewMode === 'list' ? 'bg-brand-blue/10 text-brand-blue' : 'text-gray-400 hover:text-gray-600'"
                                            class="p-2 rounded-lg transition-all duration-200">
                                            <Icon icon="mdi:format-list-bulleted" class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Sort Dropdown -->
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-600 text-sm hidden md:block">Sort by:</span>
                                    <select v-model="sortBy" @change="applySort"
                                        class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-orange focus:border-brand-orange outline-none transition min-w-[160px] text-sm">
                                        <option value="newest">Newest First</option>
                                        <option value="price_low">Price: Low to High</option>
                                        <option value="price_high">Price: High to Low</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Results Grid/List -->
                        <template v-if="filteredAds.length">
                            <div v-if="viewMode === 'grid'"
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
                                <AdCard v-for="ad in paginatedAds" :key="ad.id" :ad="ad" />
                            </div>

                            <div v-if="viewMode === 'list'" class="space-y-3 md:space-y-4">
                                <AdListItem v-for="ad in paginatedAds" :key="ad.id" :ad="ad" />
                            </div>

                            <!-- Pagination -->
                            <div v-if="totalPages > 1" class="mt-8 flex justify-center">
                                <div class="flex items-center gap-2">
                                    <button @click="currentPage--" :disabled="currentPage === 1"
                                        class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <Icon icon="mdi:chevron-left" />
                                    </button>

                                    <button v-for="page in displayedPages" :key="page" @click="currentPage = page"
                                        class="w-10 h-10 rounded-lg transition-colors"
                                        :class="[currentPage === page ? 'text-white' : 'hover:bg-gray-50']"
                                        :style="currentPage === page ? { background: 'var(--brand-blue)' } : {}">
                                        {{ page }}
                                    </button>

                                    <button @click="currentPage++" :disabled="currentPage === totalPages"
                                        class="w-10 h-10 flex items-center justify-center rounded-lg border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                        <Icon icon="mdi:chevron-right" />
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- No Results -->
                        <div v-else class="text-center py-12 md:py-16 bg-white rounded-xl shadow-sm">
                            <div class="max-w-md mx-auto px-4">
                                <Icon icon="mdi:package-variant-closed" class="text-6xl text-gray-300 mx-auto mb-4" />
                                <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-3">No ads found</h3>
                                <p class="text-gray-600 text-sm md:text-base mb-6 md:mb-8">
                                    Try adjusting your filters or browse other categories
                                </p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <button @click="resetAllFilters"
                                        class="px-6 md:px-8 py-2.5 md:py-3 text-white font-medium rounded-lg transition-colors duration-200 text-sm md:text-base shadow-md bg-brand-blue">
                                        Clear Filters
                                    </button>
                                    <Link :href="route('home')"
                                        class="px-6 md:px-8 py-2.5 md:py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-sm md:text-base">
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

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import OlxLayout from '@/layouts/OlxLayout.vue'
import AdCard from '@/components/AdCard.vue'
import AdListItem from '@/components/AdListItem.vue'
import debounce from 'lodash/debounce'
import citiesList from '@/data/cities.json'

const props = defineProps<{
    category?: any
    categories: any[]
    brands: any[]
    allBrands?: any[]
    filters: any
    priceRange?: { min: number; max: number }
}>()

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

useForceTheme('light');

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

// Initialize selected brands from URL if present
onMounted(() => {
    if (props.filters?.filter?.brand) {
        selectedBrands.value = props.filters.filter.brand.split(',').map(Number)
    }
})
</script>

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

/* Custom scrollbar for filter sidebar */
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