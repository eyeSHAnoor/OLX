<template>
    <div
        class="group bg-white border border-gray-200 rounded-xl hover:shadow-xl transition-all duration-300 overflow-hidden cursor-pointer">

        <!-- Image Container -->
        <div class="relative h-64 bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden">

            <!-- Ad Image -->
            <img v-if="ad.images?.[0]?.path" :src="`/storage/${ad.images[0].path}`" :alt="ad.ad_title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />

            <!-- Placeholder if no image -->
            <div v-else class="w-full h-full flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <!-- Price Badge -->
            <div class="absolute top-4 left-4">
                <span
                    class="bg-white/95 backdrop-blur-sm px-4 py-2 rounded-full font-bold text-gray-900 text-sm shadow-lg">
                    Rs {{ formatPrice(ad.price) }}
                </span>
            </div>

            <!-- Favorite Button -->
            <button @click.stop="toggleFavorite"
                class="absolute top-4 right-4 bg-white/95 backdrop-blur-sm p-2.5 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 group/fav"
                :class="isFavorited ? 'text-red-500' : 'text-gray-400 hover:text-red-400'">
                <svg v-if="isFavorited" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                        clip-rule="evenodd" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>

            <!-- Featured Badge (Optional) -->
            <div v-if="ad.is_featured" class="absolute bottom-4 left-4">
                <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-md">
                    Featured
                </span>
            </div>

        </div>

        <!-- Content -->
        <div class="p-5">
            <!-- Title -->
            <h3 class="font-bold text-gray-900 text-lg mb-2 line-clamp-2 group-hover:text-yellow-700 transition-colors">
                {{ ad.ad_title || 'Untitled' }}
            </h3>

            <!-- Location & Category -->
            <div class="flex items-start justify-between text-gray-600 text-sm mb-4 space-x-4">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="line-clamp-1">{{ ad.location || 'Location not specified' }}</span>
                </div>
                <div v-if="ad.category" class="flex items-start">
                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>{{ ad.category.name }}</span>
                </div>
            </div>

            <!-- Footer - Time & Views -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div class="flex items-center text-xs text-gray-500">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ timeAgo(ad.created_at) }}</span>
                </div>
                <div class="flex items-center text-xs text-gray-500">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
</template>

<script setup lang="ts">
import { ref } from 'vue'

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
}

interface Props {
    ad: Ad
}

const props = defineProps<Props>()

const isFavorited = ref(false)

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
</style>