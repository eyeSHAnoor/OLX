<template>
    <OlxLayout>
        <div class="max-w-9/11 mx-auto px-3 sm:px-4 py-6 md:py-8">

            <!-- Profile Header -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6 md:mb-8">
                <!-- Cover Photo (Optional) -->
                <div class="h-32 md:h-48 bg-gray-100"></div>

                <!-- Profile Info -->
                <div class="px-4 md:px-8 pb-6 md:pb-8 relative">
                    <!-- Edit Profile Button (only for owner) -->
                    <div v-if="isOwner" class="absolute top-4 right-4 md:top-6 md:right-8">
                        <Link :href="route('profile.edit')"
                            class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-lg shadow-md transition-all duration-200 border border-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Edit Profile
                        </Link>
                    </div>

                    <!-- Avatar -->
                    <div class="flex flex-col md:flex-row md:items-end -mt-12 md:-mt-16 mb-4 md:mb-6">
                        <div class="flex items-end space-x-4 md:space-x-6">
                            <!-- Avatar with Initial -->
                            <div class="relative">
                                <div v-if="profileUser.avatar"
                                    class="w-20 h-20 md:w-28 md:h-28 rounded-full border-4 border-white shadow-lg overflow-hidden">
                                    <img :src="profileUser.avatar" :alt="profileUser.name"
                                        class="w-full h-full object-cover" />
                                </div>
                                <div v-else
                                    class="w-20 h-20 md:w-28 md:h-28 rounded-full border-4 border-white shadow-lg bg-brand-blue flex items-center justify-center">
                                    <span class="text-4xl md:text-5xl font-bold text-white uppercase">
                                        {{ profileUser.initial }}
                                    </span>
                                </div>
                            </div>

                            <!-- Name and basic info -->
                            <div class="mb-1 md:mb-2">
                                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ profileUser.name }}</h1>
                                <p class="text-gray-600 text-sm md:text-base flex items-center gap-2 mt-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Member since {{ profileUser.member_since }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 md:mt-6">
                        <!-- Email -->
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500">Email</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ profileUser.email }}</p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div
                                class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500">Phone</p>
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ profileUser.phone || 'Not provided' }}
                                </p>
                            </div>
                        </div>

                        <!-- Location if available -->
                        <div v-if="profileUser.location" class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div
                                class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500">Location</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ profileUser.location }}</p>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div
                                class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500">Total Views</p>
                                <p class="text-sm font-medium text-gray-900">{{ profileUser.total_views }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bio if available -->
                    <div v-if="profileUser.bio" class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700">{{ profileUser.bio }}</p>
                    </div>
                </div>
            </div>

            <!-- User's Ads Section -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-4 md:mb-0">
                        {{ profileUser.name }}'s Ads
                        <span class="text-sm font-normal text-gray-500 ml-2">({{ profileUser.total_ads }} total)</span>
                    </h2>



                    <!-- Filters -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <!-- City Filter -->
                        <select v-model="cityFilter" @change="applyFilters"
                            class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-blue focus:border-brand-blue outline-none transition text-sm min-w-[160px]">
                            <option value="all">All Cities</option>
                            <option v-for="city in userCities" :key="city" :value="city">
                                {{ city }}
                            </option>
                        </select>

                        <!-- Sort Filter -->
                        <select v-model="sortBy" @change="applyFilters"
                            class="border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-blue focus:border-brand-blue outline-none transition text-sm min-w-[160px]">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                        </select>

                        <!-- Add New Ad Button (only for owner) -->
                        <div v-if="isOwner" class="mb-4 md:mb-0">
                            <Link :href="route('ads.create')"
                                class="inline-flex items-center gap-2 bg-brand-blue hover:bg-brand-blue-dark text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                    <div v-for="ad in ads.data" :key="ad.id" class="relative group">
                        <AdCard :ad="ad" />

                        <!-- Owner Actions Overlay -->
                        <div v-if="isOwner"
                            class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex gap-2">
                            <button @click="editAd(ad.id)"
                                class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button @click="confirmDeleteAd(ad)"
                                class="p-2 bg-red-600 text-white rounded-lg hover:bg-red-700 shadow-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- No Ads -->
                <div v-else class="text-center py-12">
                    <svg class="w-16 h-16 md:w-20 md:h-20 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No ads found</h3>
                    <p class="text-gray-600">
                        {{ isOwner ? "You haven't posted any ads yet." : "This user hasn't posted any ads yet." }}
                    </p>
                    <Link v-if="isOwner" :href="route('ads.create')"
                        class="inline-block mt-4 bg-brand-blue hover:bg-brand-blue-dark text-white px-6 py-2 rounded-lg transition-colors">
                        Post Your First Ad
                    </Link>
                </div>

                <!-- Pagination with Next/Prev buttons -->
                <div v-if="ads.links && ads.links.length > 3" class="mt-8 flex items-center justify-center gap-2">
                    <!-- Previous Button -->
                    <Link v-if="ads.prev_page_url" :href="ads.prev_page_url"
                        class="flex items-center gap-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </Link>
                    <span v-else
                        class="flex items-center gap-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-400 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </span>

                    <!-- Page Numbers -->
                    <div class="hidden sm:flex gap-1">
                        <Link v-for="link in ads.links.slice(1, -1)" :key="link.label" :href="link.url || '#'"
                            class="px-4 py-2 rounded-lg transition-colors" :class="link.active
                                ? 'bg-brand-blue text-white'
                                : 'border border-gray-300 hover:bg-gray-50'">
                            {{ link.label }}
                        </Link>
                    </div>

                    <!-- Current Page Indicator for Mobile -->
                    <span class="sm:hidden px-4 py-2 text-gray-700">
                        Page {{ ads.current_page }} of {{ ads.last_page }}
                    </span>

                    <!-- Next Button -->
                    <Link v-if="ads.next_page_url" :href="ads.next_page_url"
                        class="flex items-center gap-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Next
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    <span v-else
                        class="flex items-center gap-1 px-4 py-2 border border-gray-200 rounded-lg text-gray-400 cursor-not-allowed">
                        Next
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        Delete Ad
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Are you sure you want to delete "{{ adToDelete?.ad_title }}"? This action
                                            cannot be undone.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button @click="deleteAd" type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Delete
                            </button>
                            <button @click="showDeleteModal = false" type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
    router.get(route('ads.edit', { id: adId }))
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