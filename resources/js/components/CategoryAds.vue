<template>
    <div v-if="category.ads && category.ads.length > 0" class="py-6 sm:py-8">
        <!-- Category Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2 sm:gap-4">
            <div class="flex-1">
                <h2 class="text-base sm:text-lg font-semibold" :class="theme.text">
                    {{ category.name }}
                    <span class="text-[10px] sm:text-xs text-gray-500 ml-1">({{ filteredAds.length }})</span>
                </h2>
            </div>
            <div class="flex items-center gap-1.5">
                <button @click="navigateToCategory(category)"
                    class="inline-flex items-center text-[10px] sm:text-xs font-medium transition-colors px-2 py-1 sm:px-2.5 sm:py-1.5 rounded-md shadow-sm"
                    :class="theme.button">
                    View All
                    <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- No Results -->
        <div v-if="filteredAds.length === 0" class="text-center py-6 sm:py-8 text-gray-500 text-xs sm:text-sm">
            No ads found in "{{ category.name }}" matching "{{ localSearch }}"
        </div>

        <!-- Desktop/Tablet Grid (hidden on mobile) -->
        <div v-else class="hidden sm:grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            <AdCard v-for="ad in visibleAds" :key="ad.id" :ad="ad" :size="'normal'" />
        </div>

        <!-- Mobile Carousel (visible only on mobile) -->
        <div class="block sm:hidden overflow-x-auto overflow-y-hidden pb-2 -mx-4 px-4 scrollbar-hide">
            <div class="flex flex-nowrap gap-3">
                <div v-for="ad in filteredAds" :key="ad.id" class="w-[260px] flex-shrink-0">
                    <AdCard :ad="ad" :size="'small'" />
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div v-if="filteredAds.length > 0" class="mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-gray-100"></div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdCard from '@/components/AdCard.vue'
import { useTheme } from '@/composables/useTheme'
const { theme } = useTheme()

const props = defineProps({
    category: Object,
    searchTerm: String
})

const navigateToCategory = (category) => {
    if (category.slug) {
        router.get(route('category.show', { slug: category.slug }))
    }
}

const showAll = ref(false)
const localSearch = ref(props.searchTerm || '')

const filteredAds = computed(() => {
    if (!localSearch.value.trim()) return props.category.ads || []
    const search = localSearch.value.toLowerCase().trim()
    return (props.category.ads || []).filter(ad =>
        ad.title?.toLowerCase().includes(search) ||
        ad.description?.toLowerCase().includes(search)
    )
})

const visibleAds = computed(() => showAll.value ? filteredAds.value : filteredAds.value.slice(0, 4))

watch(() => props.searchTerm, (newSearch) => {
    localSearch.value = newSearch || ''
})
</script>

<style scoped>
/* Hide scrollbar on mobile carousel (optional) */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>