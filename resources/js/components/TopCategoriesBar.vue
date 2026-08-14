<template>
    <!-- TOP BAR -->
    <div class="w-full border-t border-b py-1 md:py-2 relative" :class="[theme.bg, theme.border]">
        <div
            class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3 py-1 md:py-2 flex items-center space-x-4 md:space-x-5 overflow-x-auto no-scrollbar text-base md:text-sm">

            <!-- ALL CATEGORIES BUTTON - Visible on all screens -->
            <div @click="toggleMega"
                class="font-semibold cursor-pointer whitespace-nowrap flex items-center gap-1 flex-shrink-0"
                :class="[theme.text, theme.hover]">
                All Categories
                <span class="hidden sm:inline">▾</span>
            </div>

            <!-- DESKTOP: TOP 5 CATEGORIES -->
            <div v-for="cat in topFive" :key="cat.id" @click="navigateToCategory(cat)"
                class="whitespace-nowrap cursor-pointer flex-shrink-0 hidden md:block transition-colors"
                :class="[theme.textMuted, theme.hover, theme.textAccent]">
                {{ cat.name }}
            </div>

            <!-- MOBILE/TABLET: Scrollable top categories -->
            <div v-for="cat in topCategories" :key="cat.id"
                class="whitespace-nowrap cursor-pointer flex-shrink-0 md:hidden text-sm transition-colors"
                :class="[theme.textMuted, theme.hover, theme.textAccent]" @click="navigateToCategory(cat)">
                {{ cat.name }}
            </div>
        </div>

        <!-- MEGA DROPDOWN -->
        <div v-if="showMega"
            class="fixed md:absolute inset-x-0 md:left-40 top-[56px] md:top-full w-full md:w-5/6 shadow-xl z-50 max-h-[80vh] overflow-y-auto"
            :class="[theme.bg, theme.shadow, theme.card]">
            <div
                class="max-w-7xl mx-auto px-4 md:px-6 py-4 md:py-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-8 text-base md:text-lg">

                <div v-for="cat in topCategories" :key="cat.id" class="pb-4 sm:pb-0"
                    :class="[theme.border, { 'border-b': true, 'sm:border-b-0': true }]">
                    <!-- ROOT CATEGORY -->
                    <h3 class="font-semibold mb-2 md:mb-3 cursor-pointer text-sm md:text-base transition-colors"
                        :class="[theme.text, theme.hover, theme.textAccent]" @click="navigateToCategory(cat)">
                        {{ cat.name }}
                    </h3>

                    <!-- SUB CATEGORIES -->
                    <ul class="space-y-1 md:space-y-2">
                        <li v-for="child in cat.children_recursive" :key="child.id"
                            class="text-xs md:text-sm cursor-pointer pl-2 md:pl-0 transition-colors"
                            :class="[theme.textMuted, theme.hover, theme.textAccent]"
                            @click="navigateToCategory(child)">
                            {{ child.name }}
                        </li>
                    </ul>
                </div>

            </div>

            <!-- CLOSE BUTTON FOR MOBILE -->
            <div class="block md:hidden p-4 border-t" :class="theme.border">
                <button @click="showMega = false"
                    class="w-full py-2 text-center rounded-lg text-sm font-medium transition-colors"
                    :class="[theme.card, theme.hover, theme.text]">
                    Close
                </button>
            </div>
        </div>

        <!-- OVERLAY FOR MOBILE -->
        <div v-if="showMega" @click="showMega = false" class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden">
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { useTheme } from '@/composables/useTheme'

const page = usePage()
const { theme } = useTheme()
const topCategories = page.props.topCategories || []

const showMega = ref(false)

const toggleMega = () => {
    showMega.value = !showMega.value
    // Prevent body scroll when mega menu is open on mobile
    if (showMega.value && window.innerWidth < 768) {
        document.body.style.overflow = 'hidden'
    } else {
        document.body.style.overflow = 'auto'
    }
}

// Close mega menu on escape key
const handleEscape = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
        showMega.value = false
        document.body.style.overflow = 'auto'
    }
}

// Close mega menu on window resize (optional)
const handleResize = () => {
    if (window.innerWidth >= 768) {
        showMega.value = false
        document.body.style.overflow = 'auto'
    }
}

// Top 5 categories for desktop top bar
const topFive = computed(() => topCategories.slice(0, 5))

// Navigate to category show page
const navigateToCategory = (category: any) => {
    if (category?.slug) {
        router.get(route('category.show', { slug: category.slug }), {}, {
            preserveScroll: true,
            preserveState: false, // Set to false to load fresh category data
        })
    } else {
        // Fallback to home if no slug
        router.get(route('home'))
    }
    showMega.value = false
    document.body.style.overflow = 'auto'
}

// Cleanup
onMounted(() => {
    window.addEventListener('keydown', handleEscape)
    window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleEscape)
    window.removeEventListener('resize', handleResize)
    document.body.style.overflow = 'auto'
})
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}

.no-scrollbar {
    scrollbar-width: none;
}

/* Smooth transitions for dropdown */
.fixed {
    animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>