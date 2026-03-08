<template>
    <Link :href="route('ads.show', ad.id)" class="block h-full">
        <div
            class="h-full group bg-white border border-gray-200 rounded-xl hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer flex flex-col">

            <!-- Image Container - Fixed height for consistency -->
            <div
                class="relative h-48 sm:h-52 md:h-56 bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden flex-shrink-0">

                <!-- Ad Image -->
                <img v-if="ad.images?.[0]?.path" :src="`/storage/${ad.images[0].path}`" :alt="ad.ad_title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />

                <!-- Placeholder if no image -->
                <div v-else class="w-full h-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <!-- Price Badge - Smaller -->
                <div class="absolute top-3 left-3">
                    <span
                        class="bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-full font-medium text-gray-900 text-xs shadow-sm">
                        Rs {{ formatPrice(ad.price) }}
                    </span>
                </div>

                <!-- Favorite Button - Smaller -->
                <button @click.stop="toggleFavorite"
                    class="absolute top-3 right-3 bg-white/95 backdrop-blur-sm p-2 rounded-full shadow-md hover:shadow-lg transition-all duration-200 group/fav"
                    :class="[isFavorited ? 'text-red-500' : 'text-gray-400 hover:text-red-400', isFavoriteLoading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer']"
                    :disabled="isFavoriteLoading">
                    <svg v-if="isFavorited" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                            clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </button>

                <!-- Featured Badge - Smaller -->
                <div v-if="ad.is_featured" class="absolute bottom-3 left-3">
                    <span class="bg-brand-blue text-white px-2 py-1 rounded-full text-[10px] font-semibold shadow-sm">
                        Featured
                    </span>
                </div>

            </div>

            <!-- Content - Flex-grow to fill remaining space -->
            <div class="p-3 sm:p-4 flex flex-col flex-grow">
                <!-- Title - Smaller font -->
                <h3
                    class="font-semibold text-gray-900 text-sm sm:text-base mb-2 line-clamp-2 group-hover:text-brand-blue transition-colors leading-snug">
                    {{ ad.ad_title || 'Untitled' }}
                </h3>

                <!-- Location & Category - Smaller text and compact -->
                <div class="flex flex-col gap-1.5 text-gray-600 text-xs mb-3">
                    <div class="flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="line-clamp-1 text-xs">{{ ad.location || 'Location not specified' }}</span>
                    </div>
                    <div v-if="ad.category" class="flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span class="text-xs">{{ ad.category.name }}</span>
                    </div>
                </div>

                <!-- Footer - Time & Views - Push to bottom with mt-auto -->
                <div class="flex items-center justify-between pt-2 mt-auto border-t border-gray-100">
                    <div class="flex items-center text-[10px] sm:text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ timeAgo(ad.created_at) }}</span>
                    </div>
                    <div class="flex items-center text-[10px] sm:text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span>{{ ad.views || 0 }} views</span>
                    </div>
                </div>
            </div>
        </div>
    </Link>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

interface Ad {
    id: number
    ad_title: string
    price: number
    location: string
    created_at: string
    is_featured?: boolean
    views?: number
    category?: {
        name: string
    }
    images?: Array<{ path: string }>
    is_favorited?: boolean
}

interface Props {
    ad: Ad
}

const props = defineProps<Props>()

// Use the backend value as initial state
const isFavorited = ref(!!props.ad.is_favorited)
const isFavoriteLoading = ref(false)

const toggleFavorite = async () => {
    if (isFavoriteLoading.value) return
    isFavoriteLoading.value = true

    try {
        await router.post(`/ads/${props.ad.id}/favorite`, {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Toggle locally after successful request
                isFavorited.value = !isFavorited.value
            },
            onError: (errors) => {
                console.error('Failed to toggle favorite', errors)
            },
            onFinish: () => {
                isFavoriteLoading.value = false
            }
        })
    } catch (error) {
        console.error(error)
        isFavoriteLoading.value = false
    }
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
</style>