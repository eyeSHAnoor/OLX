<template>
    <OlxLayout>
        <div class="min-h-screen bg-gray-50">
            <!-- Loading State -->
            <div v-if="!ad" class="flex items-center justify-center min-h-[60vh]">
                <div class="text-center">
                    <Icon icon="lucide:loader-2" class="size-8 animate-spin text-primary mx-auto mb-4" />
                    <p class="text-muted-foreground">Loading ad details...</p>
                </div>
            </div>

            <div v-else class="container mx-auto px-4 py-8 max-w-7/8">
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 text-sm text-muted-foreground mb-6">
                    <a href="/" class="hover:text-primary transition-colors">Home</a>
                    <Icon icon="lucide:chevron-right" class="size-4" />
                    <a :href="route('category.show', ad.category.slug)" class="hover:text-primary transition-colors">
                        {{ ad.category.name }}
                    </a>
                    <Icon icon="lucide:chevron-right" class="size-4" />
                    <span class="text-foreground font-medium truncate">{{ ad.ad_title }}</span>
                </nav>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Images and Main Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Image Gallery -->
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <!-- Main Image -->
                            <div class="relative aspect-[16/9] bg-gray-100">
                                <img v-if="selectedImage" :src="`/storage/${selectedImage}`" :alt="ad.ad_title"
                                    class="w-full h-full object-contain" />
                                <div v-else class="flex items-center justify-center h-full">
                                    <Icon icon="lucide:image" class="size-12 text-gray-400" />
                                </div>

                                <!-- Image Navigation -->
                                <button v-if="ad.images?.length > 1" @click="prevImage"
                                    class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white p-2 rounded-full shadow-lg transition-all">
                                    <Icon icon="lucide:chevron-left" class="size-5" />
                                </button>
                                <button v-if="ad.images?.length > 1" @click="nextImage"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white p-2 rounded-full shadow-lg transition-all">
                                    <Icon icon="lucide:chevron-right" class="size-5" />
                                </button>

                                <!-- Image Counter -->
                                <div v-if="ad.images?.length > 1"
                                    class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/75 text-white px-3 py-1 rounded-full text-sm">
                                    {{ currentImageIndex + 1 }} / {{ ad.images.length }}
                                </div>

                                <!-- Favorite Button -->
                                <button
                                    class="absolute top-4 right-4 bg-white p-2 rounded-full shadow-lg hover:scale-110 transition-transform">
                                    <Icon icon="lucide:heart" class="size-5 text-gray-600" />
                                </button>
                            </div>

                            <!-- Thumbnail Strip -->
                            <div v-if="ad.images?.length > 1" class="p-4 border-t">
                                <div class="flex gap-2 overflow-x-auto pb-2">
                                    <button v-for="(image, index) in ad.images" :key="image.id"
                                        @click="selectImage(index)"
                                        class="relative flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all"
                                        :class="currentImageIndex === index ? 'border-primary' : 'border-transparent hover:border-gray-300'">
                                        <img :src="`/storage/${image.path}`" :alt="`Thumbnail ${index + 1}`"
                                            class="w-full h-full object-cover" />
                                        <div v-if="image.is_primary"
                                            class="absolute top-1 left-1 bg-primary text-white text-xs px-1.5 py-0.5 rounded">
                                            Primary
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Ad Details Card -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h1 class="text-2xl font-bold mb-4">{{ ad.ad_title }}</h1>

                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-4">
                                    <span class="text-3xl font-bold text-primary">
                                        Rs. {{ Number(ad.price).toLocaleString() }}
                                    </span>
                                    <span
                                        class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                                        {{ ad.is_featured ? 'Featured' : 'Regular' }}
                                    </span>
                                </div>
                                <span class="text-sm text-muted-foreground">
                                    Posted: {{ formatDate(ad.created_at) }}
                                </span>
                            </div>

                            <!-- Key Details Grid -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg mb-6">
                                <div>
                                    <p class="text-xs text-muted-foreground mb-1">Brand</p>
                                    <p class="font-medium">{{ ad.brand?.name || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground mb-1">Category</p>
                                    <p class="font-medium">{{ ad.category?.name || 'Not specified' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground mb-1">Location</p>
                                    <p class="font-medium">{{ ad.location }}, {{ ad.city }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground mb-1">Seller</p>
                                    <p class="font-medium">{{ ad.seller_name }}</p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <h2 class="text-lg font-semibold mb-3">Description</h2>
                                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ ad.description }}</p>
                            </div>

                            <!-- Features/Specifications -->
                            <div v-if="ad.features?.length" class="border-t pt-6">
                                <h2 class="text-lg font-semibold mb-4">Features & Specifications</h2>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="feature in ad.features" :key="feature.id"
                                        class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                        <Icon icon="lucide:check-circle"
                                            class="size-5 text-green-500 flex-shrink-0 mt-0.5" />
                                        <div>
                                            <p class="text-sm text-muted-foreground">{{ feature.name }}</p>
                                            <p class="font-medium">
                                                {{ getFeatureValue(feature) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Search Keywords -->
                            <div v-if="ad.search_keywords?.length" class="border-t pt-6 mt-6">
                                <h2 class="text-lg font-semibold mb-3">Related Keywords</h2>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="keyword in ad.search_keywords.slice(0, 15)" :key="keyword"
                                        class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors cursor-default">
                                        {{ keyword }}
                                    </span>
                                    <span v-if="ad.search_keywords.length > 15"
                                        class="px-3 py-1.5 text-sm text-muted-foreground">
                                        +{{ ad.search_keywords.length - 15 }} more
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Seller Info & Actions -->
                    <div class="space-y-6">
                        <!-- Seller Card -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h2 class="text-lg font-semibold mb-4">Seller Information</h2>

                            <div class="flex items-center gap-4 mb-6">
                                <Link :href="route('user.profile', ad?.user?.id)" class="flex items-center gap-4 mb-6">
                                <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center">
                                    <Icon icon="lucide:user" class="size-8 text-primary" />
                                </div>
                                <div>
                                    <p class="font-medium text-lg">{{ ad?.user?.name }}</p>
                                    <p class="text-sm text-muted-foreground">Member since {{
                                        formatMemberSince(ad.user?.created_at) }}</p>
                                </div>
                                </Link>
                            </div>

                            <div class="space-y-3">
                                <a :href="`tel:${ad.seller_phone}`"
                                    class="flex items-center justify-between w-full p-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <Icon icon="lucide:phone" class="size-5" />
                                        <span>Call Seller</span>
                                    </span>
                                    <span class="text-sm">{{ formatPhoneNumber(ad.seller_phone) }}</span>
                                </a>

                                <button @click="copyPhoneNumber"
                                    class="flex items-center justify-center gap-2 w-full p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <Icon icon="lucide:copy" class="size-5" />
                                    <span>Copy Phone Number</span>
                                </button>

                                <button @click="openChat"
                                    class="flex items-center justify-center gap-2 w-full p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <Icon icon="lucide:message-circle" class="size-5" />
                                    <span>Chat with Seller</span>
                                </button>
                            </div>

                            <div class="mt-6 pt-6 border-t">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-muted-foreground">Ad ID:</span>
                                    <span class="font-mono">{{ ad.id }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm mt-2">
                                    <span class="text-muted-foreground">Views:</span>
                                    <span class="font-medium">{{ ad.views || 0 }} views</span>
                                </div>
                            </div>
                        </div>

                        <!-- Safety Tips Card -->
                        <div class="bg-blue-50 rounded-xl p-6">
                            <h3 class="font-semibold flex items-center gap-2 mb-3">
                                <Icon icon="lucide:shield" class="size-5 text-blue-600" />
                                Safety Tips
                            </h3>
                            <ul class="space-y-2 text-sm text-blue-800">
                                <li class="flex items-start gap-2">
                                    <Icon icon="lucide:check" class="size-4 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <span>Meet in a safe, public place</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <Icon icon="lucide:check" class="size-4 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <span>Check the item before paying</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <Icon icon="lucide:check" class="size-4 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <span>Never send money in advance</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <Icon icon="lucide:check" class="size-4 text-blue-600 mt-0.5 flex-shrink-0" />
                                    <span>Report suspicious ads</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Report Ad -->
                        <button
                            class="flex items-center justify-center gap-2 w-full p-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <Icon icon="lucide:flag" class="size-5" />
                            <span>Report this ad</span>
                        </button>
                    </div>
                </div>

                <!-- Similar Ads Section -->
                <div v-if="similarAds?.length" class="mt-12">
                    <h2 class="text-2xl font-bold mb-6">Similar Ads</h2>
                    <CategoryAds :ads="similarAds" />
                </div>
            </div>
        </div>

        <!-- Toast Notification for Copy -->
        <div v-if="showCopyToast"
            class="fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-lg shadow-lg animate-in slide-in-from-bottom">
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

interface PageProps extends InertiaPageProps {
    ad?: App.Data.AdData;
    similarAds?: App.Data.AdData[];
    categories: App.Data.CategoryData[];
    brands: App.Data.BrandData[];
}

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
        month: 'long',
        day: 'numeric'
    });
};

const formatMemberSince = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long'
    });
};

const formatPhoneNumber = (phone: string) => {
    // Format as needed, e.g., "XXXX-XXXXXXX"
    return phone;
};

const getFeatureValue = (feature: any) => {
    if (feature.pivot?.custom_value) {
        return feature.pivot.custom_value;
    }
    // Find the actual value name from feature values
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

const openChat = () => {
    if (!ad.value?.user?.id) return

    router.post('/chat/start', {
        seller_id: ad.value.user.id,
        product_id: ad.value.id
    }, {
        preserveScroll: true,
        onSuccess: (response) => {
            // Redirect to the chat
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
    height: 4px;
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