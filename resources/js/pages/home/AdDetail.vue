<template>
    <OlxLayout>
        <TopCategoriesBar />
        <div class="max-w-full px-2 md:max-w-8/10 mx-auto space-y-12 pb-20">
            <!-- Loading State -->
            <div v-if="!ad" class="flex items-center justify-center min-h-[60vh]">
                <div class="text-center">
                    <Icon icon="lucide:loader-2" class="size-6 sm:size-8 animate-spin text-primary mx-auto mb-3" />
                    <p class="text-sm text-gray-500">Loading ad details...</p>
                </div>
            </div>

            <div v-else class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 max-w-7xl">
                <!-- Breadcrumb - Compact -->
                <nav class="flex items-center gap-1.5 text-xs sm:text-sm text-gray-500 mt-3 flex-wrap">
                    <button @click="goBack"
                        class="inline-flex items-center gap-1 px-3 py-2 mb-2 rounded-md border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-50 transition">
                        <Icon icon="mdi:arrow-left" class="text-base" />
                        Back
                    </button>
                    <a href="/" class="hover:text-primary transition-colors">Home</a>
                    <Icon icon="lucide:chevron-right" class="size-3 sm:size-3.5 flex-shrink-0" />
                    <a :href="route('category.show', ad.category.slug)"
                        class="hover:text-primary transition-colors truncate max-w-[120px] sm:max-w-[200px]">
                        {{ ad.category.name }}
                    </a>
                    <Icon icon="lucide:chevron-right" class="size-3 sm:size-3.5 flex-shrink-0" />
                    <span class="text-gray-700 font-medium truncate max-w-[150px] sm:max-w-[300px]">{{ ad.ad_title
                        }}</span>
                </nav>



                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
                    <!-- Left Column - Images and Main Details -->
                    <div class="lg:col-span-2 space-y-4 sm:space-y-5">
                        <!-- Image Gallery -->
                        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm overflow-hidden">
                            <!-- Main Image -->
                            <div class="relative aspect-[16/9] bg-gray-100">
                                <img v-if="selectedImage" :src="`/storage/${selectedImage}`" :alt="ad.ad_title"
                                    class="w-full h-full object-contain" @click="openLightbox(currentImageIndex)" />
                                <div v-else class="flex items-center justify-center h-full">
                                    <Icon icon="lucide:image" class="size-8 sm:size-10 text-gray-400" />
                                </div>

                                <!-- Image Navigation -->
                                <button v-if="ad.images?.length > 1" @click="prevImage"
                                    class="absolute left-2 sm:left-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white p-1.5 sm:p-2 rounded-full shadow-md transition-all">
                                    <Icon icon="lucide:chevron-left" class="size-4 sm:size-5" />
                                </button>
                                <button v-if="ad.images?.length > 1" @click="nextImage"
                                    class="absolute right-2 sm:right-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white p-1.5 sm:p-2 rounded-full shadow-md transition-all">
                                    <Icon icon="lucide:chevron-right" class="size-4 sm:size-5" />
                                </button>

                                <!-- Image Counter -->
                                <div v-if="ad.images?.length > 1"
                                    class="absolute bottom-2 sm:bottom-4 left-1/2 -translate-x-1/2 bg-black/75 text-white px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs">
                                    {{ currentImageIndex + 1 }} / {{ ad.images.length }}
                                </div>

                                <!-- Favorite Button -->
                                <button @click="toggleFavorite"
                                    class="absolute top-2 sm:top-4 right-2 sm:right-4 bg-white p-1.5 sm:p-2 rounded-full shadow-md hover:scale-110 transition-transform"
                                    :disabled="isFavoriteLoading">
                                    <Icon :icon="isFavorited ? 'mdi:heart' : 'lucide:heart'"
                                        class="size-4 sm:size-5 transition-colors" :class="[
                                            isFavorited ? 'text-red-500' : 'text-gray-600',
                                            isFavoriteLoading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                                        ]" />
                                </button>
                            </div>

                            <!-- Thumbnail Strip -->
                            <div v-if="ad.images?.length > 1" class="p-3 sm:p-4 border-t">
                                <div class="flex gap-1.5 sm:gap-2 overflow-x-auto pb-1 sm:pb-2">
                                    <button v-for="(image, index) in ad.images" :key="image.id"
                                        @click="selectImage(index)"
                                        class="relative flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-lg overflow-hidden border-2 transition-all"
                                        :class="currentImageIndex === index ? 'border-primary' : 'border-transparent hover:border-gray-300'">
                                        <img :src="`/storage/${image.path}`" :alt="`Thumbnail ${index + 1}`"
                                            class="w-full h-full object-cover" />
                                        <div v-if="image.is_primary"
                                            class="absolute top-0.5 left-0.5 bg-primary text-white text-[8px] sm:text-[10px] px-1 py-0.5 rounded">
                                            Primary
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Ad Details Card -->
                        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-5 lg:p-6">
                            <div class="flex items-start justify-between gap-3 mb-3 sm:mb-4">
                                <h1 class="text-md sm:text-lg lg:text-xl font-semibold mb-3 sm:mb-4">{{ ad.ad_title }}
                                </h1>

                                <div class="flex items-center gap-2 sm:gap-3">
                                    <button @click="!hasOrdered && ad?.user?.id !== userId && handleShowModal()"
                                        :disabled="hasOrdered || ad?.user?.id === userId"
                                        class="group relative flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg transition-all duration-200 shadow-md"
                                        :class="(hasOrdered || ad?.user?.id === userId)
                                            ? 'bg-gray-200 cursor-not-allowed opacity-60'
                                            : 'bg-white hover:bg-gray-200 hover:scale-105 active:scale-95 hover:shadow-lg'">
                                        <Icon icon="lucide:shopping-cart" class="size-4 sm:size-5" />
                                        <span class="text-xs sm:text-sm font-medium hidden sm:inline">Order</span>
                                        <!-- Tooltip for mobile -->
                                        <span
                                            class="sm:hidden absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                            Order Now
                                        </span>
                                    </button>
                                    <button @click="toggleFavorite"
                                        class="group relative flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 bg-white border-2 rounded-lg transition-all duration-200 hover:scale-105 active:scale-95 shadow-sm hover:shadow-md"
                                        :class="[
                                            isFavorited
                                                ? 'border-red-200 bg-red-50 hover:bg-red-100'
                                                : 'border-gray-200 hover:border-red-200 hover:bg-red-50',
                                            isFavoriteLoading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                                        ]" :disabled="isFavoriteLoading">
                                        <Icon :icon="isFavorited ? 'mdi:heart' : 'lucide:heart'"
                                            class="size-4 sm:size-5 transition-colors" :class="[
                                                isFavorited ? 'text-red-500' : 'text-gray-500 group-hover:text-red-500'
                                            ]" />
                                        <span class="text-xs sm:text-sm font-medium hidden sm:inline" :class="[
                                            isFavorited ? 'text-red-600' : 'text-gray-600 group-hover:text-red-600'
                                        ]">
                                            {{ isFavorited ? 'Saved' : 'Save' }}
                                        </span>
                                        <!-- Tooltip for mobile -->
                                        <span
                                            class="sm:hidden absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                                            {{ isFavorited ? 'Remove from favorites' : 'Add to favorites' }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-5">
                                <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
                                    <span class="text-md sm:text-lg lg:text-xl font-bold text-primary">
                                        Rs. {{ Number(ad.price).toLocaleString() }}
                                    </span>
                                    <span
                                        class="px-2 py-0.5 sm:px-2.5 sm:py-1 bg-green-100 text-green-700 rounded-full text-xs sm:text-sm font-medium">
                                        {{ ad.is_featured ? 'Featured' : 'Regular' }}
                                    </span>
                                </div>
                                <span class="text-xs sm:text-sm text-gray-500">
                                    Posted: {{ formatDate(ad.created_at) }}
                                </span>
                            </div>

                            <!-- Key Details Grid -->
                            <div
                                class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3 p-3 sm:p-4 bg-gray-50 rounded-lg mb-4 sm:mb-5">
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Brand</p>
                                    <p class="text-sm font-medium">{{ ad.brand?.name || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Category</p>
                                    <p class="text-sm font-medium">{{ ad.category?.name || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Location</p>
                                    <p class="text-sm font-medium">{{ ad.location }}, {{ ad.city }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Seller</p>
                                    <p class="text-sm font-medium">{{ ad.seller_name }}</p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-4 sm:mb-5">
                                <h2 class="text-base sm:text-md font-semibold mb-2 sm:mb-3">Description</h2>
                                <p class="text-xs sm:text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{
                                    ad.description }}</p>
                            </div>

                            <!-- Features/Specifications -->
                            <div v-if="ad.features?.length" class="border-t pt-4 sm:pt-5">
                                <h2 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">Features & Specifications
                                </h2>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                                    <div v-for="feature in ad.features" :key="feature.id"
                                        class="flex items-start gap-2 p-2 sm:p-3 bg-gray-50 rounded-lg">
                                        <Icon icon="lucide:check-circle"
                                            class="size-4 sm:size-5 text-green-500 flex-shrink-0 mt-0.5" />
                                        <div>
                                            <p class="text-xs text-gray-500">{{ feature.name }}</p>
                                            <p class="text-sm font-medium">
                                                {{ feature.value }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Search Keywords -->
                            <div v-if="ad.search_keywords?.length" class="border-t pt-4 sm:pt-5 mt-4 sm:mt-5">
                                <h2 class="text-base sm:text-md font-semibold mb-2 sm:mb-3">Related Keywords</h2>
                                <div class="flex flex-wrap gap-1.5 sm:gap-2">
                                    <span v-for="keyword in ad.search_keywords.slice(0, 15)" :key="keyword"
                                        class="px-2 py-1 sm:px-2.5 sm:py-1.5 bg-gray-100 text-gray-700 rounded-full text-xs hover:bg-gray-200 transition-colors cursor-default">
                                        {{ keyword }}
                                    </span>
                                    <span v-if="ad.search_keywords.length > 15" class="px-2 py-1 text-xs text-gray-500">
                                        +{{ ad.search_keywords.length - 15 }} more
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Seller Info & Actions -->
                    <div class="space-y-4 sm:space-y-5">
                        <!-- Seller Card -->
                        <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-5">
                            <h2 class="text-base sm:text-md font-semibold mb-3 sm:mb-4">Seller Information</h2>

                            <Link :href="route('user.profile', ad?.user?.id)"
                                class="flex items-center gap-3 mb-4 sm:mb-5">
                                <div
                                    class="size-12 sm:size-14 rounded-full bg-primary/10 flex items-center justify-center">
                                    <Icon icon="lucide:user" class="size-6 sm:size-7 text-primary" />
                                </div>
                                <div>
                                    <p class="font-medium text-sm sm:text-md">{{ ad?.user?.name }}</p>
                                    <p class="text-xs text-gray-500">Member since {{
                                        formatMemberSince(ad.user?.created_at) }}</p>
                                </div>
                            </Link>

                            <!-- Action Buttons -->
                            <div class="space-y-2 sm:space-y-2.5">
                                <!-- WhatsApp Button -->
                                <a :href="getWhatsAppLink()" target="_blank" rel="noopener noreferrer"
                                    class="flex items-center justify-between w-full p-2.5 sm:p-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                                    <span class="flex items-center gap-1.5">
                                        <Icon icon="ic:baseline-whatsapp" class="size-4 sm:size-5" />
                                        <span>WhatsApp</span>
                                    </span>
                                    <span class="text-xs sm:text-sm">Chat now</span>
                                </a>

                                <!-- Chat Button -->
                                <button @click="openChat" :disabled="ad?.user?.id === userId"
                                    class="flex bg-brand-blue disabled:bg-brand-blue/70 disabled:cursor-not-allowed gap-1.5 w-full p-2.5 sm:p-3 border border-gray-300 rounded-lg hover:bg-brand-blue/85 cursor-pointer transition-colors text-sm text-white">
                                    <Icon icon="lucide:message-circle" class="size-4 sm:size-5" />
                                    <span>Chat with Seller</span>
                                </button>

                                <!-- Copy Phone Button -->
                                <button @click="copyPhoneNumber"
                                    class="flex gap-1.5 w-full p-2.5 sm:p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                                    <Icon icon="lucide:copy" class="size-4 sm:size-5" />
                                    <span>Copy Phone Number</span>
                                </button>
                            </div>
                        </div>

                        <!-- Safety Tips Card -->
                        <div class="bg-blue-50 rounded-lg sm:rounded-xl p-4 sm:p-5">
                            <h3 class="font-semibold flex items-center gap-1.5 mb-2 text-sm sm:text-base">
                                <Icon icon="lucide:shield" class="size-4 sm:size-5 text-blue-600" />
                                Safety Tips
                            </h3>
                            <ul class="space-y-1.5 text-xs sm:text-sm text-blue-800">
                                <li class="flex items-start gap-1.5">
                                    <Icon icon="lucide:check"
                                        class="size-3.5 sm:size-4 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <span>Meet in a safe, public place</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <Icon icon="lucide:check"
                                        class="size-3.5 sm:size-4 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <span>Check the item before paying</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <Icon icon="lucide:check"
                                        class="size-3.5 sm:size-4 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <span>Never send money in advance</span>
                                </li>
                                <li class="flex items-start gap-1.5">
                                    <Icon icon="lucide:check"
                                        class="size-3.5 sm:size-4 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <span>Report suspicious ads</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Report Ad -->
                        <button @click="openReportModal"
                            class="flex items-center justify-center gap-1.5 w-full p-2.5 sm:p-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors text-sm cursor-pointer">
                            <Icon icon="lucide:flag" class="size-4 sm:size-5" />
                            <span>Report this ad</span>
                        </button>
                    </div>
                </div>

                <!-- Ad Rating Section - Beautifully designed at the end -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-5 lg:p-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Customer Ratings</h2>
                        <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-medium">
                            {{ adRatingCount }} {{ adRatingCount === 1 ? 'Rating' : 'Ratings' }}
                        </span>
                    </div>

                    <!-- Overall Rating Summary -->
                    <div
                        class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6 mb-6 pb-6 border-b border-gray-100">
                        <!-- Average Score -->
                        <div class="flex flex-col items-center">
                            <span class="text-4xl sm:text-5xl font-bold text-gray-900">{{ adAvgRating.toFixed(1)
                            }}</span>
                            <span class="text-xs text-gray-500 mt-1">out of 5</span>
                        </div>

                        <!-- Stars and Progress Bars -->
                        <div class="flex-1 w-full">
                            <!-- Average Stars Display -->
                            <div class="flex items-center gap-2 mb-3">
                                <div class="flex gap-1">
                                    <Icon v-for="i in 5" :key="i" icon="lucide:star" class="size-5"
                                        :class="i <= Math.round(adAvgRating) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-200'" />
                                </div>
                                <span class="text-sm text-gray-600">Average Rating</span>
                            </div>

                            <!-- Rating Distribution Bars -->
                            <div class="space-y-2">
                                <div v-for="star in [5, 4, 3, 2, 1]" :key="star"
                                    class="flex items-center gap-2 text-xs">
                                    <span class="w-8 text-gray-600">{{ star }} star</span>
                                    <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-yellow-400 rounded-full"
                                            :style="{ width: `${getRatingPercentage(star)}%` }"></div>
                                    </div>
                                    <span class="w-8 text-gray-500">{{ getRatingCount(star) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Your Rating Section -->
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Rate this ad</h3>

                        <!-- Logged in user who is not the seller -->
                        <div v-if="page.props.auth?.user && page.props.auth.user.id !== ad?.user_id"
                            class="bg-gray-50 rounded-lg p-4">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                <div class="flex gap-1">
                                    <button v-for="star in 5" :key="star" @click="submitRating(star)"
                                        @mouseover="hoverRating = star" @mouseleave="hoverRating = 0"
                                        class="focus:outline-none transition-all duration-200 hover:scale-110"
                                        :disabled="isSubmitting">
                                        <Icon icon="lucide:star" class="size-6 transition-colors" :class="[
                                            star <= (hoverRating || userCurrentRating)
                                                ? 'text-yellow-400 fill-yellow-400'
                                                : 'text-gray-300 hover:text-yellow-200',
                                            isSubmitting ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                                        ]" />
                                    </button>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span v-if="userCurrentRating > 0" class="text-sm text-gray-600">
                                        You rated this <span class="font-medium text-gray-900">{{ userCurrentRating
                                        }}/5</span>
                                    </span>
                                    <span v-else class="text-sm text-gray-500">Click a star to rate</span>

                                    <Icon v-if="isSubmitting" icon="lucide:loader-2"
                                        class="size-5 animate-spin text-primary" />
                                </div>
                            </div>
                        </div>

                        <!-- Message for logged out users -->
                        <div v-else-if="!page.props.auth?.user" class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-600">
                                <Link href="/login" class="text-primary font-medium hover:underline">Sign in</Link>
                                to rate this ad and help others make informed decisions
                            </p>
                        </div>

                        <!-- Message for ad owner -->
                        <div v-else-if="page.props.auth?.user && page.props.auth.user.id === ad?.user_id"
                            class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-600">
                                <Icon icon="lucide:info" class="size-4 inline-block mr-1 text-gray-400" />
                                You cannot rate your own ad
                            </p>
                        </div>
                    </div>

                    <!-- Recent Ratings -->
                    <div v-if="recentRatings.length > 0">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-gray-700">Recent ratings</h3>
                            <Link v-if="adRatingCount > 3" :href="route('ad.ratings', ad?.id)"
                                class="text-xs text-primary hover:underline">
                                View all
                            </Link>
                        </div>

                        <div class="space-y-3">
                            <div v-for="rating in recentRatings.slice(0, 3)" :key="rating.id"
                                class="flex items-start justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <Link :href="route('user.profile', rating.rater?.id)"
                                            class="font-medium text-sm hover:text-primary transition-colors">
                                            {{ rating.rater?.name || 'Anonymous' }}
                                        </Link>
                                        <span class="text-xs text-gray-500">•</span>
                                        <span class="text-xs text-gray-500">{{ formatRelativeTime(rating.created_at)
                                        }}</span>
                                    </div>
                                    <div class="flex gap-1">
                                        <Icon v-for="i in 5" :key="i" icon="lucide:star" class="size-3.5"
                                            :class="i <= rating.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-200'" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No ratings yet -->
                    <div v-else class="text-center py-6">
                        <Icon icon="lucide:star" class="size-10 text-gray-300 mx-auto mb-2" />
                        <p class="text-sm text-gray-500">No ratings yet. Be the first to rate this ad!</p>
                    </div>
                </div>

                <!-- Similar Ads Section -->
                <div v-if="similarAds?.length" class="mt-8 sm:mt-10 lg:mt-12">
                    <h2 class="text-lg sm:text-xl lg:text-2xl font-semibold mb-4 sm:mb-5">Similar Ads</h2>
                    <CategoryAds :ads="similarAds" />
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <div v-if="showCopyToast"
            class="fixed bottom-3 right-3 sm:bottom-4 sm:right-4 bg-gray-800 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg shadow-lg text-xs sm:text-sm animate-in slide-in-from-bottom">
            Phone number copied to clipboard!
        </div>

        <div v-if="showToast"
            class="fixed bottom-3 right-3 sm:bottom-4 sm:right-4 bg-gray-800 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg shadow-lg text-xs sm:text-sm animate-in slide-in-from-bottom">
            {{ toastMessage }}
        </div>
        <!-- Report Modal -->
        <ReportModal v-model="showReportModal" :ad="ad" :reasons="reportReasons" @submitted="handleReportSubmitted" />
        <OrderModal v-model="showModal" :ad="ad" @order-placed="handleOrderPlaced"
            @success="showToastMessage('Order placed successfully! The seller has been notified.')" />
        <!-- Lightbox Modal -->
        <Teleport to="body">
            <div v-if="lightboxOpen" class="fixed inset-0 z-[9999] bg-black/95 flex items-center justify-center"
                style="height: 100dvh; width: 100dvw;" @click.self="closeLightbox">
                <!-- Close button (always visible) -->
                <button @click="closeLightbox"
                    class="absolute top-4 right-4 z-20 text-white bg-black/50 rounded-full p-2 hover:bg-black/70 transition touch-manipulation">
                    <Icon icon="lucide:x" class="size-6" />
                </button>

                <!-- Navigation arrows (larger hit area for mobile) -->
                <button v-if="ad?.images?.length > 1" @click="prevImageLightbox"
                    class="absolute left-2 top-1/2 -translate-y-1/2 text-white bg-black/50 rounded-full p-3 hover:bg-black/70 transition touch-manipulation">
                    <Icon icon="lucide:chevron-left" class="size-8" />
                </button>
                <button v-if="ad?.images?.length > 1" @click="nextImageLightbox"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-white bg-black/50 rounded-full p-3 hover:bg-black/70 transition touch-manipulation">
                    <Icon icon="lucide:chevron-right" class="size-8" />
                </button>

                <!-- Zoom controls (bottom center) -->
                <div
                    class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-4 bg-black/60 rounded-full px-5 py-2 backdrop-blur-sm z-10">
                    <button @click="zoomOut" class="text-white text-2xl font-bold px-2 touch-manipulation">−</button>
                    <button @click="resetZoomAndPan" class="text-white text-sm px-3">Reset</button>
                    <button @click="zoomIn" class="text-white text-2xl font-bold px-2 touch-manipulation">+</button>
                </div>

                <!-- Image counter -->
                <div v-if="ad?.images?.length > 1"
                    class="absolute top-4 left-1/2 -translate-x-1/2 text-white bg-black/50 px-4 py-1.5 rounded-full text-sm z-10">
                    {{ lightboxIndex + 1 }} / {{ ad.images.length }}
                </div>

                <!-- Full-screen draggable image container -->
                <div class="w-full h-full flex items-center justify-center overflow-hidden touch-none"
                    @mousedown="startDrag" @mousemove="onDrag" @mouseup="stopDrag" @mouseleave="stopDrag"
                    @touchstart="startDrag" @touchmove="onDrag" @touchend="stopDrag"
                    @wheel.prevent="(e) => { if (e.deltaY < 0) zoomIn(); else zoomOut(); }">
                    <img :src="`/storage/${ad.images[lightboxIndex]?.path}`" :alt="ad.ad_title"
                        class="max-w-[95vw] max-h-[95vh] object-contain transition-transform duration-200 select-none"
                        :style="{
                            transform: `translate(${pan.x}px, ${pan.y}px) scale(${zoomLevel})`,
                            cursor: zoomLevel > 1 ? 'grab' : 'default'
                        }" draggable="false" />
                </div>
            </div>
        </Teleport>
    </OlxLayout>
</template>

<script setup lang="ts">
import OlxLayout from '@/layouts/OlxLayout.vue'
import { usePage, router } from '@inertiajs/vue3';
import CategoryAds from '@/components/CategoryAds.vue'
import TopCategoriesBar from '@/components/TopCategoriesBar.vue'
import ReportModal from './_partials/ReportModal.vue';
import { ref, computed, onMounted } from 'vue'
import { Icon } from '@iconify/vue'
import { Link } from '@inertiajs/vue3'
import OrderModal from './_partials/OrderModal.vue'

interface PageProps extends InertiaPageProps {
    ad?: any;
    similarAds?: any[];
    categories?: any[];
    brands?: any[];
    auth?: {
        user?: {
            id: number;
            name: string;
        }
    }
}

// Theme
const useForceTheme = (theme: string) => {
    document.documentElement.setAttribute('data-theme', theme);
};
useForceTheme('light');

const page = usePage<PageProps>();
const ad = computed(() => page.props.ad);
const similarAds = computed(() => page.props.similarAds || []);
const hasOrdered = computed(() => page.props.hasOrdered || false)
const userId = computed(() => page.props.auth?.user?.id || null)
const { handleShowModal, showModal, selectedItem } = useModal();

const lightboxOpen = ref(false)
const lightboxIndex = ref(0)
const zoomLevel = ref(1)
const pan = ref({ x: 0, y: 0 })
const isDragging = ref(false)
const dragStart = ref({ x: 0, y: 0, panX: 0, panY: 0 })

// Zoom limits
const MIN_ZOOM = 1
const MAX_ZOOM = 3

// Open lightbox at a specific image index
const openLightbox = (index: number) => {
    lightboxIndex.value = index
    lightboxOpen.value = true
    resetZoomAndPan()
    document.body.style.overflow = 'hidden'
}

// Close lightbox
const closeLightbox = () => {
    lightboxOpen.value = false
    document.body.style.overflow = ''
    resetZoomAndPan()
}

// Reset zoom and pan
const resetZoomAndPan = () => {
    zoomLevel.value = 1
    pan.value = { x: 0, y: 0 }
}

// Navigation
const nextImageLightbox = () => {
    if (!ad.value?.images?.length) return
    lightboxIndex.value = (lightboxIndex.value + 1) % ad.value.images.length
    resetZoomAndPan()
}

const prevImageLightbox = () => {
    if (!ad.value?.images?.length) return
    lightboxIndex.value = (lightboxIndex.value - 1 + ad.value.images.length) % ad.value.images.length
    resetZoomAndPan()
}

// Zoom controls
const zoomIn = () => {
    zoomLevel.value = Math.min(MAX_ZOOM, zoomLevel.value + 0.25)
}

const zoomOut = () => {
    zoomLevel.value = Math.max(MIN_ZOOM, zoomLevel.value - 0.25)
    if (zoomLevel.value === 1) pan.value = { x: 0, y: 0 }
}

// Handle mouse/touch drag for panning when zoomed
const startDrag = (e: MouseEvent | TouchEvent) => {
    if (zoomLevel.value === 1) return
    isDragging.value = true
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY
    dragStart.value = {
        x: clientX,
        y: clientY,
        panX: pan.value.x,
        panY: pan.value.y,
    }
}

const onDrag = (e: MouseEvent | TouchEvent) => {
    if (!isDragging.value) return
    e.preventDefault()
    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY
    const dx = clientX - dragStart.value.x
    const dy = clientY - dragStart.value.y
    pan.value = {
        x: dragStart.value.panX + dx,
        y: dragStart.value.panY + dy,
    }
}

const stopDrag = () => {
    isDragging.value = false
}

// Keyboard navigation
const handleKeydown = (e: KeyboardEvent) => {
    if (!lightboxOpen.value) return
    if (e.key === 'ArrowLeft') prevImageLightbox()
    if (e.key === 'ArrowRight') nextImageLightbox()
    if (e.key === 'Escape') closeLightbox()
    if (e.key === '+' || e.key === '=') zoomIn()
    if (e.key === '-') zoomOut()
    if (e.key === '0') resetZoomAndPan()
}

// Watch for keydown events
onMounted(() => {
    window.addEventListener('keydown', handleKeydown)
})
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown)
})

const handleOrderPlaced = () => {
    // Optional: Do something specific when order is placed
    //console.log('Order placed successfully');
}
//console.log(page.props)
// Favorite state
const isFavorited = ref(false)
const isFavoriteLoading = ref(false)

// Report modal state - use ref for v-model
const showReportModal = ref(false)

const reportReasons = {
    scam: 'Scam or Fraud',
    spam: 'Spam',
    abusive: 'Abusive Behavior',
    fake_listing: 'Fake Listing',
    inappropriate: 'Inappropriate Content',
    other: 'Other',
}

const openReportModal = () => {
    if (!page.props.auth?.user) {
        router.visit('/login')
        return
    }
    showReportModal.value = true
}

const handleReportSubmitted = () => {
    showToastMessage('Report submitted successfully')
}

// Check if ad is favorited by current user
const checkIfFavorited = () => {
    if (!page.props.auth?.user || !ad.value) return false
    return ad.value?.is_favorited || false
}

// Initialize favorite state
onMounted(() => {
    isFavorited.value = checkIfFavorited()

    if (ad.value?.images?.length) {
        const primaryIndex = ad.value.images.findIndex((img: any) => img.is_primary);
        if (primaryIndex !== -1) {
            currentImageIndex.value = primaryIndex;
        }
    }
})

// Toggle favorite
const toggleFavorite = () => {
    if (!page.props.auth?.user) {
        router.visit('/login')
        return
    }

    if (isFavoriteLoading.value) return

    isFavoriteLoading.value = true

    router.post(route('ads.favorite', ad.value?.id), {}, {
        preserveScroll: true,
        onSuccess: (response: any) => {
            isFavorited.value = !isFavorited.value
            isFavoriteLoading.value = false
            showToastMessage(isFavorited.value ? 'Added to favorites' : 'Removed from favorites')
        },
        onError: (errors) => {
            isFavoriteLoading.value = false
            showToastMessage('Failed to update favorite')
            console.error(errors)
        }
    })
}
const goBack = () => {
    window.history.back()
}
// Rating state for ad
const hoverRating = ref(0)
const isSubmitting = ref(false)

// Toast state
const showToast = ref(false)
const toastMessage = ref('')

// Computed properties for ad ratings
const adAvgRating = computed(() => {
    if (!ad.value?.ratings || ad.value.ratings.length === 0) return 0
    const sum = ad.value.ratings.reduce((acc: number, curr: any) => acc + curr.rating, 0)
    return sum / ad.value.ratings.length
})

const adRatingCount = computed(() => {
    return ad.value?.ratings?.length || 0
})

// Check if current user has rated this ad
const userCurrentRating = computed(() => {
    if (!ad.value?.ratings || !page.props.auth?.user?.id) return 0
    const userRating = ad.value.ratings.find(
        (r: any) => r.rater_id === page.props.auth?.user?.id
    )
    return userRating?.rating || 0
})

// Get recent ratings for this ad
const recentRatings = computed(() => {
    if (!ad.value?.ratings) return []
    return [...ad.value.ratings]
        .sort((a: any, b: any) =>
            new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
        )
})

// Rating distribution
const ratingDistribution = computed(() => {
    if (!ad.value?.ratings) return { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 }

    const distribution = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 }
    ad.value.ratings.forEach((r: any) => {
        distribution[r.rating as keyof typeof distribution]++
    })
    return distribution
})

const getRatingPercentage = (star: number) => {
    if (adRatingCount.value === 0) return 0
    return (ratingDistribution.value[star as keyof typeof ratingDistribution.value] / adRatingCount.value) * 100
}

const getRatingCount = (star: number) => {
    return ratingDistribution.value[star as keyof typeof ratingDistribution.value] || 0
}

// Submit rating for this ad (no review required)
const submitRating = (rating: number) => {
    if (!page.props.auth?.user) {
        router.visit('/login')
        return
    }

    if (page.props.auth.user.id === ad.value?.user_id) {
        showToastMessage('You cannot rate your own ad')
        return
    }

    if (isSubmitting.value) return

    isSubmitting.value = true

    router.post(route('ratings.store'), {
        rated_user_id: ad.value?.user_id,
        ad_id: ad.value?.id,
        rating: rating,
        review: null
    }, {
        preserveScroll: true,
        onSuccess: () => {
            hoverRating.value = 0
            isSubmitting.value = false
            showToastMessage('Rating submitted successfully!')
        },
        onError: (errors) => {
            isSubmitting.value = false
            showToastMessage('Failed to submit rating')
            console.error(errors)
        }
    })
}

// Show toast message
const showToastMessage = (message: string) => {
    toastMessage.value = message
    showToast.value = true
    setTimeout(() => {
        showToast.value = false
    }, 2000)
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

    return formatDate(dateString)
}

// Image Gallery State
const currentImageIndex = ref(0);
const selectedImage = computed(() => {
    if (!ad.value?.images?.length) return null;
    return ad.value.images[currentImageIndex.value]?.path;
});

const selectImage = (index: number) => {
    currentImageIndex.value = index;
};

const nextImage = () => {
    if (!ad.value?.images?.length) return;
    currentImageIndex.value = (currentImageIndex.value + 1) % ad.value.images.length;
};

const prevImage = () => {
    if (!ad.value?.images?.length) return;
    currentImageIndex.value = (currentImageIndex.value - 1 + ad.value.images.length) % ad.value.images.length;
};

// Helper Functions
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatMemberSince = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short'
    });
};

const getFeatureValue = (feature: any) => {
    if (feature.pivot?.custom_value) {
        return feature.pivot.custom_value;
    }
    const value = feature.values?.find((v: any) => v.id === feature.pivot?.feature_value_id);
    return value?.value || 'Not specified';
};

// Copy phone number
const showCopyToast = ref(false);

const copyPhoneNumber = () => {
    if (ad.value?.seller_phone) {
        navigator.clipboard.writeText(ad.value.seller_phone);
        showCopyToast.value = true;
        setTimeout(() => {
            showCopyToast.value = false;
        }, 2000);
    }
};

// WhatsApp function
const getWhatsAppLink = () => {
    if (!ad.value?.seller_phone) return '#';

    // Clean the phone number
    let phoneNumber = ad.value.seller_phone.replace(/[^0-9+]/g, '');

    // Ensure it has country code
    if (phoneNumber.startsWith('0')) {
        phoneNumber = '92' + phoneNumber.substring(1);
    } else if (!phoneNumber.startsWith('+') && !phoneNumber.startsWith('92')) {
        phoneNumber = '92' + phoneNumber;
    }

    phoneNumber = phoneNumber.replace('+', '');

    const message = encodeURIComponent(
        `Hi, I'm interested in your ad: ${ad.value.ad_title}\n` +
        `Price: Rs. ${Number(ad.value.price).toLocaleString()}\n` +
        `Link: ${window.location.href}`
    );

    return `https://wa.me/${phoneNumber}?text=${message}`;
};

const openChat = () => {
    if (!ad.value?.user?.id) return

    router.post('/chat/start', {
        seller_id: ad.value.user.id,
        product_id: ad.value.id
    }, {
        preserveScroll: true,
        onSuccess: (response: any) => {
            if (response.props?.conversation_id) {
                router.visit(`/chat/${response.props.conversation_id}`)
            }
        }
    })
}

const orderAd = () => {
    if (!page.props.auth?.user) {
        router.visit('/login');
        return;
    }

    router.post(route('orders.store'), {
        ad_id: ad.value?.id,
        qty: 1 // optional, you can make it dynamic later
    }, {
        preserveScroll: true,
        onSuccess: (response) => {
            showToastMessage('Item is ordered! Owner has been notified.');
        },
        onError: (errors) => {
            console.error(errors);
            showToastMessage('Failed to place order.');
        }
    });
}
</script>

<style scoped>
/* Smooth transitions */
img {
    transition: opacity 0.3s ease;
}

/* Custom scrollbar for thumbnails */
.overflow-x-auto::-webkit-scrollbar {
    height: 3px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Animation for toast */
@keyframes slideIn {
    from {
        transform: translateY(100%);
        opacity: 0;
    }

    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-in {
    animation: slideIn 0.3s ease-out;
}
</style>