<template>
    <OlxLayout>
        <div class="max-w-9/11 mx-auto px-3 sm:px-4 py-4 md:py-6">

            <!-- Profile Header -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-4 md:mb-6">
                <!-- Cover Photo (Optional) -->
                <div class="h-24 md:h-32 bg-gray-100"></div>

                <!-- Profile Info -->
                <div class="px-4 md:px-6 pb-4 md:pb-6 relative">
                    <!-- Edit Profile Button (only for owner) -->
                    <div v-if="isOwner" class="absolute top-3 right-3 md:top-4 md:right-4">
                        <Link :href="route('profile.edit')"
                            class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 border border-gray-200 text-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Edit Profile
                        </Link>
                    </div>

                    <!-- Avatar -->
                    <div class="flex flex-col md:flex-row md:items-end -mt-10 md:-mt-14 mb-3 md:mb-4">
                        <div class="flex items-end space-x-3 md:space-x-4">
                            <!-- Avatar with Initial -->
                            <div class="relative">
                                <div v-if="profileUser.avatar"
                                    class="w-16 h-16 md:w-24 md:h-24 rounded-full border-4 border-white shadow-md overflow-hidden">
                                    <img :src="profileUser.avatar" :alt="profileUser.name"
                                        class="w-full h-full object-cover" />
                                </div>
                                <div v-else
                                    class="w-16 h-16 md:w-24 md:h-24 rounded-full border-4 border-white shadow-md bg-brand-blue flex items-center justify-center">
                                    <span class="text-2xl md:text-4xl font-semibold text-white uppercase">
                                        {{ profileUser.initial }}
                                    </span>
                                </div>
                            </div>

                            <!-- Name and basic info -->
                            <div class="mb-1">
                                <h1 class="text-xl md:text-2xl font-semibold text-gray-900">{{ profileUser.name }}</h1>
                                <p class="text-gray-600 text-xs md:text-sm flex items-center gap-1.5 mt-0.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Member since {{ profileUser.member_since }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        <!-- Email -->
                        <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg">
                            <div
                                class="w-8 h-8 rounded-full bg-brand-blue/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-brand-blue" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] text-gray-500">Email</p>
                                <p class="text-xs font-medium text-gray-900 truncate">{{ profileUser.email }}</p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg">
                            <div
                                class="w-8 h-8 rounded-full bg-brand-teal/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-brand-teal" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] text-gray-500">Phone</p>
                                <p class="text-xs font-medium text-gray-900 truncate">
                                    {{ profileUser.phone || 'Not provided' }}
                                </p>
                            </div>
                        </div>

                        <!-- Location if available -->
                        <div v-if="profileUser.location" class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg">
                            <div
                                class="w-8 h-8 rounded-full bg-brand-blue/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-brand-blue" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] text-gray-500">Location</p>
                                <p class="text-xs font-medium text-gray-900 truncate">{{ profileUser.location }}</p>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded-lg">
                            <div
                                class="w-8 h-8 rounded-full bg-brand-teal/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-brand-teal" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] text-gray-500">Total Views</p>
                                <p class="text-xs font-medium text-gray-900">{{ profileUser.total_views }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bio if available -->
                    <div v-if="profileUser.bio" class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-700">{{ profileUser.bio }}</p>
                    </div>
                </div>
            </div>

            <!-- User's Ads Section -->
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <h2 class="text-lg md:text-xl font-semibold text-gray-900">
                        {{ profileUser.name }}'s Ads
                        <span class="text-xs font-normal text-gray-500 ml-2">({{ profileUser.total_ads }} total)</span>
                    </h2>

                    <!-- Filters and Actions -->
                    <div class="flex flex-col sm:flex-row gap-2">
                        <!-- City Filter -->
                        <select v-model="cityFilter" @change="applyFilters"
                            class="border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs min-w-[140px]">
                            <option value="all">All Cities</option>
                            <option v-for="city in userCities" :key="city" :value="city">
                                {{ city }}
                            </option>
                        </select>

                        <!-- Sort Filter -->
                        <select v-model="sortBy" @change="applyFilters"
                            class="border border-gray-300 rounded px-3 py-1.5 focus:ring-1 focus:ring-brand-teal focus:border-brand-teal outline-none transition text-xs min-w-[140px]">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                        </select>

                        <!-- Add New Ad Button (only for owner) -->
                        <div v-if="isOwner">
                            <Link :href="route('ads.create')"
                                class="inline-flex items-center gap-1.5 bg-brand-blue hover:bg-brand-blue/90 text-white px-3 py-1.5 rounded transition-colors text-xs font-medium shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Post New Ad
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Ads Grid with Edit/Delete for Owner -->
                <div v-if="ads.data.length > 0"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4">
                    <div v-for="ad in ads.data" :key="ad.id" class="relative group">
                        <AdCard :ad="ad" />

                        <!-- Owner Actions Overlay -->
                        <div v-if="isOwner"
                            class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex gap-1.5">
                            <button @click="editAd(ad.id)"
                                class="p-1.5 bg-brand-teal text-white rounded hover:bg-brand-teal/90 shadow-md transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button @click="confirmDeleteAd(ad)"
                                class="p-1.5 bg-red-600 text-white rounded hover:bg-red-700 shadow-md transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- No Ads -->
                <div v-else class="text-center py-8 md:py-10">
                    <svg class="w-12 h-12 md:w-16 md:h-16 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900 mb-1">No ads found</h3>
                    <p class="text-xs text-gray-600">
                        {{ isOwner ? "You haven't posted any ads yet." : "This user hasn't posted any ads yet." }}
                    </p>
                    <Link v-if="isOwner" :href="route('ads.create')"
                        class="inline-block mt-3 bg-brand-blue hover:bg-brand-blue/90 text-white px-4 py-1.5 rounded transition-colors text-xs shadow-sm">
                        Post Your First Ad
                    </Link>
                </div>

                <!-- Pagination with Next/Prev buttons -->
                <div v-if="ads.links && ads.links.length > 3" class="mt-6 flex items-center justify-center gap-2">
                    <!-- Previous Button -->
                    <Link v-if="ads.prev_page_url" :href="ads.prev_page_url"
                        class="flex items-center gap-1 px-3 py-1.5 border border-gray-300 rounded hover:bg-gray-50 transition-colors text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </Link>
                    <span v-else
                        class="flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded text-gray-400 cursor-not-allowed text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </span>

                    <!-- Page Numbers -->
                    <div class="hidden sm:flex gap-1">
                        <Link v-for="link in ads.links.slice(1, -1)" :key="link.label" :href="link.url || '#'"
                            class="w-7 h-7 flex items-center justify-center rounded text-xs transition-colors" :class="link.active
                                ? 'bg-brand-blue text-white'
                                : 'border border-gray-300 hover:bg-gray-50'">
                            {{ link.label }}
                        </Link>
                    </div>

                    <!-- Current Page Indicator for Mobile -->
                    <span class="sm:hidden text-xs text-gray-700">
                        Page {{ ads.current_page }} of {{ ads.last_page }}
                    </span>

                    <!-- Next Button -->
                    <Link v-if="ads.next_page_url" :href="ads.next_page_url"
                        class="flex items-center gap-1 px-3 py-1.5 border border-gray-300 rounded hover:bg-gray-50 transition-colors text-xs">
                        Next
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    <span v-else
                        class="flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded text-gray-400 cursor-not-allowed text-xs">
                        Next
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity" @click="showDeleteModal = false">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-4 pb-3 sm:p-5 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100 sm:mx-0 sm:h-8 sm:w-8">
                                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-2 text-center sm:mt-0 sm:ml-3 sm:text-left">
                                    <h3 class="text-base font-medium text-gray-900">
                                        Delete Ad
                                    </h3>
                                    <div class="mt-1">
                                        <p class="text-xs text-gray-500">
                                            Are you sure you want to delete "{{ adToDelete?.ad_title }}"? This action
                                            cannot be undone.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-2 sm:px-5 sm:flex sm:flex-row-reverse">
                            <button @click="deleteAd" type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-3 py-1.5 bg-red-600 text-xs font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-2 sm:w-auto">
                                Delete
                            </button>
                            <button @click="showDeleteModal = false" type="button"
                                class="mt-2 sm:mt-0 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-3 py-1.5 bg-white text-xs font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-blue sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </OlxLayout>
</template>
<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'
import AdCard from '@/components/AdCard.vue'

const page = usePage()
useForceTheme('light');
interface Props {
    profileUser: {
        id: number
        name: string
        email: string
        phone: string | null
        avatar: string | null
        initial: string
        bio: string | null
        location: string | null
        member_since: string
        total_ads: number
        total_views: number
    }
    ads: {
        data: any[]
        links: Array<{
            url: string | null
            label: string
            active: boolean
        }>
        current_page: number
        last_page: number
        total: number
        next_page_url: string | null
        prev_page_url: string | null
    }
    filters: {
        city: string
        sort_by: string
    }
    userCities: string[]
    selectedCity: string
}

const props = defineProps<Props>()

// Check if current user is the profile owner
const isOwner = computed(() => {
    return page.props.auth?.user?.id === props.profileUser.id
})

// Filter states
const cityFilter = ref(props.filters.city)
const sortBy = ref(props.filters.sort_by)

// Delete modal state
const showDeleteModal = ref(false)
const adToDelete = ref<any>(null)

// Apply filters
const applyFilters = () => {
    router.get(route('user.profile', { id: props.profileUser.id }), {
        city: cityFilter.value,
        sort_by: sortBy.value
    }, {
        preserveScroll: true,
        preserveState: true
    })
}

// Edit ad
const editAd = (adId: number) => {
    router.get(route('user.ads.edit', { id: adId }))
}

// Confirm delete
const confirmDeleteAd = (ad: any) => {
    adToDelete.value = ad
    showDeleteModal.value = true
}

// Delete ad
const deleteAd = () => {
    if (adToDelete.value) {
        router.delete(route('ads.destroy', { id: adToDelete.value.id }), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false
                adToDelete.value = null
            }
        })
    }
}
</script>