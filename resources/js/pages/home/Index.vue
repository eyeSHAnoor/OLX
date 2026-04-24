<template>
    <OlxLayout>
        <!-- SEO & Favicon -->

        <Head>
            <title>Marketplace - Buy & Sell New & Used Items</title>
            <meta name="description"
                content="Find great deals on new and used items in your city. Post free ads, buy and sell electronics, cars, furniture, and more." />
            <meta name="keywords" content="classifieds, buy sell, marketplace, used items, free ads" />
            <meta property="og:title" content="Marketplace - Best Local Deals" />
            <meta property="og:description" content="Find great deals on new and used items in your city." />
            <meta property="og:type" content="website" />
            <link rel="icon" type="image/x-icon" href="/favicon.ico" />
            <link rel="shortcut icon" href="/favicon.ico" />
        </Head>

        <TopCategoriesBar />

        <!-- HERO BANNER -->
        <section v-if="homepageBanners.length"
            class="relative bg-gray-100 h-[180px] md:h-[400px] lg:h-[500px] overflow-hidden">
            <div v-for="(banner, index) in homepageBanners" :key="banner.id"
                class="absolute inset-0 transition-opacity duration-700"
                :class="{ 'opacity-100 z-10': currentSlide === index, 'opacity-0': currentSlide !== index }">
                <a :href="banner.link || '#'" :target="banner.link ? '_blank' : '_self'" class="block w-full h-full">
                    <img :src="banner.image_url" :alt="banner.title" class="w-full h-full object-cover" />
                </a>
            </div>

            <button v-if="homepageBanners.length > 1" @click="prevSlide"
                class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-white/90 rounded-full p-3">
                <Icon icon="mdi:chevron-left" class="text-2xl" />
            </button>
            <button v-if="homepageBanners.length > 1" @click="nextSlide"
                class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-white/90 rounded-full p-3">
                <Icon icon="mdi:chevron-right" class="text-2xl" />
            </button>

            <div v-if="homepageBanners.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                <button v-for="(_, index) in homepageBanners" :key="index" @click="currentSlide = index"
                    class="h-2 rounded-full transition-all"
                    :class="currentSlide === index ? 'w-8 bg-yellow-500' : 'w-2 bg-white/70'">
                </button>
            </div>
        </section>

        <!-- ========== RECENTLY VIEWED SECTION (RESPONSIVE: GRID + CAROUSEL) ========== -->
        <section v-if="recentAds.length" class="py-8 bg-white border-b border-gray-100">
            <div class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold flex items-center gap-2">
                        <Icon icon="mdi:history" class="text-2xl text-yellow-500" />
                        Recently Viewed
                    </h2>
                    <!-- Uncomment if you have a "View All" page -->
                    <!-- <button @click="viewAllRecent" class="text-sm text-brand-blue hover:underline">
          View All
        </button> -->
                </div>

                <!-- Carousel Container (horizontal scroll) -->
                <div class="relative">
                    <!-- Optional: Navigation Buttons (uncomment if needed) -->
                    <button v-if="showPrevButton" @click="scrollPrev"
                        class="hidden md:flex absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white/80 rounded-full p-2 shadow-md hover:bg-white focus:outline-none"
                        :class="{ 'hidden': !canScrollLeft }">
                        <Icon icon="mdi:chevron-left" class="w-5 h-5" />
                    </button>
                    <button v-if="showNextButton" @click="scrollNext"
                        class="hidden md:flex absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white/80 rounded-full p-2 shadow-md hover:bg-white focus:outline-none"
                        :class="{ 'hidden': !canScrollRight }">
                        <Icon icon="mdi:chevron-right" class="w-5 h-5" />
                    </button>

                    <!-- Scrollable Track -->
                    <div ref="scrollContainer" class="overflow-x-auto overflow-y-hidden pb-4 -mx-4 px-4 scrollbar-hide"
                        style="scroll-behavior: smooth; scrollbar-width: none; -ms-overflow-style: none;">
                        <div class="flex flex-nowrap gap-4">
                            <!-- Each card takes width based on screen size: 
                 mobile: full width (w-full) but flex-shrink-0 makes it side-scrollable.
                 Better to use fixed widths: 
                 on sm: 2 items (50% minus gap), md: 3 items (33.333%), lg: 4 items (25%) -->
                            <div v-for="ad in recentAds" :key="ad.id"
                                class="flex-shrink-0 w-full sm:w-[calc(50%-0.5rem)] md:w-[calc(33.333%-0.666rem)] lg:w-[calc(25%-0.75rem)]">
                                <AdCard :ad="ad" :size="'normal'" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- MAIN CONTENT -->
        <template v-if="!isSearching">
            <section class="py-12 bg-gray-50">
                <div class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3">
                    <h2 class="text-lg md:text-xl font-semibold mb-8 text-center">Browse Categories</h2>
                    <div class="grid grid-cols-3 md:grid-cols-7 gap-4">
                        <div v-for="category in categories" :key="category.id" @click="navigateToCategory(category)"
                            class="flex flex-col items-center cursor-pointer group">
                            <div class="w-full aspect-square rounded-xl overflow-hidden bg-white">
                                <img v-if="category.files?.length" :src="category.files[0].file_url"
                                    class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                    <Icon icon="mdi:image-off" class="text-3xl" />
                                </div>
                            </div>
                            <span class="mt-2 text-xs md:text-sm font-medium text-center line-clamp-2">
                                {{ category.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="promotionalBanners.length" class="py-5">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a v-for="banner in promotionalBanners" :key="banner.id" :href="banner.link || '#'"
                            class="block overflow-hidden rounded-xl h-48 md:h-56">
                            <img :src="banner.image_url" :alt="banner.title" class="w-full h-full object-cover" />
                        </a>
                    </div>
                </div>
            </section>

            <section class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3 space-y-12 pb-20">
                <CategoryAds v-for="cat in topCategories" :key="cat.id" :category="cat" />
            </section>

            <section v-if="bottomBanner" class="relative h-64 md:h-80 overflow-hidden">
                <a :href="bottomBanner.link || '#'" class="block w-full h-full">
                    <img :src="bottomBanner.image_url" :alt="bottomBanner.title" class="w-full h-full object-cover" />
                </a>
            </section>
        </template>

        <template v-else>
            <div class="max-w-7xl mx-auto px-4 py-4">
                <button @click="goBack" class="flex items-center px-5 py-3 text-gray-700">
                    <Icon icon="mdi:arrow-left" class="text-4xl mr-2" />
                    Back to Home
                </button>
            </div>
            <SearchResults :search-term="form.filter.global" />
        </template>
    </OlxLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import OlxLayout from '@/layouts/OlxLayout.vue'
import CategoryAds from '@/components/CategoryAds.vue'
import SearchResults from './_partials/SearchResults.vue';
import AdCard from '@/components/AdCard.vue';   // <-- imported for recent ads
import { Icon } from '@iconify/vue';
import { useForceTheme } from '@/composables/useForceTheme'

const page = usePage();
const categories = computed(() => page.props.categories || [])
const banners = computed(() => page.props.banners || [])
const isSearching = computed(() => page.props.isSearching || false)
const recentAds = computed(() => page.props.recentAds || [])

// Hero carousel (unchanged)
const currentSlide = ref(0);
let interval: any;

const homepageBanners = computed(() => banners.value.filter(b => b.position === 'homepage'));
const promotionalBanners = computed(() => banners.value.filter(b => ['category', 'sidebar'].includes(b.position)).slice(0, 2));
const bottomBanner = computed(() => banners.value.find(b => b.position === 'floating'));

const nextSlide = () => {
    if (homepageBanners.value.length) {
        currentSlide.value = (currentSlide.value + 1) % homepageBanners.value.length;
    }
};

const prevSlide = () => {
    if (homepageBanners.value.length) {
        currentSlide.value = (currentSlide.value - 1 + homepageBanners.value.length) % homepageBanners.value.length;
    }
};

const startAutoPlay = () => {
    if (homepageBanners.value.length > 1) {
        stopAutoPlay();
        interval = setInterval(nextSlide, 5000);
    }
};

const stopAutoPlay = () => {
    if (interval) {
        clearInterval(interval);
        interval = null;
    }
};

const form = ref({ filter: { global: '' } });
const topCategories = computed(() => categories.value.filter(c => !c.parent_id).slice(0, 7));

const navigateToCategory = (category: any) => {
    if (category.slug) {
        router.get(route('category.show', { slug: category.slug }));
    }
};

const goBack = () => {
    router.get(route('home'));
};

onMounted(() => {
    useForceTheme('light');
    startAutoPlay();

    const carousel = document.querySelector('.relative.bg-gray-100');
    carousel?.addEventListener('mouseenter', stopAutoPlay);
    carousel?.addEventListener('mouseleave', startAutoPlay);
});

onUnmounted(() => {
    stopAutoPlay();
    const carousel = document.querySelector('.relative.bg-gray-100');
    carousel?.removeEventListener('mouseenter', stopAutoPlay);
    carousel?.removeEventListener('mouseleave', startAutoPlay);
});

const updateScrollButtons = () => {
    if (!scrollContainer.value) return
    const { scrollLeft, scrollWidth, clientWidth } = scrollContainer.value
    canScrollLeft.value = scrollLeft > 20
    canScrollRight.value = scrollLeft + clientWidth < scrollWidth - 20
}

// Optional navigation logic (if you uncomment the buttons)
const scrollContainer = ref(null)

// Optionally show/hide navigation buttons based on scroll position
const canScrollLeft = ref(false)
const canScrollRight = ref(false)

const scrollPrev = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({ left: -300, behavior: 'smooth' })
    }
}

const scrollNext = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({ left: 300, behavior: 'smooth' })
    }
}

// Watch scroll events to update button visibility
const handleScroll = () => {
    updateScrollButtons()
}

// Resize observer to recalc on window resize (optional)
const handleResize = () => {
    updateScrollButtons()
}

onMounted(() => {
    const container = scrollContainer.value
    if (container) {
        container.addEventListener('scroll', handleScroll)
        window.addEventListener('resize', handleResize)
        updateScrollButtons()
    }
})

onUnmounted(() => {
    const container = scrollContainer.value
    if (container) {
        container.removeEventListener('scroll', handleScroll)
        window.removeEventListener('resize', handleResize)
    }
})

// Optional: set true if you want navigation buttons always visible
const showPrevButton = true
const showNextButton = true
</script>

<style scoped>
/* Hide scrollbar on mobile carousel */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>