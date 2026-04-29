<template>
    <Link :href="route('ads.show', ad.id)" class="block h-full">
        <div :class="[
            'h-full group bg-white border border-gray-200 rounded-xl hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer flex flex-col',
            size === 'small' ? 'card-compact' : '',
        ]">
            <!-- Image Container -->
            <div :class="[
                'relative bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden flex-shrink-0',
                size === 'small' ? 'h-42' : 'h-48 sm:h-52 md:h-56',
            ]">
                <img v-if="ad.images?.[0]?.path" :src="`/storage/${ad.images[0].path}`" :alt="ad.ad_title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />

                <!-- Placeholder -->
                <div v-else class="w-full h-full flex items-center justify-center">
                    <svg :class="size === 'small' ? 'w-6 h-6' : 'w-10 h-10'" class="text-gray-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <!-- Price Badge (ONLY final price when discount present, otherwise full price) -->
                <div class="absolute top-2 left-2">
                    <span :class="[
                        'bg-white/95 backdrop-blur-sm rounded-full font-medium text-gray-900 shadow-sm',
                        size === 'small' ? 'px-2 py-1 text-[10px]' : 'px-3 py-1.5 text-xs',
                    ]">
                        Rs {{ formatPrice(ad.discount && ad.discount > 0 ? ad.discount : ad.price) }}
                    </span>
                </div>

                <!-- Favorite Button -->
                <button @click.stop="toggleFavorite" :class="[
                    'absolute top-2 right-2 bg-white/95 backdrop-blur-sm rounded-full shadow-md hover:shadow-lg transition-all duration-200 group/fav',
                    isFavorited ? 'text-red-500' : 'text-gray-400 hover:text-red-400',
                    isFavoriteLoading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
                    size === 'small' ? 'p-1.5' : 'p-2',
                ]" :disabled="isFavoriteLoading">
                    <svg v-if="isFavorited" :class="size === 'small' ? 'w-3 h-3' : 'w-4 h-4'" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                            clip-rule="evenodd" />
                    </svg>
                    <svg v-else :class="size === 'small' ? 'w-3 h-3' : 'w-4 h-4'" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>

                <!-- Featured Badge -->
                <div v-if="ad.is_featured" class="absolute bottom-2 left-2">
                    <span :class="[
                        'bg-brand-blue text-white rounded-full font-semibold shadow-sm',
                        size === 'small' ? 'px-1.5 py-0.5 text-[8px]' : 'px-2 py-1 text-[10px]',
                    ]">
                        Featured
                    </span>
                </div>
            </div>

            <!-- Content -->
            <div :class="['flex flex-col flex-grow', size === 'small' ? 'p-2' : 'p-3 sm:p-4']">
                <h3 :class="[
                    'font-semibold text-gray-900 line-clamp-2 group-hover:text-brand-blue transition-colors leading-snug',
                    size === 'small' ? 'text-xs mb-1' : 'text-sm sm:text-base mb-2',
                ]">
                    {{ ad.ad_title || "Untitled" }}
                </h3>

                <!-- Discount info (NEW – below title, inside text area) -->
                <div v-if="ad.discount && ad.discount > 0"
                    :class="['flex items-center gap-2 mb-2', size === 'small' ? 'gap-1' : 'gap-2']">
                    <!-- Discounted price (prominent) -->
                    <span :class="[
                        'font-bold text-gray-900',
                        size === 'small' ? 'text-xs' : 'text-sm sm:text-base',
                    ]">
                        Rs {{ formatPrice(ad.discount) }}
                    </span>
                    <!-- Original price (line-through) -->
                    <span :class="[
                        'text-gray-400 line-through',
                        size === 'small' ? 'text-[10px]' : 'text-xs',
                    ]">
                        Rs {{ formatPrice(ad.price) }}
                    </span>
                    <!-- Discount % badge -->
                    <span :class="[
                        'text-green-700 bg-green-50 font-medium rounded-full',
                        size === 'small' ? 'text-[9px] px-1.5 py-0.5' : 'text-xs px-2 py-1',
                    ]">
                        -{{ discountPercentage.toFixed(1) }}%
                    </span>
                </div>

                <!-- Location & Category -->
                <div :class="[
                    'flex flex-col text-gray-600',
                    size === 'small' ? 'gap-1 mb-2' : 'gap-1.5 mb-3',
                ]">
                    <div class="flex items-center">
                        <svg :class="size === 'small' ? 'w-3 h-3 mr-0.5' : 'w-3.5 h-3.5 mr-1'"
                            class="text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span :class="size === 'small' ? 'text-[10px] line-clamp-1' : 'text-xs line-clamp-1'
                            ">
                            {{ ad.location || "Location not specified" }}
                        </span>
                    </div>
                    <div v-if="ad.category" class="flex items-center">
                        <svg :class="size === 'small' ? 'w-3 h-3 mr-0.5' : 'w-3.5 h-3.5 mr-1'"
                            class="text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span :class="size === 'small' ? 'text-[10px]' : 'text-xs'">{{
                            ad.category.name
                            }}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between pt-1 mt-auto border-t border-gray-100">
                    <div class="flex items-center text-gray-500">
                        <svg :class="size === 'small' ? 'w-3 h-3 mr-0.5' : 'w-3.5 h-3.5 mr-1'" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span :class="size === 'small' ? 'text-[9px]' : 'text-[10px] sm:text-xs'">
                            {{ timeAgo(ad.created_at) }}
                        </span>
                    </div>
                    <div class="flex items-center text-gray-500">
                        <span :class="size === 'small' ? 'text-[9px]' : 'text-[10px] sm:text-xs'">
                            {{ ad.views_count || 0 }} views
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";

interface Ad {
    id: number;
    ad_title: string;
    price: number;
    discount?: number;
    location: string;
    created_at: string;
    is_featured?: boolean;
    views?: number;
    category?: { name: string };
    images?: Array<{ path: string }>;
    is_favorited?: boolean;
}

interface Props {
    ad: Ad;
    size?: "normal" | "small";
}

const props = withDefaults(defineProps<Props>(), {
    size: "normal",
});

const isFavorited = ref(!!props.ad.is_favorited);
const isFavoriteLoading = ref(false);

const discountPercentage = computed(() => {
    const price = parseFloat(String(props.ad.price));
    const discountedPrice = parseFloat(String(props.ad.discount ?? 0));
    if (discountedPrice > 0 && discountedPrice < price) {
        return ((price - discountedPrice) / price) * 100;
    }
    return 0;
});

const toggleFavorite = async () => {
    if (isFavoriteLoading.value) return;
    isFavoriteLoading.value = true;

    try {
        await router.post(
            `/ads/${props.ad.id}/favorite`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    isFavorited.value = !isFavorited.value;
                },
                onError: (errors) => {
                    console.error("Failed to toggle favorite", errors);
                },
                onFinish: () => {
                    isFavoriteLoading.value = false;
                },
            }
        );
    } catch (error) {
        console.error(error);
        isFavoriteLoading.value = false;
    }
};

const timeAgo = (date: string) => {
    const now = new Date();
    const past = new Date(date);
    const diffInSeconds = Math.floor((now.getTime() - past.getTime()) / 1000);

    if (diffInSeconds < 60) return "Just now";
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
    return past.toLocaleDateString("en-US", { month: "short", day: "numeric" });
};

const formatPrice = (price: number) => {
    if (!price) return "0";
    return price.toLocaleString("en-US");
};
</script>

<style scoped>
.line-clamp-1 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
}

.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
</style>
