<template>
    <OlxLayout>
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

            <!-- Navigation buttons (optional, keep as is) -->
            <button v-if="homepageBanners.length > 1" @click="prevSlide"
                class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-white/90 rounded-full p-3">
                <Icon icon="mdi:chevron-left" class="text-2xl" />
            </button>
            <button v-if="homepageBanners.length > 1" @click="nextSlide"
                class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-white/90 rounded-full p-3">
                <Icon icon="mdi:chevron-right" class="text-2xl" />
            </button>

            <!-- Dots -->
            <div v-if="homepageBanners.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                <button v-for="(_, index) in homepageBanners" :key="index" @click="currentSlide = index"
                    class="h-2 rounded-full transition-all"
                    :class="currentSlide === index ? 'w-8 bg-yellow-500' : 'w-2 bg-white/70'">
                </button>
            </div>
        </section>

        <!-- MAIN CONTENT -->
        <template v-if="!isSearching">
            <!-- Categories -->
            <section class="py-12 bg-gray-50">
                <div class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3 ">
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

            <!-- Promo Banners -->
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

            <!-- Category Ads -->
            <section class="max-w-full md:max-w-8/10 mx-auto px-4 md:px-3  space-y-12 pb-20">
                <CategoryAds v-for="cat in topCategories" :key="cat.id" :category="cat" />
            </section>

            <!-- Bottom Banner -->
            <section v-if="bottomBanner" class="relative h-64 md:h-80 overflow-hidden">
                <a :href="bottomBanner.link || '#'" class="block w-full h-full">
                    <img :src="bottomBanner.image_url" :alt="bottomBanner.title" class="w-full h-full object-cover" />
                </a>
            </section>
        </template>

        <!-- SEARCH RESULTS -->
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
import OlxLayout from '@/layouts/OlxLayout.vue'
import { usePage, router } from '@inertiajs/vue3';
import CategoryAds from '@/components/CategoryAds.vue'
import SearchResults from './_partials/SearchResults.vue';
import { Icon } from '@iconify/vue';
import { useForceTheme } from '@/composables/useForceTheme'

const page = usePage();

console.log('Page Props:', page.props);
const categories = computed(() => page.props.categories || [])
const banners = computed(() => page.props.banners || [])
const isSearching = computed(() => page.props.isSearching || false)

// Carousel
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

// Auto-play
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

// Form
const form = ref({ filter: { global: '' } });

// Top categories
const topCategories = computed(() => categories.value.filter(c => !c.parent_id).slice(0, 7));

// Navigation
const navigateToCategory = (category: any) => {
    if (category.slug) {
        router.get(route('category.show', { slug: category.slug }));
    }
};

const goBack = () => {
    router.get(route('home'));
};

// Lifecycle
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
</script>