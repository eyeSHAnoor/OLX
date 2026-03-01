<template>
    <OlxLayout>
        <div class="max-w-8/10 mx-auto  space-y-12 pb-20">
            <!-- Loading State -->
            <div v-if="!ad" class="flex items-center justify-center min-h-[60vh]">
                <div class="text-center">
                    <Icon icon="lucide:loader-2" class="size-6 sm:size-8 animate-spin text-primary mx-auto mb-3" />
                    <p class="text-sm text-gray-500">Loading ad details...</p>
                </div>
            </div>

            <div v-else class="container mx-auto px-3 sm:px-4 py-4 sm:py-6 max-w-7xl">
                <!-- Breadcrumb - Compact -->
                <nav class="flex items-center gap-1.5 text-xs sm:text-sm text-gray-500 mb-4 sm:mb-5 flex-wrap">
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
                                    class="w-full h-full object-contain" />
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
                                <button
                                    class="absolute top-2 sm:top-4 right-2 sm:right-4 bg-white p-1.5 sm:p-2 rounded-full shadow-md hover:scale-110 transition-transform">
                                    <Icon icon="lucide:heart" class="size-4 sm:size-5 text-gray-600" />
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
                            <h1 class="text-md sm:text-lg lg:text-xl font-semibold mb-3 sm:mb-4">{{ ad.ad_title }}</h1>

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
                                                {{ getFeatureValue(feature) }}
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

                            <div class="space-y-2 sm:space-y-2.5">

                                <!-- WhatsApp Button - New -->
                                <a :href="getWhatsAppLink()" target="_blank" rel="noopener noreferrer"
                                    class="flex items-center justify-between w-full p-2.5 sm:p-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                                    <span class="flex items-center gap-1.5">
                                        <Icon icon="ic:baseline-whatsapp" class="size-4 sm:size-5" />
                                        <span>WhatsApp</span>
                                    </span>
                                    <span class="text-xs sm:text-sm">Chat now</span>
                                </a>

                                <!-- Chat Button -->
                                <button @click="openChat"
                                    class="flex bg-brand-blue  gap-1.5 w-full p-2.5 sm:p-3 border border-gray-300 rounded-lg hover:bg-brand-blue/85 cursor-pointer transition-colors text-sm text-white">
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

                            <!-- <div class="mt-4 sm:mt-5 pt-4 sm:pt-5 border-t">
                                <div class="flex items-center justify-between text-xs sm:text-sm">
                                    <span class="text-gray-500">Ad ID:</span>
                                    <span class="font-mono">{{ ad.id }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs sm:text-sm mt-1.5">
                                    <span class="text-gray-500">Views:</span>
                                    <span class="font-medium">{{ ad.views || 0 }} views</span>
                                </div>
                            </div> -->
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
                        <button
                            class="flex items-center justify-center gap-1.5 w-full p-2.5 sm:p-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors text-sm">
                            <Icon icon="lucide:flag" class="size-4 sm:size-5" />
                            <span>Report this ad</span>
                        </button>
                    </div>
                </div>

                <!-- Similar Ads Section -->
                <div v-if="similarAds?.length" class="mt-8 sm:mt-10 lg:mt-12">
                    <h2 class="text-lg sm:text-xl lg:text-2xl font-semibold mb-4 sm:mb-5">Similar Ads</h2>
                    <CategoryAds :ads="similarAds" />
                </div>
            </div>
        </div>

        <!-- Toast Notification for Copy -->
        <div v-if="showCopyToast"
            class="fixed bottom-3 right-3 sm:bottom-4 sm:right-4 bg-gray-800 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg shadow-lg text-xs sm:text-sm animate-in slide-in-from-bottom">
            Phone number copied to clipboard!
        </div>
    </OlxLayout>
</template>

<script setup lang="ts">
import OlxLayout from '@/layouts/OlxLayout.vue'
import { usePage, router } from '@inertiajs/vue3';
import CategoryAds from '@/components/CategoryAds.vue'
import { ref, computed, onMounted } from 'vue'
import { Icon } from '@iconify/vue'
import { Link } from '@inertiajs/vue3'

interface PageProps extends InertiaPageProps {
    ad?: App.Data.AdData;
    similarAds?: App.Data.AdData[];
    categories: App.Data.CategoryData[];
    brands: App.Data.BrandData[];
}
useForceTheme('light');
const page = usePage<PageProps>();
const ad = computed(() => page.props.ad);
const similarAds = computed(() => page.props.similarAds || []);

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

const formatPhoneNumber = (phone: string) => {
    return phone;
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

    // Clean the phone number (remove spaces, dashes, etc)
    let phoneNumber = ad.value.seller_phone.replace(/[^0-9+]/g, '');

    // Ensure it has country code (default to Pakistan +92 if not present)
    if (phoneNumber.startsWith('0')) {
        phoneNumber = '92' + phoneNumber.substring(1);
    } else if (!phoneNumber.startsWith('+') && !phoneNumber.startsWith('92')) {
        phoneNumber = '92' + phoneNumber;
    }

    // Remove any '+' for WhatsApp URL
    phoneNumber = phoneNumber.replace('+', '');

    // Create message with ad details
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
        onSuccess: (response) => {
            if (response.props?.conversation_id) {
                router.visit(`/chat/${response.props.conversation_id}`)
            }
        }
    })
}

// Set initial image to primary
onMounted(() => {
    if (ad.value?.images?.length) {
        const primaryIndex = ad.value.images.findIndex(img => img.is_primary);
        if (primaryIndex !== -1) {
            currentImageIndex.value = primaryIndex;
        }
    }
});
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