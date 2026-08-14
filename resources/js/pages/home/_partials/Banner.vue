<template>
    <div v-if="banners.length" class="relative h-[180px] md:h-[400px] lg:h-[500px] overflow-hidden"
        :class="theme.bgLight">
        <div class="w-full h-full relative overflow-hidden">
            <!-- Single Banner -->
            <div v-if="banners.length === 1" class="w-full h-full">
                <a :href="banners[0].link || '#'" :target="banners[0].link ? '_blank' : '_self'"
                    class="block w-full h-full">
                    <img :src="banners[0].image_url" :alt="banners[0].title" class="w-full h-full object-contain"
                        loading="lazy" />
                </a>
            </div>

            <!-- Carousel for multiple banners -->
            <div v-else class="relative w-full h-full">
                <div v-for="(banner, index) in banners" :key="banner.id"
                    class="absolute inset-0 transition-opacity duration-700 w-full h-full" :class="{
                        'opacity-100 z-10': currentSlide === index,
                        'opacity-0': currentSlide !== index,
                    }">
                    <a :href="banner.link || '#'" :target="banner.link ? '_blank' : '_self'"
                        class="block w-full h-full">
                        <img :src="banner.image_url" :alt="banner.title" class="w-full h-full object-contain"
                            loading="lazy" />
                    </a>
                </div>

                <!-- Navigation Buttons -->
                <button v-if="banners.length > 1" @click="prevSlide"
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-20 rounded-full p-3 shadow-lg transition-all"
                    :class="[theme.card, theme.hover, theme.shadow, theme.text]">
                    <Icon icon="mdi:chevron-left" class="text-2xl" />
                </button>
                <button v-if="banners.length > 1" @click="nextSlide"
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-20 rounded-full p-3 shadow-lg transition-all"
                    :class="[theme.card, theme.hover, theme.shadow, theme.text]">
                    <Icon icon="mdi:chevron-right" class="text-2xl" />
                </button>

                <!-- Dot Indicators -->
                <div v-if="banners.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                    <button v-for="(_, index) in banners" :key="index" @click="currentSlide = index"
                        class="h-2 rounded-full transition-all"
                        :class="currentSlide === index ? 'w-8' : 'w-2 opacity-70'" :style="currentSlide === index ?
                            `background-color: ${theme.icon.includes('brand-orange') ? '#f97316' : theme.icon.includes('brand-teal') ? '#14b8a6' : '#3b82f6'}` :
                            'background-color: rgba(255,255,255,0.7)'">
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from "vue";
import { Icon } from "@iconify/vue";
import axios from "axios";
import { useTheme } from "@/Composables/useTheme";

const { theme, activePlan } = useTheme();

const props = defineProps<{
    position: string;
    categoryId?: number | null;
    autoplay?: boolean;
    autoplayInterval?: number;
}>();

const banners = ref<any[]>([]);
const currentSlide = ref(0);
let interval: ReturnType<typeof setInterval> | null = null;

// Dot indicator color based on active plan
const dotColor = computed(() => {
    switch (activePlan.value) {
        case 'premium':
            return '#f97316'; // brand-orange
        case 'pro':
            return '#14b8a6'; // brand-teal
        default:
            return '#3b82f6'; // brand-blue
    }
});

const fetchBanners = async () => {
    try {
        const response = await axios.get(route("getbanners"), {
            params: {
                position: props.position,
                category_id: props.categoryId,
            },
        });
        banners.value = response.data;
    } catch (error) {
        console.error("Failed to fetch banners:", error);
        banners.value = [];
    }
};

const nextSlide = () => {
    if (banners.value.length) {
        currentSlide.value = (currentSlide.value + 1) % banners.value.length;
    }
};

const prevSlide = () => {
    if (banners.value.length) {
        currentSlide.value =
            (currentSlide.value - 1 + banners.value.length) % banners.value.length;
    }
};

const startAutoPlay = () => {
    if (banners.value.length > 1 && props.autoplay !== false && !interval) {
        interval = setInterval(nextSlide, props.autoplayInterval || 5000);
    }
};

const stopAutoPlay = () => {
    if (interval) {
        clearInterval(interval);
        interval = null;
    }
};

const handleVisibilityChange = () => {
    if (document.hidden) {
        stopAutoPlay();
    } else {
        startAutoPlay();
    }
};

// Watch for position changes
watch(
    () => [props.position, props.categoryId],
    () => {
        fetchBanners();
    },
    { immediate: true }
);

// Watch for banners changes to restart autoplay
watch(
    () => banners.value.length,
    () => {
        if (banners.value.length > 1) {
            startAutoPlay();
        } else {
            stopAutoPlay();
        }
    }
);

onMounted(() => {
    document.addEventListener("visibilitychange", handleVisibilityChange);
    if (banners.value.length > 1) {
        startAutoPlay();
    }
});

onUnmounted(() => {
    stopAutoPlay();
    document.removeEventListener("visibilitychange", handleVisibilityChange);
});
</script>

<style scoped>
/* Add smooth color transitions for theme changes */
.relative {
    transition: background-color 0.3s ease;
}

button {
    transition: all 0.3s ease;
}
</style>