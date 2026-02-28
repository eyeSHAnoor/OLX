<template>
    <div class="max-w-full mx-auto">
        <!-- Remove the {{ features }} debug line -->

        <!-- Header -->
        <div class="mb-8">
            <button @click="$emit('back')"
                class="flex items-center text-gray-600 hover:text-gray-900 mb-4 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Categories
            </button>

            <div class="border-b border-gray-200 pb-4">
                <h2 class="text-3xl font-light text-gray-900">Ad Details</h2>
                <p class="text-gray-500 mt-1 text-sm">Fill in the information below to create your ad</p>
            </div>
        </div>

        <!-- Category + Brand Summary -->
        <div v-if="selectedCategory" class="bg-gray-50 rounded-lg p-4 mb-8 flex items-center justify-between text-sm">
            <div class="flex items-center space-x-6">
                <div class="flex items-center space-x-2">
                    <span class="text-gray-500">Category:</span>
                    <span class="font-medium text-gray-900">{{ selectedCategory.name }}</span>
                </div>

                <div v-if="localSelectedBrand" class="flex items-center space-x-2">
                    <span class="text-gray-500">Brand:</span>
                    <span class="font-medium text-gray-900">{{ localSelectedBrand.name }}</span>
                </div>
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

                    <!-- Brand -->
                    <div v-if="availableBrands?.length">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Brand <span
                                class="text-red-500">*</span></label>
                        <select v-model="localSelectedBrand"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors bg-white">
                            <option :value="null">Select Brand</option>
                            <option v-for="brand in availableBrands" :key="brand.id" :value="brand">
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
                    <h3 class="font-medium text-gray-900">Images <span class="text-red-500">*</span></h3>
                    <p class="text-xs text-gray-500 mt-1">Upload up to 10 images (JPEG, PNG, JPG, GIF)</p>
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
                            <span class="text-sm text-gray-600">Click to upload images</span>
                            <span class="text-xs text-gray-400 mt-1">or drag and drop</span>
                        </button>
                    </div>

                    <!-- Image Preview Grid - Now starts from beginning -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <!-- Existing Images (show first) -->
                        <div v-for="image in existingImages" :key="`existing-${image.id}`" class="relative group">
                            <img :src="`/storage/${image.path}`"
                                class="w-full aspect-square object-cover rounded-lg border border-gray-200" />
                            <button type="button" @click="removeExistingImage(image.id)"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div v-if="image.is_primary"
                                class="absolute bottom-2 left-2 bg-gray-900 text-white text-xs px-2 py-1 rounded">
                                Primary
                            </div>
                        </div>

                        <!-- New Images (show after existing) -->
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
                        </div>

                        <!-- Empty Placeholders (show last) -->
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
                        <p class="text-xs text-gray-500 mt-1">Add specific features for your ad</p>
                    </div>
                    <button type="button" @click="addFeatureRow"
                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
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

            <!-- Submit Button -->
            <div class="flex justify-end pt-4">
                <button type="submit" :disabled="form.processing"
                    class="px-8 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium">
                    <span v-if="form.processing">Posting...</span>
                    <span v-else>Post Ad</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    selectedCategory: Object,
    selectedBrand: Object,
    user: Object,
    features: {
        type: Array,
        default: () => []
    },
    existingImages: {
        type: Array,
        default: () => []
    }
})

const form = useForm({
    category_id: null,
    brand_id: null,
    ad_title: '',
    description: '',
    price: null,
    location: '',
    city: '',
    seller_name: '',
    seller_phone: '',
    search_keywords: [],
    images: [],
    features: [],
    remove_images: []
})

const newKeyword = ref('')
const imagePreviews = ref([])
const localSelectedBrand = ref(props.selectedBrand || null)
const fileInput = ref(null)

// Calculate total images for placeholder count
const totalImages = computed(() => {
    return (props.existingImages?.length || 0) + form.images.length
})

watch(() => props.selectedBrand, (val) => {
    localSelectedBrand.value = val
}, { immediate: true })

const availableBrands = computed(() => props.selectedCategory?.brands || [])

// Improved function to get feature values
const getFeatureValues = (featureId) => {
    if (!featureId || !props.features || !props.features.length) return []

    // Convert featureId to number for comparison
    const id = Number(featureId)
    const feature = props.features.find(f => Number(f.id) === id)

    // Return values array or empty array
    return feature?.values || []
}

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
    // Emit event to parent to update UI
    emit('remove-existing-image', imageId)
}

const handleSubmit = () => {
    if (!props.selectedCategory) {
        alert('No category selected!')
        return
    }

    form.transform(data => ({
        ...data,
        category_id: props.selectedCategory.id,
        brand_id: localSelectedBrand.value?.id ?? null
    })).post(route('ads.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            imagePreviews.value = []
            router.visit(route('user.profile', props.user.id))
        }
    })
}

// Emit for removing existing images
const emit = defineEmits(['back', 'remove-existing-image'])
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