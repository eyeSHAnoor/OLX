<template>
    <div v-if="category.ads && category.ads.length > 0" class="my-10">

        <!-- Category Header + Mini Search -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <!-- Category Name -->
            <div class="flex-1">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">
                    {{ category.name }}
                    <span class="text-sm text-gray-500">({{ filteredAds.length }})</span>
                </h2>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-2 md:gap-3">
                <!-- Show More/Less Toggle -->
                <button v-if="filteredAds.length > 4" @click="showAll = !showAll"
                    class="flex items-center text-sm font-medium text-gray-600 hover:text-yellow-500 transition-colors px-3 py-2 rounded-lg hover:bg-yellow-50">
                    {{ showAll ? 'Show Less' : `Show ${filteredAds.length - 4} More` }}
                    <svg class="w-4 h-4 ml-1 transition-transform" :class="showAll ? 'rotate-180' : ''" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- View All Button -->
                <button @click="navigateToCategory(category)"
                    class="flex items-center text-sm sm:text-base font-semibold text-white bg-brand-blue hover:bg-brand-teal transition-colors px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg shadow-sm hover:shadow">
                    View All
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- No Results Message -->
        <div v-if="filteredAds.length === 0" class="text-center py-10 text-gray-500">
            No ads found in "{{ category.name }}" matching "{{ localSearch }}"
        </div>

        <!-- Ads Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            <AdCard v-for="ad in visibleAds" :key="ad.id" :ad="ad" />
        </div>

        <!-- Divider -->
        <div v-if="filteredAds.length > 0" class="mt-8 pt-8 border-t border-gray-100"></div>
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
