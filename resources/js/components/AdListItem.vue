<template>
    <Link :href="route('ads.show', ad.id)" class="block h-full">
        <div
            class="h-full group bg-white border border-gray-200 rounded-lg hover:shadow-md transition-all duration-300 overflow-hidden cursor-pointer">
            <div class="flex flex-col sm:flex-row h-full">

                <!-- Image Container - Smaller fixed dimensions -->
                <div
                    class="relative sm:w-36 lg:w-40 h-36 sm:h-auto bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden flex-shrink-0">
                    <img v-if="ad.images?.[0]?.path" :src="`/storage/${ad.images[0].path}`" :alt="ad.ad_title"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                    <div v-else class="w-full h-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <!-- Featured Badge - Smaller -->
                    <div v-if="ad.is_featured" class="absolute top-2 left-2">
                        <span
                            class="bg-brand-blue text-white px-1.5 py-0.5 rounded-full text-[10px] font-medium shadow-sm">
                            Featured
                        </span>
                    </div>

                    <!-- Price Badge - Mobile only, shows final price -->
                    <div class="absolute bottom-2 left-2 sm:hidden">
                        <span
                            class="bg-white/95 backdrop-blur-sm px-2 py-1 rounded-full font-medium text-gray-900 text-[10px] shadow">
                            Rs {{ formatPrice(ad.discount && ad.discount > 0 ? discountedPrice : ad.price) }}
                        </span>
                    </div>
                </div>

                <!-- Content - Compact padding -->
                <div class="flex-1 p-3 sm:p-4 flex flex-col h-full">
                    <div class="flex flex-col h-full">
                        <!-- Header -->
                        <div class="flex-1">
                            <!-- Price & Title Row -->
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div class="flex-1 min-w-0">
                                    <!-- Mobile Price & Discount -->
                                    <div class="sm:hidden mb-0.5">
                                        <div v-if="ad.discount && ad.discount > 0" class="flex items-center gap-1.5">
                                            <span class="font-medium text-gray-900 text-xs">
                                                Rs {{ formatPrice(discountedPrice) }}
                                            </span>
                                            <span class="text-gray-400 line-through text-[10px]">
                                                Rs {{ formatPrice(ad.price) }}
                                            </span>
                                            <span
                                                class="text-green-700 bg-green-50 text-[9px] px-1 py-0.5 rounded-full">
                                                -{{ ad.discount }}%
                                            </span>
                                        </div>
                                        <span v-else class="font-medium text-gray-900 text-xs">
                                            Rs {{ formatPrice(ad.price) }}
                                        </span>
                                    </div>

                                    <h3
                                        class="font-medium text-gray-900 text-sm sm:text-base line-clamp-2 group-hover:text-brand-blue transition-colors leading-tight">
                                        {{ ad.ad_title || 'Untitled' }}
                                    </h3>

                                    <!-- Desktop Price & Condition -->
                                    <div class="hidden sm:flex items-center gap-2 mt-1">
                                        <div v-if="ad.discount && ad.discount > 0" class="flex items-center gap-1.5">
                                            <span class="font-medium text-gray-900 text-sm">
                                                Rs {{ formatPrice(discountedPrice) }}
                                            </span>
                                            <span class="text-gray-400 line-through text-xs">
                                                Rs {{ formatPrice(ad.price) }}
                                            </span>
                                            <span
                                                class="text-green-700 bg-green-50 text-[10px] px-1.5 py-0.5 rounded-full">
                                                -{{ ad.discount }}%
                                            </span>
                                        </div>
                                        <span v-else class="font-medium text-gray-900 text-sm">
                                            Rs {{ formatPrice(ad.price) }}
                                        </span>
                                        <span v-if="ad.condition"
                                            class="text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full">
                                            {{ ad.condition }}
                                        </span>
                                    </div>

                                    <!-- Mobile Condition -->
                                    <div v-if="ad.condition" class="sm:hidden mt-1">
                                        <span class="text-[10px] text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded-full">
                                            {{ ad.condition }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Favorite Button - Tiny -->
                                <button @click.stop="toggleFavorite"
                                    class="flex-shrink-0 bg-gray-100 hover:bg-gray-200 p-1.5 rounded-full transition-colors duration-200"
                                    :class="isFavorited ? 'text-red-500' : 'text-gray-400'">
                                    <svg v-if="isFavorited" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Description - Small -->
                            <p v-if="ad.description"
                                class="text-gray-600 text-xs sm:text-sm mb-2 line-clamp-2 leading-relaxed">
                                {{ ad.description }}
                            </p>

                            <!-- Location & Category - Compact -->
                            <div class="flex flex-wrap gap-2 mb-2">
                                <div class="flex items-center text-gray-600 text-[10px] sm:text-xs">
                                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 mr-0.5 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="line-clamp-1">{{ ad.location || 'Location not specified' }}</span>
                                </div>
                                <div v-if="ad.category" class="flex items-center text-gray-600 text-[10px] sm:text-xs">
                                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 mr-0.5 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span>{{ ad.category.name }}</span>
                                </div>
                                <div v-if="ad.brand" class="flex items-center text-gray-600 text-[10px] sm:text-xs">
                                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 mr-0.5 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                    <span>{{ ad.brand.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer - Compact -->
                        <div
                            class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 pt-2 border-t border-gray-100">
                            <div class="flex items-center justify-between xs:justify-start gap-3">
                                <div class="flex items-center text-[10px] sm:text-xs text-gray-500">
                                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 mr-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ timeAgo(ad.created_at) }}</span>
                                </div>
                                <div class="flex items-center text-[10px] sm:text-xs text-gray-500">
                                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 mr-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>{{ ad.views_count || 0 }} views</span>
                                </div>
                            </div>
                            <button
                                class="w-full xs:w-auto px-3 py-1.5 bg-brand-blue text-white text-xs font-medium rounded-md hover:bg-brand-teal transition-colors duration-200">
                                Contact
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'

interface Brand {
    name: string
}

interface Ad {
    id: number
    ad_title: string
    price: number
    discount?: number  // discount percentage (e.g., 10 for 10%)
    location: string
    created_at: string
    description?: string
    condition?: string
    is_featured?: boolean
    views?: number
    category?: {
        name: string
    }
    brand?: Brand
    images?: Array<{ path: string }>
}

interface Props {
    ad: Ad
}

const props = defineProps<Props>()

const isFavorited = ref(false)

const discountedPrice = computed(() => {
    const price = parseFloat(String(props.ad.price))
    const discount = parseFloat(String(props.ad.discount ?? 0))
    if (discount > 0 && discount <= 100) {
        return Math.round(price * (1 - discount / 100))
    }
    return price
})

const toggleFavorite = () => {
    isFavorited.value = !isFavorited.value
}

const timeAgo = (date: string) => {
    const now = new Date()
    const past = new Date(date)
    const diffInSeconds = Math.floor((now.getTime() - past.getTime()) / 1000)

    if (diffInSeconds < 60) return 'Just now'
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`

    return past.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    })
}

const formatPrice = (price: number) => {
    if (!price) return '0'
    return price.toLocaleString('en-US')
}
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

/* Custom breakpoint for extra small screens */
@media (min-width: 480px) {
    .xs\:flex-row {
        flex-direction: row;
    }

    .xs\:items-center {
        align-items: center;
    }

    .xs\:justify-start {
        justify-content: flex-start;
    }

    .xs\:w-auto {
        width: auto;
    }
}
</style>