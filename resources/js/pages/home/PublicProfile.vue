<template>
    <OlxLayout>
        <div class="max-w-9/11 mx-auto px-3 sm:px-4 py-4 md:py-6">
            <!-- Rank Banner -->
            <!-- Rank Badge - Professional Minimal Style -->
            <div v-if="profileUser.rank !== null" class="mb-4 md:mb-5">
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927C9.469 1.638 10.531 1.638 10.951 2.927l.7 2.153a1 1 0 00.95.69h2.263c1.356 0 1.92 1.736.822 2.54l-1.83 1.33a1 1 0 00-.364 1.118l.7 2.153c.42 1.289-.99 2.36-2.088 1.556l-1.83-1.33a1 1 0 00-1.176 0l-1.83 1.33c-1.098.804-2.508-.267-2.088-1.556l.7-2.153a1 1 0 00-.364-1.118l-1.83-1.33c-1.098-.804-.534-2.54.822-2.54h2.263a1 1 0 00.95-.69l.7-2.153z" />
                            </svg>
                            <span class="text-xs font-medium text-gray-600 uppercase tracking-wide">Rank</span>
                        </div>
                        <span class="text-xl font-semibold text-gray-900">#{{ profileUser.rank }}</span>
                    </div>
                </div>
            </div>

            <!-- Order Statistics - Clean Dashboard Style -->
            <div v-if="profileUser.orderStats" class="mb-5">
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Order Statistics</h3>
                    </div>

                    <div class="p-4">
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                            <!-- Total Orders -->
                            <div class="text-left">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ profileUser.orderStats.total_orders || 0 }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Total Orders</div>
                            </div>

                            <!-- Completed Orders -->
                            <div class="text-left">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ profileUser.orderStats.completed_orders || 0 }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Completed</div>
                            </div>

                            <!-- Total Amount -->
                            <div class="text-left">
                                <div class="text-lg font-semibold text-gray-900">
                                    Rs. {{ formatAmount(profileUser.orderStats.completed_amount || 0) }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Total Spent</div>
                            </div>

                            <!-- Completion Rate with Progress Bar -->
                            <div class="text-left">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ profileUser.orderStats.completion_rate || 0 }}%
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Completion Rate</div>
                                <div class="w-full bg-gray-100 rounded h-1 mt-2">
                                    <div class="h-1 rounded bg-gray-700 transition-all duration-300"
                                        :style="{ width: `${profileUser.orderStats.completion_rate || 0}%` }">
                                    </div>
                                </div>
                            </div>

                            <!-- Cancelled Orders -->
                            <div class="text-left">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ profileUser.orderStats.cancelled_orders || 0 }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Cancelled ({{ profileUser.orderStats.cancel_rate || 0 }}%)
                                </div>
                                <div class="w-full bg-gray-100 rounded h-1 mt-2">
                                    <div class="h-1 rounded bg-gray-400 transition-all duration-300"
                                        :style="{ width: `${profileUser.orderStats.cancel_rate || 0}%` }">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rating Summary -->
                        <div v-if="averageRating > 0" class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-gray-600">Average Rating</span>
                                    <span class="text-sm font-medium text-gray-900">{{ averageRating.toFixed(1) }} /
                                        5.0</span>
                                </div>
                                <span class="text-xs text-gray-500">{{ totalRatings }} {{ totalRatings === 1 ? 'rating'
                                    : 'ratings' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Header - Professional Layout -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
                <!-- Cover Photo - Subtle Gradient -->
                <div class="h-28 md:h-36 bg-gradient-to-r from-gray-100 to-gray-50 relative overflow-hidden">
                    <img v-if="profileUser.profile?.cover_image" :src="`/storage/${profileUser.profile.cover_image}`"
                        :alt="`${profileUser.name}'s cover`" class="w-full h-full object-cover" />
                </div>

                <!-- Profile Content -->
                <div class="px-5 pb-5 relative">
                    <!-- Edit Button -->
                    <div v-if="isOwner" class="absolute top-0 right-5">
                        <Link :href="route('profile.edit')"
                            class="inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-700 text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Edit
                        </Link>
                    </div>

                    <!-- Avatar and Primary Info -->
                    <div class="flex items-end -mt-12 md:-mt-14 mb-4">
                        <div class="relative">
                            <!-- Avatar -->
                            <div v-if="profileUser.profile?.profile_image"
                                class="w-20 h-20 md:w-28 md:h-28 rounded-full border-4 border-white shadow-sm overflow-hidden bg-white">
                                <img :src="`/storage/${profileUser.profile.profile_image}`" :alt="profileUser.name"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div v-else
                                class="w-20 h-20 md:w-28 md:h-28 rounded-full border-4 border-white shadow-sm bg-gray-100 flex items-center justify-center">
                                <span class="text-3xl md:text-4xl font-medium text-gray-600 uppercase">
                                    {{ profileUser.name?.charAt(0) || 'U' }}
                                </span>
                            </div>

                            <!-- Username Badge -->
                            <div v-if="profileUser.profile?.username"
                                class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-[11px] px-2.5 py-0.5 rounded-full whitespace-nowrap">
                                @{{ profileUser.profile.username }}
                            </div>
                        </div>

                        <div class="ml-4 mb-1">
                            <h1 class="text-xl md:text-2xl font-semibold text-gray-900">{{ profileUser.name }}</h1>
                            <p class="text-xs text-gray-500 flex items-center gap-1.5 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Member since {{ formatDate(profileUser.created_at) }}
                            </p>
                        </div>
                    </div>

                    <!-- Contact Information Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        <!-- Email -->
                        <div class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] text-gray-500 uppercase tracking-wide">Email</p>
                                <p class="text-sm text-gray-900 truncate">{{ profileUser.email }}</p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] text-gray-500 uppercase tracking-wide">Phone</p>
                                <p class="text-sm text-gray-900 truncate">
                                    {{ profileUser.phone || 'Not provided' }}
                                </p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div v-if="profileUser.profile?.location"
                            class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] text-gray-500 uppercase tracking-wide">Location</p>
                                <p class="text-sm text-gray-900 truncate">{{ profileUser.profile.location }}</p>
                            </div>
                        </div>

                        <!-- Website -->
                        <div v-if="profileUser.profile?.website"
                            class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] text-gray-500 uppercase tracking-wide">Website</p>
                                <a :href="profileUser.profile.website" target="_blank"
                                    class="text-sm text-gray-900 hover:text-gray-700 truncate block">
                                    {{ formatWebsite(profileUser.profile.website) }}
                                </a>
                            </div>
                        </div>

                        <!-- Profile Views -->
                        <div class="flex items-center gap-3 p-2.5 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] text-gray-500 uppercase tracking-wide">Profile Views</p>
                                <p class="text-sm font-medium text-gray-900">{{ profileUser.total_views || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bio Section -->
                    <div v-if="profileUser.profile?.bio" class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700 leading-relaxed">{{ profileUser.profile.bio }}</p>
                    </div>

                    <!-- Privacy Status -->
                    <div v-if="isOwner && profileUser.profile?.is_public === 0" class="mt-3">
                        <div class="inline-flex items-center gap-1.5 text-xs text-gray-500">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Private Profile</span>
                        </div>
                    </div>
                </div>
            </div>



            <!-- User's Ads Section -->
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <h2 class="text-lg md:text-xl font-semibold text-gray-900">
                        {{ profileUser.name }}'s Ads
                        <span class="text-xs font-normal text-gray-500 ml-2">({{ ads.total || profileUser.total_ads || 0
                            }} total)</span>
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
                            <Link :href="route('user.ads.create')"
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
                <div v-if="ads.data && ads.data.length > 0"
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
                    <Link v-if="isOwner" :href="route('user.ads.create')"
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

            <!-- Recent Ratings Section -->
            <div v-if="recentRatings.length > 0" class="bg-white rounded-lg mt-10 shadow-sm p-4 md:p-5 mb-4 md:mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg md:text-xl font-semibold text-gray-900">Recent Ratings</h2>
                    <Link v-if="totalRatings > 5" :href="route('user.ratings', profileUser.id)"
                        class="text-xs text-brand-blue hover:underline">
                        View all ({{ totalRatings }})
                    </Link>
                </div>

                <div class="space-y-3">
                    <!-- Rating Summary Section - Moved to top of contact info -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-gray-700">Seller Rating</h3>
                            <span class="text-xs text-gray-500">{{ totalRatings }} {{ totalRatings === 1 ? 'rating' :
                                'ratings' }}</span>
                        </div>

                        <!-- Average Rating -->
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex flex-col items-center">
                                <span class="text-2xl font-bold text-gray-900">{{ averageRating.toFixed(1) }}</span>
                                <span class="text-[10px] text-gray-500">out of 5</span>
                            </div>
                            <div class="flex-1">
                                <div class="flex gap-1 mb-1">
                                    <Icon v-for="i in 5" :key="i" icon="lucide:star" class="size-4"
                                        :class="i <= Math.round(averageRating) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'" />
                                </div>
                                <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-yellow-400 rounded-full"
                                        :style="{ width: `${(averageRating / 5) * 100}%` }"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Rating Distribution -->
                        <div class="space-y-1.5">
                            <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="flex items-center gap-2 text-xs">
                                <span class="w-6 text-gray-600">{{ star }}★</span>
                                <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-yellow-400 rounded-full"
                                        :style="{ width: `${getRatingPercentage(star)}%` }"></div>
                                </div>
                                <span class="w-6 text-gray-500 text-right">{{ getRatingCount(star) }}</span>
                            </div>
                        </div>
                    </div>
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
                                            Are you sure you want to delete "{{ adToDelete?.title }}"? This action
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
import { Icon } from '@iconify/vue'

const page = usePage()

interface Props {
    profileUser: {
        id: number
        name: string
        email: string
        phone: string | null
        rank: number
        created_at: string
        avatar: string | null
        total_ads?: number
        total_views?: number
        orderStats?: {
            total_orders: number
            completed_orders: number
            completed_amount: number
            cancelled_orders: number
            completion_rate: number
            cancel_rate: number
        }
        received_ratings?: Array<{
            id: number
            rating: number
            review: string | null
            created_at: string
            ad_id: number
            rater_id: number
            rated_user_id: number
            rater?: {
                id: number
                name: string
            }
        }>
        profile?: {
            id: number
            user_id: number
            username: string | null
            profile_image: string | null
            cover_image: string | null
            bio: string | null
            location: string | null
            website: string | null
            is_public: number | boolean
            created_at: string
            updated_at: string
        }
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

console.log(page.props)

// Helper function to format amount
const formatAmount = (amount: number) => {
    if (amount >= 100000) {
        return (amount / 100000).toFixed(1) + 'L'
    } else if (amount >= 1000) {
        return (amount / 1000).toFixed(1) + 'K'
    }
    return amount.toString()
}

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

// Rating calculations
const receivedRatings = computed(() => props.profileUser.received_ratings || [])

const totalRatings = computed(() => receivedRatings.value.length)

const averageRating = computed(() => {
    if (totalRatings.value === 0) return 0
    const sum = receivedRatings.value.reduce((acc, curr) => acc + curr.rating, 0)
    return sum / totalRatings.value
})

const ratingDistribution = computed(() => {
    const distribution = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 }
    receivedRatings.value.forEach((r) => {
        distribution[r.rating as keyof typeof distribution]++
    })
    return distribution
})

const getRatingPercentage = (star: number) => {
    if (totalRatings.value === 0) return 0
    return (ratingDistribution.value[star as keyof typeof ratingDistribution.value] / totalRatings.value) * 100
}

const getRatingCount = (star: number) => {
    return ratingDistribution.value[star as keyof typeof ratingDistribution.value] || 0
}

const recentRatings = computed(() => {
    return [...receivedRatings.value]
        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
        .slice(0, 5)
})

// Helper function to format date
const formatDate = (dateString: string) => {
    if (!dateString) return 'Unknown'
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
}

// Helper function for relative time
const formatRelativeTime = (dateString: string) => {
    const date = new Date(dateString)
    const now = new Date()
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)

    if (diffInSeconds < 60) return 'just now'
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

// Helper function to format website URL for display
const formatWebsite = (url: string) => {
    if (!url) return ''
    return url.replace(/^https?:\/\//, '')
}

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