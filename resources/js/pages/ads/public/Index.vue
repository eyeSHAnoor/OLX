<template>
    <OlxLayout>
        <div class="max-w-9/11 mx-auto px-3 sm:px-4 py-4 md:py-6">
            <!-- Header Section -->
            <div class="mb-6 md:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">My Ads</h1>
                        <p class="text-sm text-gray-600 mt-1">Manage and track all your marketplace listings</p>
                    </div>

                    <!-- Create New Ad Button -->
                    <Link :href="route('user.ads.create')"
                        class="inline-flex items-center justify-center gap-2 bg-brand-teal text-white px-4 py-2.5 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg text-sm font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Post New Ad
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mt-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-brand-teal/10 rounded-lg">
                                <svg class="w-5 h-5 text-brand-teal" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ totalAds }}</p>
                                <p class="text-xs text-gray-600">Total Ads</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ activeAds }}</p>
                                <p class="text-xs text-gray-600">Active</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ pendingAds }}</p>
                                <p class="text-xs text-gray-600">Pending</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-brand-blue/10 rounded-lg">
                                <svg class="w-5 h-5 text-brand-blue" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ totalViews }}</p>
                                <p class="text-xs text-gray-600">Total Views</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="p-4 md:p-5">
                    <!-- Search and Filter Row -->
                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- Search Bar -->
                        <div class="flex-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input v-model="searchQuery" type="text"
                                placeholder="Search by title, description, location..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm" />
                        </div>

                        <!-- Filter Buttons -->
                        <div class="flex flex-wrap gap-2">
                            <!-- Category Filter -->
                            <select v-model="categoryFilter"
                                class="px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm min-w-[140px]">
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>

                            <!-- Brand Filter -->
                            <select v-model="brandFilter"
                                class="px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm min-w-[140px]">
                                <option value="">All Brands</option>
                                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                    {{ brand.name }}
                                </option>
                            </select>

                            <!-- Status Filter -->
                            <select v-model="statusFilter"
                                class="px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm min-w-[120px]">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="sold">Sold</option>
                                <option value="expired">Expired</option>
                            </select>

                            <!-- Price Range -->
                            <div class="flex items-center gap-1">
                                <input v-model="minPrice" type="number" placeholder="Min"
                                    class="w-20 px-2 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm" />
                                <span class="text-gray-500">-</span>
                                <input v-model="maxPrice" type="number" placeholder="Max"
                                    class="w-20 px-2 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-teal/20 focus:border-brand-teal outline-none transition text-sm" />
                            </div>

                            <!-- Apply Filters Button -->
                            <button @click="applyFilters"
                                class="px-4 py-2.5 bg-brand-teal text-white rounded-lg hover:from-brand-teal/90 hover:to-brand-blue/90 transition text-sm font-medium">
                                Apply
                            </button>

                            <!-- Reset Filters -->
                            <button @click="resetFilters" v-if="isFiltered"
                                class="px-4 py-2.5 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                                Reset
                            </button>
                        </div>
                    </div>

                    <!-- Active Filters Tags -->
                    <div v-if="isFiltered" class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
                        <span v-if="categoryFilter"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-teal/10 text-brand-teal rounded-full text-xs">
                            Category: {{ getCategoryName(categoryFilter) }}
                            <button @click="categoryFilter = ''" class="hover:text-brand-teal/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="brandFilter"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-blue/10 text-brand-blue rounded-full text-xs">
                            Brand: {{ getBrandName(brandFilter) }}
                            <button @click="brandFilter = ''" class="hover:text-brand-blue/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="statusFilter"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs">
                            Status: {{ statusFilter }}
                            <button @click="statusFilter = ''" class="hover:text-gray-900">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="minPrice || maxPrice"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-teal/10 text-brand-teal rounded-full text-xs">
                            Price: {{ minPrice || '0' }} - {{ maxPrice || 'Any' }}
                            <button @click="minPrice = ''; maxPrice = ''" class="hover:text-brand-teal/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>

                        <span v-if="searchQuery"
                            class="inline-flex items-center gap-1 px-3 py-1 bg-brand-teal/10 text-brand-teal rounded-full text-xs">
                            Search: "{{ searchQuery }}"
                            <button @click="searchQuery = ''" class="hover:text-brand-teal/80">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Ads Grid View -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5">
                <div v-for="ad in filteredAds" :key="ad.id"
                    class="group bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-200">

                    <!-- Image Container -->
                    <div class="relative aspect-square bg-gray-100">
                        <img v-if="ad.images?.length"
                            :src="`/storage/${ad.images.find(img => img.is_primary)?.path || ad.images[0].path}`"
                            :alt="ad.ad_title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>

                        <!-- Status Badge -->
                        <div class="absolute top-2 left-2">
                            <span :class="{
                                'bg-green-500': ad.status === 'active',
                                'bg-yellow-500': ad.status === 'pending',
                                'bg-gray-500': ad.status === 'sold' || ad.status === 'expired'
                            }" class="px-2 py-1 text-xs font-medium text-white rounded-md shadow-sm">
                                {{ ad.status || 'active' }}
                            </span>
                        </div>

                        <!-- Action Buttons Overlay -->
                        <div
                            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2">
                            <button @click="editAd(ad.id)"
                                class="p-2 bg-white rounded-lg hover:bg-brand-teal hover:text-white transition-colors shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            <button @click="viewAd(ad.id)"
                                class="p-2 bg-white rounded-lg hover:bg-brand-blue hover:text-white transition-colors shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <button @click="confirmDeleteAd(ad)"
                                class="p-2 bg-white rounded-lg hover:bg-red-600 hover:text-white transition-colors shadow-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Ad Details -->
                    <div class="p-3">
                        <!-- Price -->
                        <div class="text-lg font-bold text-brand-teal mb-1">
                            {{ formatPrice(ad.price) }}
                        </div>

                        <!-- Title -->
                        <h3 class="font-medium text-gray-900 mb-1 line-clamp-2 text-sm">
                            {{ ad.ad_title }}
                        </h3>

                        <!-- Category & Brand -->
                        <div class="flex flex-wrap gap-1 mb-2">
                            <span v-if="ad.category"
                                class="px-2 py-0.5 bg-brand-teal/10 text-brand-teal rounded text-xs">
                                {{ ad.category.name }}
                            </span>
                            <span v-if="ad.brand" class="px-2 py-0.5 bg-brand-blue/10 text-brand-blue rounded text-xs">
                                {{ ad.brand.name }}
                            </span>
                        </div>

                        <!-- Location & Date -->
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ ad.location || 'Location not set' }}
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ formatDate(ad.created_at) }}
                            </div>
                        </div>

                        <!-- Views -->
                        <div class="flex items-center gap-1 mt-2 text-xs text-gray-500">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            {{ ad.views || 0 }} views
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Ads State -->
            <div v-if="filteredAds.length === 0"
                class="text-center py-12 bg-white rounded-xl shadow-sm border border-gray-100">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">No ads found</h3>
                <p class="text-sm text-gray-600 mb-4">Get started by posting your first ad</p>
                <Link :href="route('user.ads.create')"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-teal to-brand-blue text-white px-4 py-2 rounded-lg hover:from-brand-teal/90 hover:to-brand-blue/90 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Post Your First Ad
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="mt-8 flex items-center justify-center gap-2">
                <button @click="changePage(pagination.current_page - 1)" :disabled="!pagination.prev_page_url"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition">
                    Previous
                </button>

                <div class="flex gap-1">
                    <button v-for="page in pagination.last_page" :key="page" @click="changePage(page)"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-sm transition" :class="page === pagination.current_page
                            ? 'bg-gradient-to-r from-brand-teal to-brand-blue text-white'
                            : 'border border-gray-300 hover:bg-gray-50'">
                        {{ page }}
                    </button>
                </div>

                <button @click="changePage(pagination.current_page + 1)" :disabled="!pagination.next_page_url"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition">
                    Next
                </button>
            </div>
        </div>
    </OlxLayout>

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
                                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-2 text-center sm:mt-0 sm:ml-3 sm:text-left">
                                <h3 class="text-base font-medium text-gray-900">Delete Ad</h3>
                                <div class="mt-1">
                                    <p class="text-xs text-gray-500">
                                        Are you sure you want to delete "{{ adToDelete?.ad_title }}"? This action cannot
                                        be undone.
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
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import OlxLayout from '@/layouts/OlxLayout.vue'

const page = usePage()

interface Props {
    ads: {
        data: any[]
        current_page: number
        last_page: number
        total: number
        next_page_url: string | null
        prev_page_url: string | null
    }
    categories: any[]
    brands: any[]
}

const props = defineProps<Props>()

// Filter states
const searchQuery = ref('')
const categoryFilter = ref('')
const brandFilter = ref('')
const statusFilter = ref('')
const minPrice = ref('')
const maxPrice = ref('')

// Delete modal state
const showDeleteModal = ref(false)
const adToDelete = ref<any>(null)

// Computed stats
const totalAds = computed(() => props.ads?.total || 0)
const activeAds = computed(() => props.ads?.data?.filter(ad => ad.status === 'active').length || 0)
const pendingAds = computed(() => props.ads?.data?.filter(ad => ad.status === 'pending').length || 0)
const totalViews = computed(() => props.ads?.data?.reduce((sum, ad) => sum + (ad.views || 0), 0) || 0)

// Check if any filters are applied
const isFiltered = computed(() => {
    return searchQuery.value || categoryFilter.value || brandFilter.value || statusFilter.value || minPrice.value || maxPrice.value
})

// Filtered ads based on local filters
const filteredAds = computed(() => {
    let filtered = props.ads?.data || []

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(ad =>
            ad.ad_title?.toLowerCase().includes(query) ||
            ad.description?.toLowerCase().includes(query) ||
            ad.location?.toLowerCase().includes(query)
        )
    }

    if (categoryFilter.value) {
        filtered = filtered.filter(ad => ad.category?.id == categoryFilter.value)
    }

    if (brandFilter.value) {
        filtered = filtered.filter(ad => ad.brand?.id == brandFilter.value)
    }

    if (statusFilter.value) {
        filtered = filtered.filter(ad => ad.status === statusFilter.value)
    }

    if (minPrice.value) {
        filtered = filtered.filter(ad => ad.price >= Number(minPrice.value))
    }

    if (maxPrice.value) {
        filtered = filtered.filter(ad => ad.price <= Number(maxPrice.value))
    }

    return filtered
})

// Helper functions
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price)
}

const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const getCategoryName = (id: string) => {
    return props.categories?.find(c => c.id == id)?.name || id
}

const getBrandName = (id: string) => {
    return props.brands?.find(b => b.id == id)?.name || id
}

// Actions
const applyFilters = () => {
    // In a real app, you'd make an API call here
    console.log('Applying filters:', {
        search: searchQuery.value,
        category: categoryFilter.value,
        brand: brandFilter.value,
        status: statusFilter.value,
        minPrice: minPrice.value,
        maxPrice: maxPrice.value
    })
}

const resetFilters = () => {
    searchQuery.value = ''
    categoryFilter.value = ''
    brandFilter.value = ''
    statusFilter.value = ''
    minPrice.value = ''
    maxPrice.value = ''
}

const changePage = (page: number) => {
    router.get(route('my-ads'), { page }, {
        preserveState: true,
        preserveScroll: true
    })
}

const editAd = (adId: number) => {
    router.get(route('user.ads.edit', { id: adId }))
}

const viewAd = (adId: number) => {
    router.get(route('ads.show', { id: adId }))
}

const confirmDeleteAd = (ad: any) => {
    adToDelete.value = ad
    showDeleteModal.value = true
}

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

// Debounce search
let searchTimeout: NodeJS.Timeout
watch(searchQuery, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 500)
})
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>