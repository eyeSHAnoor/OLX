<template>
    <OlxLayout>

        <!-- HERO BANNERS SECTION - Carousel for homepage banners -->
        <section v-if="homepageBanners.length > 0" class="relative bg-gray-100">
            <div class="relative h-[300px] md:h-[400px] lg:h-[500px] overflow-hidden">
                <!-- Banner Slides -->
                <div v-for="(banner, index) in homepageBanners" :key="banner.id"
                    class="absolute inset-0 transition-opacity duration-700 ease-in-out"
                    :class="{ 'opacity-100 z-10': currentSlide === index, 'opacity-0 z-0': currentSlide !== index }">

                    <!-- Banner Link -->
                    <a :href="banner.link || '#'" :target="banner.link ? '_blank' : '_self'"
                        class="block w-full h-full relative">
                        <img :src="banner.image_url" :alt="banner.title" class="w-full h-full object-cover" />

                        <!-- Overlay with gradient and text -->
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/40 to-transparent flex items-center">
                            <div class="container mx-auto px-4 md:px-8">
                                <div class="max-w-2xl text-white">
                                    <h2 v-if="banner.title"
                                        class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 animate-fade-in-up">
                                        {{ banner.title }}
                                    </h2>
                                    <p v-if="banner.description"
                                        class="text-lg md:text-xl mb-6 text-gray-200 animate-fade-in-up animation-delay-200">
                                        {{ banner.description }}
                                    </p>
                                    <span v-if="banner.link"
                                        class="inline-block bg-yellow-500 text-black px-6 py-3 rounded-lg font-semibold hover:bg-yellow-400 transition transform hover:scale-105 animate-fade-in-up animation-delay-400">
                                        Shop Now
                                        <Icon icon="mdi:arrow-right" class="inline ml-2" />
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Navigation Arrows (if multiple banners) -->
                <button v-if="homepageBanners.length > 1" @click="prevSlide"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-white/90 hover:bg-white rounded-full p-3 shadow-lg transition-all hover:scale-110">
                    <Icon icon="mdi:chevron-left" class="text-2xl" />
                </button>
                <button v-if="homepageBanners.length > 1" @click="nextSlide"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-white/90 hover:bg-white rounded-full p-3 shadow-lg transition-all hover:scale-110">
                    <Icon icon="mdi:chevron-right" class="text-2xl" />
                </button>

                <!-- Dots indicator -->
                <div v-if="homepageBanners.length > 1"
                    class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-3">
                    <button v-for="(_, index) in homepageBanners" :key="index" @click="currentSlide = index"
                        class="h-2 rounded-full transition-all duration-300"
                        :class="currentSlide === index ? 'w-8 bg-yellow-500' : 'w-2 bg-white/70 hover:bg-white'">
                    </button>
                </div>
            </div>
        </section>

        <!-- NORMAL HOMEPAGE -->
        <template v-if="!isSearching">

            <!-- Browse Categories -->
            <section class="py-12 bg-gray-50">
                <div class="max-w-10/12 mx-auto">
                    <h2 class="text-2xl md:text-3xl font-semibold mb-8 text-center">
                        Browse Categories
                    </h2>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-7 gap-4">
                        <div v-for="category in categories" :key="category.id"
                            class="flex flex-col items-center cursor-pointer group"
                            @click="navigateToCategory(category)">
                            <div
                                class="w-28 h-28 md:w-32 md:h-32 rounded-xl overflow-hidden shadow-sm bg-white group-hover:shadow-md transition-all group-hover:scale-105">
                                <img v-if="category.files?.length" :src="category.files[0].file_url"
                                    class="w-full h-full object-cover" />
                                <div v-else
                                    class="w-full h-full flex items-center justify-center text-xs text-gray-400">
                                    <Icon icon="mdi:image-off" class="text-3xl" />
                                </div>
                            </div>
                            <span
                                class="mt-2 text-sm md:text-base font-medium text-center group-hover:text-brand-blue transition-colors">
                                {{ category.name }}
                                <span v-if="category.ads_count" class="text-xs text-gray-500">
                                    ({{ category.ads_count }})
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Promotional Banners - Grid Layout -->
            <section v-if="promotionalBanners.length > 0" class="py-10">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <a v-for="banner in promotionalBanners" :key="banner.id" :href="banner.link || '#'"
                            :target="banner.link ? '_blank' : '_self'"
                            class="block overflow-hidden rounded-xl shadow-md hover:shadow-xl transition-all hover:scale-[1.02] group">
                            <div class="relative h-48 md:h-56">
                                <img :src="banner.image_url" :alt="banner.title"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                                <div v-if="banner.title"
                                    class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-6">
                                    <div>
                                        <h3 class="text-white text-xl font-bold">{{ banner.title }}</h3>
                                        <p v-if="banner.description" class="text-gray-200 text-sm mt-1">{{
                                            banner.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Category Ads Sections -->
            <section class="max-w-8/10 mx-auto  space-y-12 pb-20">
                <CategoryAds v-for="cat in topCategories" :key="cat.id" :category="cat"
                    :search-term="form.filter.global" />
            </section>

            <!-- Bottom Banner - Full Width -->
            <section v-if="bottomBanner" class="relative h-64 md:h-80 overflow-hidden">
                <a :href="bottomBanner.link || '#'" :target="bottomBanner.link ? '_blank' : '_self'"
                    class="block w-full h-full relative">
                    <img :src="bottomBanner.image_url" :alt="bottomBanner.title" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                        <div class="text-center text-white">
                            <h2 v-if="bottomBanner.title" class="text-3xl md:text-4xl font-bold mb-2">
                                {{ bottomBanner.title }}
                            </h2>
                            <p v-if="bottomBanner.description" class="text-lg md:text-xl mb-4">
                                {{ bottomBanner.description }}
                            </p>
                            <span v-if="bottomBanner.link"
                                class="inline-block bg-yellow-500 text-black px-8 py-3 rounded-lg font-semibold hover:bg-yellow-400 transition">
                                Learn More
                            </span>
                        </div>
                    </div>
                </a>
            </section>

        </template>

        <!-- SEARCH MODE -->
        <template v-else>
            <div class="max-w-7xl mx-auto px-4 py-4">
                <button @click="goBack"
                    class="flex items-center px-5 py-3 rounded-xl text-gray-700 font-semibold hover:shadow-md transition-all duration-200 group">
                    <Icon icon="mdi:arrow-left"
                        class="text-4xl font-extrabold text-gray-600 group-hover:text-yellow-500 mr-2" />
                    Back to Home
                </button>
            </div>
            <SearchResults :category="activeCategory" :categories="categories" :search-term="form.filter.global"
                :selected-category="form.filter.category" :selected-brand="form.filter.brand" :reset="resetToHome" />
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
import type { InertiaPageProps, PaginatedData } from '@/types';
import { useForceTheme } from '@/composables/useForceTheme'
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

interface PageProps extends InertiaPageProps {
    ads: PaginatedData<App.Data.AdData>;
    categories: App.Data.CategoryData[];
    brands: App.Data.BrandData[];
    banners: App.Data.BannerData[];
    filters: any;
    isSearching?: boolean;
    slug?: string;
}

const page = usePage<PageProps>();
const categories = computed(() => page.props.categories || [])
const brands = computed(() => page.props.brands || [])
const banners = computed(() => page.props.banners || [])
const isSearching = computed(() => page.props.isSearching || false)

// Carousel state
const currentSlide = ref(0);
const autoPlayInterval = ref();

useForceTheme('light');

// Filter banners by different positions
const homepageBanners = computed(() => {
    return banners.value.filter(b => b.position === 'homepage');
});

const promotionalBanners = computed(() => {
    // Get banners for promotional grid (could be category or sidebar position)
    return banners.value.filter(b => b.position === 'category' || b.position === 'sidebar').slice(0, 2);
});

const bottomBanner = computed(() => {
    // Get a banner for bottom section (could be floating position)
    return banners.value.find(b => b.position === 'floating') ||
        banners.value.find(b => b.position === 'homepage' && b.id !== homepageBanners.value[0]?.id);
});

// Carousel methods
const nextSlide = () => {
    if (homepageBanners.value.length > 0) {
        currentSlide.value = (currentSlide.value + 1) % homepageBanners.value.length;
    }
};

const prevSlide = () => {
    if (homepageBanners.value.length > 0) {
        currentSlide.value = (currentSlide.value - 1 + homepageBanners.value.length) % homepageBanners.value.length;
    }
};

// Auto-play
const startAutoPlay = () => {
    if (homepageBanners.value.length > 1) {
        stopAutoPlay(); // Clear any existing interval
        autoPlayInterval.value = setInterval(nextSlide, 5000);
    }
};

const stopAutoPlay = () => {
    if (autoPlayInterval.value) {
        clearInterval(autoPlayInterval.value);
        autoPlayInterval.value = null;
    }
};

// Initialize search filter
const { form, reset, isFiltered } = useSearchFilter(route('home'), {
    filter: {
        global: '',
        category: '',
        brand: ''
    }
});

// Navigate to category show page
const navigateToCategory = (category: any) => {
    if (category.slug) {
        router.get(route('category.show', { slug: category.slug }))
    }
}

// Go back to home
const goBack = () => {
    router.get(route('home'))
}

// Reset to home page
const resetToHome = () => {
    router.get(route('home'))
};

// Get flat list of all categories (including children) for dropdown
const allCategoriesFlat = computed(() => {
    const flatList: any[] = [];

    categories.value.forEach(category => {
        // Add child categories
        if (category.children_recursive && category.children_recursive.length) {
            category.children_recursive.forEach(child => {
                flatList.push(child);
            });
        }
    });

    return flatList;
});

// Filter brands based on selected category
const filteredBrands = computed(() => {
    if (!form.value.filter.category) {
        return brands.value;
    }

    // Find selected category and get its leaf categories
    const selectedCat = categories.value.find(cat =>
        cat.id == form.value.filter.category ||
        cat.children_recursive?.some(child => child.id == form.value.filter.category)
    );

    if (!selectedCat) return brands.value;

    // Get all leaf category IDs
    const leafCategories = selectedCat.getLeafCategoriesEfficient?.() || [];
    const leafCategoryIds = leafCategories.map(cat => cat.id);

    // Filter brands that belong to these categories
    return brands.value.filter(brand =>
        brand.categories?.some(cat => leafCategoryIds.includes(cat.id))
    );
});

const topCategories = computed(() => {
    return categories.value
        .filter(c => c.parent_id === null)
        .slice(0, 7)
});

const activeCategory = computed(() => {
    if (form.value.filter.category) {
        const allCats = [...categories.value, ...allCategoriesFlat.value];
        return allCats.find(cat => cat.id == form.value.filter.category) || null;
    }
    return categories.value[0] || null;
});

// Lifecycle
onMounted(() => {
    startAutoPlay();

    // Pause auto-play when user hovers over carousel
    const carousel = document.querySelector('.relative.h-\\[300px\\]');
    if (carousel) {
        carousel.addEventListener('mouseenter', stopAutoPlay);
        carousel.addEventListener('mouseleave', startAutoPlay);
    }
});

onUnmounted(() => {
    stopAutoPlay();

    // Clean up event listeners
    const carousel = document.querySelector('.relative.h-\\[300px\\]');
    if (carousel) {
        carousel.removeEventListener('mouseenter', stopAutoPlay);
        carousel.removeEventListener('mouseleave', startAutoPlay);
    }
});
</script>

<style scoped>
/* Animation keyframes */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
}

.animation-delay-200 {
    animation-delay: 0.2s;
    opacity: 0;
}

.animation-delay-400 {
    animation-delay: 0.4s;
    opacity: 0;
}

/* Ensure smooth transitions */
.transition-opacity {
    transition-property: opacity;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>