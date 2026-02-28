<template>
    <div v-if="category.ads && category.ads.length > 0" class="my-6 sm:my-8">

        <!-- Category Header - Compact -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2 sm:gap-4">
            <!-- Category Name -->
            <div class="flex-1">
                <h2 class="text-base sm:text-lg font-semibold text-gray-800">
                    {{ category.name }}
                    <span class="text-[10px] sm:text-xs text-gray-500 ml-1">({{ filteredAds.length }})</span>
                </h2>
            </div>

            <!-- Action Buttons - Smaller -->
            <div class="flex items-center gap-1.5">
                <!-- View All Button -->
                <button @click="navigateToCategory(category)"
                    class="inline-flex items-center text-[10px] sm:text-xs font-medium text-white bg-brand-blue hover:bg-brand-teal transition-colors px-2 py-1 sm:px-2.5 sm:py-1.5 rounded-md shadow-sm">
                    View All
                    <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- No Results Message - Compact -->
        <div v-if="filteredAds.length === 0" class="text-center py-6 sm:py-8 text-gray-500 text-xs sm:text-sm">
            No ads found in "{{ category.name }}" matching "{{ localSearch }}"
        </div>

        <!-- Ads Grid - Tighter spacing -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            <AdCard v-for="ad in visibleAds" :key="ad.id" :ad="ad" />
        </div>

        <!-- Show More Button - Optional, if you want to see all ads in this section -->
        <!-- <div v-if="filteredAds.length > 4" class="flex justify-center mt-4 sm:mt-5">
            <button @click="showAll = !showAll"
                class="text-[10px] sm:text-xs font-medium text-brand-blue hover:text-brand-teal transition-colors px-3 py-1.5 bg-blue-50 hover:bg-blue-100 rounded-md">
                {{ showAll ? 'Show Less' : `Show ${filteredAds.length - 4} More` }}
            </button>
        </div> -->

        <!-- Divider - Lighter -->
        <div v-if="filteredAds.length > 0" class="mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-gray-100"></div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdCard from '@/components/AdCard.vue'

const props = defineProps({
    category: Object,
    searchTerm: String
})

// Navigate to category show page
const navigateToCategory = (category) => {
    if (category.slug) {
        router.get(route('category.show', { slug: category.slug }))
    }
}

const showAll = ref(false)
const localSearch = ref(props.searchTerm || '')

// Filter ads based on local search
const filteredAds = computed(() => {
    if (!localSearch.value.trim()) return props.category.ads || []

    const search = localSearch.value.toLowerCase().trim()
    return (props.category.ads || []).filter(ad =>
        ad.title?.toLowerCase().includes(search) ||
        ad.description?.toLowerCase().includes(search)
    )
})

const visibleAds = computed(() => showAll.value ? filteredAds.value : filteredAds.value.slice(0, 4))

// Navigate to search page with category + global filter
const viewCategoryAds = () => {
    router.get(route('home'), {
        filter: {
            category: props.category.id,
            global: localSearch.value || ''
        }
    }, {
        preserveState: true,
        preserveScroll: true
    })
}

// Watch for external search term changes
watch(() => props.searchTerm, (newSearch) => {
    localSearch.value = newSearch || ''
})
</script>