<template>
    <OlxLayout>
        <div class="max-w-8/10 mx-auto  space-y-12 py-10">
            <!-- Header -->
            <div class="mb-8">
                <!-- <button @click="router.visit(route('ads.index'))"
                    class="flex items-center text-gray-600 hover:text-gray-900 mb-4 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Ads
                </button> -->

                <div class="border-b border-gray-200 pb-4">
                    <h2 class="text-3xl font-light text-gray-900">Edit Ad</h2>
                    <p class="text-gray-500 mt-1 text-sm">Update the details of your advertisement</p>
                </div>
            </div>

            <!-- Category + Brand Summary -->
            <div v-if="ad" class="bg-gray-50 rounded-lg p-4 mb-8 flex items-center justify-between text-sm">
                <div class="flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-500">Category:</span>
                        <span class="font-medium text-gray-900">{{ ad.category?.name || 'N/A' }}</span>
                    </div>

                    <div v-if="ad.brand" class="flex items-center space-x-2">
                        <span class="text-gray-500">Brand:</span>
                        <span class="font-medium text-gray-900">{{ ad.brand.name }}</span>
                    </div>
                </div>

                <!-- Ad Status Badge -->
                <div class="flex items-center gap-2">
                    <span class="px-2 py-1 text-xs font-medium rounded-full" :class="{
                        'bg-green-100 text-green-800': ad.status === 'active',
                        'bg-yellow-100 text-yellow-800': ad.status === 'pending',
                        'bg-red-100 text-red-800': ad.status === 'sold',
                        'bg-gray-100 text-gray-800': ad.status === 'inactive'
                    }">
                        {{ ad.status }}
                    </span>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-8">
                <!-- Basic Information Section -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-medium text-gray-900">Basic Information</h3>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Ad Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ad Title <span
                                    class="text-red-500">*</span></label>
                            <input v-model="form.ad_title" type="text"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors"
                                placeholder="e.g., iPhone 12 Pro Max - 256GB - Silver" />
                            <p v-if="form.errors.ad_title" class="text-red-500 text-xs mt-1.5">
                                {{ form.errors.ad_title }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description <span
                                    class="text-red-500">*</span></label>
                            <textarea v-model="form.description" rows="5"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors resize-none"
                                placeholder="Describe your product in detail..."></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-xs mt-1.5">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Category (Read-only since it can't be changed) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <input :value="ad?.category?.name" type="text" disabled
                                class="w-full px-4 py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-gray-500" />
                        </div>

                        <!-- Brand -->
                        <div v-if="availableBrands?.length">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Brand <span
                                    class="text-red-500">*</span></label>
                            <select v-model="form.brand_id"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors bg-white">
                                <option :value="null">Select Brand</option>
                                <option v-for="brand in availableBrands" :key="brand.id" :value="brand.id">
                                    {{ brand.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.brand_id" class="text-red-500 text-xs mt-1.5">
                                {{ form.errors.brand_id }}
                            </p>
                        </div>

                        <!-- Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price <span
                                    class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-2.5 text-gray-500">$</span>
                                <input v-model.number="form.price" type="number" min="0" step="0.01"
                                    class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors"
                                    placeholder="0.00" />
                            </div>
                            <p v-if="form.errors.price" class="text-red-500 text-xs mt-1.5">
                                {{ form.errors.price }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Location Section -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-medium text-gray-900">Location</h3>
                    </div>

                    <div class="p-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Location <span
                                        class="text-red-500">*</span></label>
                                <input v-model="form.location"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors"
                                    placeholder="e.g., DHA Phase 5" />
                                <p v-if="form.errors.location" class="text-red-500 text-xs mt-1.5">
                                    {{ form.errors.location }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City <span
                                        class="text-red-500">*</span></label>
                                <select v-model="form.city"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors bg-white">
                                    <option value="">Select City</option>
                                    <option>Lahore</option>
                                    <option>Karachi</option>
                                    <option>Islamabad</option>
                                    <option>Rawalpindi</option>
                                    <option>Faisalabad</option>
                                    <option>Multan</option>
                                </select>
                                <p v-if="form.errors.city" class="text-red-500 text-xs mt-1.5">
                                    {{ form.errors.city }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seller Information Section -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-medium text-gray-900">Seller Information</h3>
                    </div>

                    <div class="p-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Seller Name <span
                                        class="text-red-500">*</span></label>
                                <input v-model="form.seller_name"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors"
                                    placeholder="Full name" />
                                <p v-if="form.errors.seller_name" class="text-red-500 text-xs mt-1.5">
                                    {{ form.errors.seller_name }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Seller Phone <span
                                        class="text-red-500">*</span></label>
                                <input v-model="form.seller_phone"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors"
                                    placeholder="+92 XXX XXXXXXX" />
                                <p v-if="form.errors.seller_phone" class="text-red-500 text-xs mt-1.5">
                                    {{ form.errors.seller_phone }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Images Section -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-medium text-gray-900">Images</h3>
                        <p class="text-xs text-gray-500 mt-1">Manage ad images (max 10 total)</p>
                    </div>

                    <div class="p-6">
                        <!-- Image Upload Area -->
                        <div class="mb-6">
                            <input type="file" multiple accept="image/*" @change="handleImageUpload" class="hidden"
                                ref="fileInput" />
                            <button type="button" @click="$refs.fileInput.click()"
                                class="w-full px-4 py-8 border-2 border-dashed border-gray-300 rounded-lg hover:border-gray-400 transition-colors flex flex-col items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm text-gray-600">Click to add more images</span>
                                <span class="text-xs text-gray-400 mt-1">or drag and drop</span>
                            </button>
                        </div>

                        <!-- Image Preview Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <!-- Existing Images -->
                            <div v-for="image in existingImages" :key="`existing-${image.id}`" class="relative group">
                                <img :src="`/storage/${image.path}`"
                                    class="w-full aspect-square object-cover rounded-lg border border-gray-200" />
                                <div
                                    class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all rounded-lg flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                                    <button type="button" @click="setPrimaryImage(image.id)" v-if="!image.is_primary"
                                        class="bg-white text-gray-900 p-1.5 rounded-full hover:bg-gray-100 transition-colors shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="removeExistingImage(image.id)"
                                        class="bg-red-500 text-white p-1.5 rounded-full hover:bg-red-600 transition-colors shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                                <div v-if="image.is_primary"
                                    class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full shadow-lg">
                                    Primary
                                </div>
                            </div>

                            <!-- New Images -->
                            <div v-for="(preview, index) in imagePreviews" :key="`new-${index}`" class="relative group">
                                <img :src="preview"
                                    class="w-full aspect-square object-cover rounded-lg border border-gray-200" />
                                <button type="button" @click="removeImage(index)"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <div
                                    class="absolute top-2 left-2 bg-green-600 text-white text-xs px-2 py-1 rounded-full shadow-lg">
                                    New
                                </div>
                            </div>

                            <!-- Empty Placeholders -->
                            <template v-for="n in Math.max(0, 10 - totalImages)" :key="`empty-${n}`">
                                <div
                                    class="aspect-square border-2 border-dashed border-gray-200 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </template>
                        </div>

                        <p v-if="form.errors.images" class="text-red-500 text-xs mt-4">
                            {{ form.errors.images }}
                        </p>
                    </div>
                </div>

                <!-- Features Section -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="font-medium text-gray-900">Features</h3>
                            <p class="text-xs text-gray-500 mt-1">Update ad features</p>
                        </div>
                        <button type="button" @click="addFeatureRow"
                            class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Add Feature
                        </button>
                    </div>

                    <div class="p-6">
                        <div v-if="features && features.length > 0" class="space-y-4">
                            <div v-for="(feature, index) in form.features" :key="index" class="flex gap-4 items-start">
                                <div class="flex-1">
                                    <select v-model="feature.feature_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors bg-white text-sm">
                                        <option value="">Select Feature</option>
                                        <option v-for="f in features" :key="f.id" :value="f.id">
                                            {{ f.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <template v-if="feature.feature_id">
                                        <!-- Check if selected feature has values -->
                                        <template v-if="hasFeatureValues(feature.feature_id)">
                                            <!-- Show dropdown for features with predefined values -->
                                            <select v-model="feature.feature_value_id"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors bg-white text-sm">
                                                <option value="">Select Value</option>
                                                <option v-for="v in getFeatureValues(feature.feature_id)" :key="v.id"
                                                    :value="v.id">
                                                    {{ v.value }}
                                                </option>
                                            </select>
                                            <!-- Show custom input only if "Select Value" is chosen -->
                                            <input v-if="feature.feature_value_id === ''" v-model="feature.custom_value"
                                                placeholder="Enter custom value"
                                                class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors text-sm" />
                                        </template>
                                        <!-- Show input directly for features without predefined values -->
                                        <template v-else>
                                            <input v-model="feature.custom_value"
                                                :placeholder="`Enter ${getFeatureName(feature.feature_id)}`"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors text-sm" />
                                        </template>
                                    </template>
                                    <p v-else class="text-sm text-gray-400 italic">Select a feature first</p>
                                </div>
                                <button type="button" @click="removeFeatureRow(index)"
                                    class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <p v-if="form.features.length === 0" class="text-sm text-gray-500 text-center py-4">
                            No features added yet. Click "Add Feature" to get started.
                        </p>
                    </div>
                </div>

                <!-- Keywords Section -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-medium text-gray-900">Search Keywords</h3>
                        <p class="text-xs text-gray-500 mt-1">Add keywords to help people find your ad (max 20)</p>
                    </div>

                    <div class="p-6">
                        <div class="flex gap-2 mb-4">
                            <input v-model="newKeyword" @keydown.enter.prevent="addKeyword"
                                class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors"
                                placeholder="Type a keyword and press Enter" />
                            <button type="button" @click="addKeyword"
                                :disabled="!newKeyword.trim() || form.search_keywords.length >= 20"
                                class="px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Add
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span v-for="(keyword, index) in form.search_keywords" :key="index"
                                class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full text-sm">
                                {{ keyword }}
                                <button type="button" @click="removeKeyword(index)"
                                    class="ml-2 text-gray-500 hover:text-gray-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </span>
                        </div>

                        <p v-if="form.errors.search_keywords" class="text-red-500 text-xs mt-4">
                            {{ form.errors.search_keywords }}
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4 pt-4">
                    <button type="button" @click="confirmDelete" v-if="ad"
                        class="px-8 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-sm font-medium">
                        Delete Ad
                    </button>
                    <button type="submit" :disabled="form.processing"
                        class="px-8 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium">
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Ad</span>
                    </button>
                </div>
            </form>

            <!-- Delete Confirmation Modal -->
            <div v-if="showDeleteModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 max-w-md w-full">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Ad</h3>
                    <p class="text-gray-500 mb-6">Are you sure you want to delete "{{ ad?.ad_title }}"? This action
                        cannot
                        be
                        undone.</p>
                    <div class="flex justify-end gap-3">
                        <button @click="showDeleteModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button @click="deleteAd"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </OlxLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import OlxLayout from '@/layouts/OlxLayout.vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    ad: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        default: () => []
    },
    brands: {
        type: Array,
        default: () => []
    },
    features: {
        type: Array,
        default: () => []
    }
})
const page = usePage()
// Initialize form with ad data
const form = useForm({
    id: props.ad?.id,
    category_id: props.ad?.category_id,
    brand_id: props.ad?.brand_id,
    ad_title: props.ad?.ad_title || '',
    description: props.ad?.description || '',
    price: props.ad?.price || null,
    location: props.ad?.location || '',
    city: props.ad?.city || '',
    seller_name: props.ad?.seller_name || '',
    seller_phone: props.ad?.seller_phone || '',
    search_keywords: props.ad?.search_keywords || [],
    images: [],
    features: props.ad?.features?.map(f => ({
        feature_id: f.id,
        feature_value_id: f.pivot?.feature_value_id || '',
        custom_value: f.pivot?.custom_value || ''
    })) || [],
    remove_images: []
})

const newKeyword = ref('')
const imagePreviews = ref([])
const fileInput = ref(null)
const showDeleteModal = ref(false)

// Existing images from the ad
const existingImages = ref(props.ad?.images || [])

// Calculate total images for placeholder count
const totalImages = computed(() => {
    return (existingImages.value?.length || 0) + form.images.length
})

// Filter brands based on selected category
const availableBrands = computed(() => {
    if (!form.category_id) return []
    return props.brands.filter(brand =>
        brand.categories?.some(cat => cat.id === form.category_id)
    )
})

// Helper function to check if a feature has values
const hasFeatureValues = (featureId) => {
    if (!featureId || !props.features || !props.features.length) return false

    const id = Number(featureId)
    const feature = props.features.find(f => Number(f.id) === id)

    return feature?.values && feature.values.length > 0
}

// Helper function to get feature name
const getFeatureName = (featureId) => {
    if (!featureId || !props.features || !props.features.length) return 'value'

    const id = Number(featureId)
    const feature = props.features.find(f => Number(f.id) === id)

    return feature?.name?.toLowerCase() || 'value'
}

// Helper function to get feature values
const getFeatureValues = (featureId) => {
    if (!featureId || !props.features || !props.features.length) return []

    const id = Number(featureId)
    const feature = props.features.find(f => Number(f.id) === id)

    return feature?.values || []
}

// Feature management
const addFeatureRow = () => {
    form.features.push({
        feature_id: '',
        feature_value_id: '',
        custom_value: ''
    })
}

const removeFeatureRow = (index) => {
    form.features.splice(index, 1)
}

// Keyword management
const addKeyword = () => {
    const value = newKeyword.value.trim()
    if (value && !form.search_keywords.includes(value) && form.search_keywords.length < 20) {
        form.search_keywords.push(value)
        newKeyword.value = ''
    }
}

const removeKeyword = (index) => {
    form.search_keywords.splice(index, 1)
}

// Image management
const handleImageUpload = (event) => {
    const files = Array.from(event.target.files)
    const totalAfterUpload = totalImages.value + files.length

    if (totalAfterUpload > 10) {
        alert('Maximum 10 images allowed')
        return
    }

    files.forEach(file => {
        if (file.type.startsWith('image/')) {
            form.images.push(file)
            const reader = new FileReader()
            reader.onload = e => imagePreviews.value.push(e.target.result)
            reader.readAsDataURL(file)
        }
    })
    event.target.value = ''
}

const removeImage = (index) => {
    form.images.splice(index, 1)
    imagePreviews.value.splice(index, 1)
}

const removeExistingImage = (imageId) => {
    if (!form.remove_images.includes(imageId)) {
        form.remove_images.push(imageId)
    }
    // Remove from existing images array for UI update
    const index = existingImages.value.findIndex(img => img.id === imageId)
    if (index > -1) {
        existingImages.value.splice(index, 1)
    }
}

const setPrimaryImage = async (imageId) => {
    if (!form.id) return

    try {
        await router.post(route('ads.set-primary-image', form.id), {
            image_id: imageId
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Update local state
                existingImages.value = existingImages.value.map(img => ({
                    ...img,
                    is_primary: img.id === imageId
                }))
            }
        })
    } catch (error) {
        console.error('Failed to set primary image:', error)
    }
}

// Form submission
const handleSubmit = () => {
    form.transform(data => ({
        ...data,
        _method: 'PUT'
    })).post(route('ads.update', form.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('user.profile', page.props.auth.user.id))
        }
    })
}

// Delete ad
const confirmDelete = () => {
    showDeleteModal.value = true
}

const deleteAd = () => {
    form.delete(route('ads.destroy', form.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false
            // router.visit(route('ads.index'))
            router.visit(document.referrer)
        }
    })
}
useForceTheme('light');
// Watch for ad changes (useful if ad data is updated from parent)
watch(() => props.ad, (newAd) => {
    if (newAd) {
        form.defaults({
            id: newAd.id,
            category_id: newAd.category_id,
            brand_id: newAd.brand_id,
            ad_title: newAd.ad_title || '',
            description: newAd.description || '',
            price: newAd.price || null,
            location: newAd.location || '',
            city: newAd.city || '',
            seller_name: newAd.seller_name || '',
            seller_phone: newAd.seller_phone || '',
            search_keywords: newAd.search_keywords || [],
            features: newAd.features?.map(f => ({
                feature_id: f.id,
                feature_value_id: f.pivot?.feature_value_id || '',
                custom_value: f.pivot?.custom_value || ''
            })) || []
        })
        form.reset()
        existingImages.value = newAd.images || []
    }
}, { immediate: true })
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}
</style>