<template>
    <div class="max-w-full mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <button @click="handleBack"
                class="flex items-center text-gray-600 hover:text-gray-900 mb-4 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Categories
            </button>

            <div class="border-b border-gray-200 pb-4">
                <h2 class="text-3xl font-light text-gray-900">{{ editMode ? 'Edit Ad' : 'Ad Details' }}</h2>
                <p class="text-gray-500 mt-1 text-sm">{{ editMode ?
                    'Update your ad information below' : 'Fill in the information below to create your ad' }}</p>
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

                <div v-if="localSelectedModel" class="flex items-center space-x-2">
                    <span class="text-gray-500">Model:</span>
                    <span class="font-medium text-gray-900">{{ localSelectedModel.name }}</span>
                </div>
            </div>
        </div>

        <form v-if="!form.processing" @submit.prevent="handleSubmit" class="space-y-8">

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
                        <SelectInput v-model="localSelectedBrand" @update:modelValue="handleBrandChange"
                            placeholder="Select Brand" class="w-full">
                            <SelectContent>

                                <SelectItem :value="null">
                                    Select Brand
                                </SelectItem>

                                <SelectItem v-for="brand in availableBrands" :key="brand.id" :value="brand">
                                    {{ brand.name }}
                                </SelectItem>

                            </SelectContent>
                        </SelectInput>
                        <p v-if="form.errors.brand_id" class="text-red-500 text-xs mt-1.5">
                            {{ form.errors.brand_id }}
                        </p>
                    </div>

                    <!-- Model -->
                    <div v-if="brandModels.length > 0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                        <SelectInput v-model="localSelectedModel" @update:modelValue="handleModelChange"
                            placeholder="Select Model" :disabled="isLoadingModels"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white">
                            <SelectContent>

                                <SelectItem :value="null">
                                    Select Model
                                </SelectItem>

                                <SelectItem v-for="model in brandModels" :key="model.id" :value="model">
                                    {{ model.name }}
                                </SelectItem>

                            </SelectContent>
                        </SelectInput>
                        <p v-if="form.errors.model_id" class="text-red-500 text-xs mt-1.5">
                            {{ form.errors.model_id }}
                        </p>
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price <span
                                class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-2.5 text-gray-500"></span>
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

            <!-- Category Attributes Section -->
            <div v-if="categoryAttributes.length > 0"
                class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900">Product Specifications</h3>
                    <p class="text-xs text-gray-500 mt-1">Fill in the specifications for your product</p>
                </div>

                <div class="p-6">
                    <div class="space-y-4">
                        <div v-for="attr in categoryAttributes" :key="attr.id"
                            class="grid grid-cols-3 gap-4 items-start">
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ attr.name }}
                                    <span v-if="attr.is_required" class="text-red-500">*</span>
                                </label>
                                <div v-if="attr.group?.name" class="text-xs text-gray-400 mt-0.5">
                                    {{ attr.group.name }}
                                </div>
                            </div>
                            <div class="col-span-2">
                                <!-- Select type -->
                                <select v-if="attr.type === 'select'" v-model="selectedAttributeValues[attr.id]"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors bg-white">
                                    <option value="">Select {{ attr.name }}</option>
                                    <option v-for="option in attr.options" :key="option.id" :value="option.id">
                                        {{ option.value }}
                                    </option>
                                </select>

                                <!-- Text type -->
                                <input v-else-if="attr.type === 'text'" v-model="selectedAttributeValues[attr.id]"
                                    type="text" :placeholder="`Enter ${attr.name.toLowerCase()}`"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors" />

                                <!-- Number type -->
                                <input v-else-if="attr.type === 'number'" v-model="selectedAttributeValues[attr.id]"
                                    type="number" step="any" :placeholder="`Enter ${attr.name.toLowerCase()}`"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors" />

                                <!-- Boolean type -->
                                <div v-else-if="attr.type === 'boolean'" class="flex items-center">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" v-model="selectedAttributeValues[attr.id]"
                                            :true-value="1" :false-value="0"
                                            class="w-4 h-4 text-gray-900 border-gray-300 rounded focus:ring-gray-400" />
                                        <span class="ml-2 text-sm text-gray-700">Yes</span>
                                    </label>
                                </div>

                                <!-- Textarea type -->
                                <textarea v-else-if="attr.type === 'textarea'"
                                    v-model="selectedAttributeValues[attr.id]" rows="3"
                                    :placeholder="`Enter ${attr.name.toLowerCase()}`"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors resize-none"></textarea>

                                <!-- Default input -->
                                <input v-else v-model="selectedAttributeValues[attr.id]" type="text"
                                    :placeholder="`Enter ${attr.name.toLowerCase()}`"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors" />
                            </div>
                        </div>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                City <span class="text-red-500">*</span>
                            </label>
                            <SearchableSelectInput v-model="form.city" :items="cityOptions" key-by="id"
                                :searchable-fields="['name']" placeholder="Select City">
                                <template #item="{ item }">
                                    <div
                                        class="flex w-3/4 cursor-pointer items-center px-3 py-2 text-left text-sm hover:bg-gray-100">
                                        <span>{{ item.name }}</span>
                                    </div>
                                </template>
                                <template #selected="{ item }">
                                    {{ item?.name ?? 'Select City' }}
                                </template>
                            </SearchableSelectInput>
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

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
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
            <!-- <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
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
                                    <template v-if="hasFeatureValues(feature.feature_id)">
                                        <select v-model="feature.feature_value_id"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors bg-white text-sm">
                                            <option value="">Select Value</option>
                                            <option v-for="v in getFeatureValues(feature.feature_id)" :key="v.id"
                                                :value="v.id">
                                                {{ v.value }}
                                            </option>
                                        </select>
                                        <input v-if="feature.feature_value_id === ''" v-model="feature.custom_value"
                                            placeholder="Enter custom value"
                                            class="mt-2 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors text-sm" />
                                    </template>
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
            </div> -->

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
                    <span v-if="form.processing">{{ editMode ? 'Updating...' : 'Posting...' }}</span>
                    <span v-else>{{ editMode ? 'Update Ad' : 'Post Ad' }}</span>
                </button>
            </div>
        </form>

        <div v-else class="min-h-[calc(100vh-4rem)] flex items-center justify-center">
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 text-brand-blue mx-auto" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <p class="mt-4 text-sm text-gray-600">Saving your ad, please wait...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import cities from '@/data/cities.json'

const props = defineProps({
    selectedCategory: Object,
    selectedBrand: Object,
    user: Object,
    features: {
        type: Array,
        default: () => []
    },
    editMode: { type: Boolean, default: false },
    adData: { type: Object, default: null }
})

const STORAGE_KEY = 'ad_form_draft'
// isSubmitting is handled by Inertia useForm processing state (form.processing)

// Dynamic attributes and models
const categoryAttributes = ref([])
const selectedAttributeValues = ref({})
const brandModels = ref([])
const localSelectedBrand = ref(null)
const localSelectedModel = ref(null)
const isLoadingModels = ref(false)

const cityOptions = computed(() => {
    const citiesList = cities
        .filter(city => city.country === 'PK')
        .map(city => ({
            id: city.name,
            name: city.name
        }))
        .sort((a, b) => a.name.localeCompare(b.name))
    return Object.freeze(citiesList)
})

// Fetch attributes for category
const fetchAttributes = async (categoryId) => {
    try {
        const response = await axios.get(`/categories/${categoryId}/attributes`)
        if (response.data.success) {
            categoryAttributes.value = response.data.attributes
        } else {
            categoryAttributes.value = response.data.attributes || []
        }

        // Populate existing attribute values if editing
        if (props.editMode && props.adData?.attributes && props.adData.attributes.length > 0) {
            props.adData.attributes.forEach((attr) => {
                const attributeId = attr.category_attribute_id || attr.attribute?.id
                if (attributeId && attr.value) {
                    selectedAttributeValues.value[attributeId] = attr.value
                }
            })
        }
    } catch (error) {
        console.error('Failed to load attributes:', error)
        categoryAttributes.value = []
    }
}

// Fetch models for brand
const fetchModels = async (brandId) => {
    isLoadingModels.value = true
    try {
        const response = await axios.get(`/brands/${brandId}/models`)
        if (response.data.success) {
            brandModels.value = response.data.models
        } else {
            brandModels.value = response.data.models || []
        }

        // Auto-select model if editing - use brand_model_id or model_id
        if (props.editMode && props.adData) {
            const modelId = props.adData.brand_model_id || props.adData.model_id
            if (modelId && brandModels.value.length > 0) {
                const modelExists = brandModels.value.some(model => model.id == modelId)
                if (modelExists) {
                    const model = brandModels.value.find(m => m.id == modelId)
                    localSelectedModel.value = model
                    //console.log('Auto-selected model:', model)
                }
            }
        }
    } catch (error) {
        console.error('Failed to load models:', error)
        brandModels.value = []
    } finally {
        isLoadingModels.value = false
    }
}

const handleBrandChange = () => {
    if (localSelectedBrand.value) {
        fetchModels(localSelectedBrand.value.id)
        localSelectedModel.value = null
    } else {
        brandModels.value = []
        localSelectedModel.value = null
    }
}

const handleModelChange = () => {
    // Update form model_id
    form.model_id = localSelectedModel.value?.id || null
}

const availableBrands = computed(() => props.selectedCategory?.brands || [])

// Initialize form
const initializeForm = () => {
    const savedDraft = localStorage.getItem(STORAGE_KEY)
    let formData = {}

    if (savedDraft && !props.editMode) {
        try {
            formData = JSON.parse(savedDraft)
        } catch (e) {
            console.error('Failed to parse saved draft', e)
        }
    }

    // Get model ID from either brand_model_id or model_id
    const modelId = props.editMode && props.adData
        ? (props.adData.brand_model_id || props.adData.model_id || null)
        : (formData.model_id || null)

    return {
        category_id: props.editMode && props.adData ? props.adData.category_id : (formData.category_id || props.selectedCategory?.id || null),
        brand_id: props.editMode && props.adData ? props.adData.brand_id : (formData.brand_id || null),
        model_id: modelId,
        ad_title: props.editMode && props.adData ? props.adData.ad_title : (formData.ad_title || ''),
        description: props.editMode && props.adData ? props.adData.description : (formData.description || ''),
        price: props.editMode && props.adData ? props.adData.price : (formData.price || null),
        location: props.editMode && props.adData ? props.adData.location : (formData.location || ''),
        city: props.editMode && props.adData ? props.adData.city : (formData.city || ''),
        seller_name: props.editMode && props.adData ? props.adData.seller_name : (formData.seller_name || ''),
        seller_phone: props.editMode && props.adData ? props.adData.seller_phone : (formData.seller_phone || ''),
        search_keywords: props.editMode && props.adData ? [...props.adData.search_keywords] : (formData.search_keywords || []),
        images: [],
        features: [],
        remove_images: [],
        attributes: {}
    }
}

const form = useForm(initializeForm())

// Initialize features
if (props.editMode && props.adData?.features) {
    form.features = props.adData.features.map(f => ({
        feature_id: f.id,
        feature_value_id: f.pivot?.feature_value_id || null,
        custom_value: f.pivot?.custom_value || null
    }))
}

// Set initial brand from adData if editing
const setInitialBrandAndModel = () => {
    if (props.editMode && props.adData && props.selectedCategory?.brands) {
        // Find and set the brand
        const brand = props.selectedCategory.brands.find(b => b.id === props.adData.brand_id)
        if (brand) {
            localSelectedBrand.value = brand
            //console.log('Auto-selected brand:', brand)

            // After brand is set, fetch models and then set model
            setTimeout(() => {
                if (localSelectedBrand.value) {
                    fetchModels(localSelectedBrand.value.id)
                }
            }, 100)
        }
    }
}

// Watch for category changes to load attributes
watch(() => props.selectedCategory, async (newCategory) => {
    if (newCategory && newCategory.id) {
        await fetchAttributes(newCategory.id)
    }
}, { immediate: true })

// Update form model_id when localSelectedModel changes
watch(localSelectedModel, (newModel) => {
    form.model_id = newModel?.id || null
})

const newKeyword = ref('')
const imagePreviews = ref([])
const existingImages = ref(props.editMode && props.adData?.images ? [...props.adData.images] : [])
const fileInput = ref(null)

const totalImages = computed(() => {
    return (existingImages.value?.length || 0) + form.images.length
})

const getFeatureValues = (featureId) => {
    if (!featureId || !props.features || !props.features.length) return []
    const id = Number(featureId)
    const feature = props.features.find(f => Number(f.id) === id)
    return feature?.values || []
}

const hasFeatureValues = (featureId) => {
    if (!featureId || !props.features || !props.features.length) return false
    const id = Number(featureId)
    const feature = props.features.find(f => Number(f.id) === id)
    return feature?.values && feature.values.length > 0
}

const getFeatureName = (featureId) => {
    if (!featureId || !props.features || !props.features.length) return 'value'
    const id = Number(featureId)
    const feature = props.features.find(f => Number(f.id) === id)
    return feature?.name?.toLowerCase() || 'value'
}

const addFeatureRow = () => {
    form.features.push({
        feature_id: '',
        feature_value_id: null,
        custom_value: null
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
    const index = existingImages.value.findIndex(img => img.id === imageId)
    if (index !== -1) {
        existingImages.value.splice(index, 1)
    }
}

const clearDraftData = () => {
    localStorage.removeItem(STORAGE_KEY)
}

const resetFormState = () => {
    form.ad_title = ''
    form.description = ''
    form.price = null
    form.location = ''
    form.city = ''
    form.seller_name = ''
    form.seller_phone = ''
    form.search_keywords = []
    form.features = []
    form.images = []
    form.remove_images = []
    imagePreviews.value = []
    localSelectedBrand.value = null
    localSelectedModel.value = null
    selectedAttributeValues.value = {}
}

const handleBack = () => {
    if (!props.editMode) {
        const hasData = localStorage.getItem(STORAGE_KEY)
        if (hasData) {
            const confirmClear = confirm('You have unsaved changes. Do you want to discard them?')
            if (confirmClear) {
                clearDraftData()
            } else {
                emit('back')
                return
            }
        }
    }
    emit('back')
}

const handleSubmit = () => {
    if (!props.selectedCategory) {
        alert('No category selected!')
        return
    }

    const submitData = {
        category_id: props.selectedCategory.id,
        brand_id: localSelectedBrand.value?.id ?? null,
        model_id: localSelectedModel.value?.id ?? null,
        ad_title: form.ad_title,
        description: form.description,
        price: form.price,
        location: form.location,
        city: form.city,
        seller_name: form.seller_name,
        seller_phone: form.seller_phone,
        search_keywords: form.search_keywords,
        features: form.features.filter(f => f.feature_id),
        attributes: selectedAttributeValues.value,
        remove_images: form.remove_images
    }

    //console.log('Submitting:', submitData)

    if (props.editMode) {
        const formData = new FormData()

        Object.keys(submitData).forEach(key => {
            if (key === 'features') {
                if (submitData[key] && submitData[key].length > 0) {
                    submitData[key].forEach((feature, index) => {
                        formData.append(`features[${index}][feature_id]`, feature.feature_id)
                        formData.append(`features[${index}][feature_value_id]`, feature.feature_value_id || '')
                        formData.append(`features[${index}][custom_value]`, feature.custom_value || '')
                    })
                }
            } else if (key === 'attributes') {
                if (submitData[key] && Object.keys(submitData[key]).length > 0) {
                    Object.entries(submitData[key]).forEach(([attrId, value]) => {
                        if (value !== null && value !== '' && value !== false) {
                            formData.append(`attributes[${attrId}]`, value)
                        }
                    })
                }
            } else if (key === 'search_keywords') {
                if (submitData[key] && submitData[key].length > 0) {
                    submitData[key].forEach((keyword, index) => {
                        formData.append(`search_keywords[${index}]`, keyword)
                    })
                }
            } else if (key === 'remove_images') {
                if (submitData[key] && submitData[key].length > 0) {
                    submitData[key].forEach((imageId, index) => {
                        formData.append(`remove_images[${index}]`, imageId)
                    })
                }
            } else if (submitData[key] !== null && submitData[key] !== undefined) {
                formData.append(key, submitData[key])
            }
        })

        form.images.forEach((image, index) => {
            formData.append(`images[${index}]`, image)
        })

        formData.append('_method', 'PUT')

        router.post(route('ads.update', props.adData.id), formData, {
            forceFormData: true,
            preserveScroll: true,
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            onSuccess: () => {
                router.visit(route('user.profile', props.user?.id))
                clearDraftData()
            },
            onError: (errors) => {
                console.error('Update errors:', errors)
                if (errors && typeof errors === 'object') {
                    Object.assign(form.errors, errors)
                }
                alert('Error updating ad. Please check the form for errors.')
            }
        })
    } else {
        const formData = new FormData()

        Object.keys(submitData).forEach(key => {
            if (key === 'features') {
                if (submitData[key] && submitData[key].length > 0) {
                    submitData[key].forEach((feature, index) => {
                        formData.append(`features[${index}][feature_id]`, feature.feature_id)
                        formData.append(`features[${index}][feature_value_id]`, feature.feature_value_id || '')
                        formData.append(`features[${index}][custom_value]`, feature.custom_value || '')
                    })
                }
            } else if (key === 'attributes') {
                if (submitData[key] && Object.keys(submitData[key]).length > 0) {
                    Object.entries(submitData[key]).forEach(([attrId, value]) => {
                        if (value !== null && value !== '' && value !== false) {
                            formData.append(`attributes[${attrId}]`, value)
                        }
                    })
                }
            } else if (key === 'search_keywords') {
                if (submitData[key] && submitData[key].length > 0) {
                    submitData[key].forEach((keyword, index) => {
                        formData.append(`search_keywords[${index}]`, keyword)
                    })
                }
            } else if (submitData[key] !== null && submitData[key] !== undefined) {
                formData.append(key, submitData[key])
            }
        })

        form.images.forEach((image, index) => {
            formData.append(`images[${index}]`, image)
        })

        router.post(route('ads.store'), formData, {
            forceFormData: true,
            preserveScroll: true,
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            onSuccess: () => {
                clearDraftData()
                resetFormState()
                router.visit(route('user.profile', props.user?.id))
            },
            onError: (errors) => {
                console.error('Create errors:', errors)
                if (errors && typeof errors === 'object') {
                    Object.assign(form.errors, errors)
                }
                alert('Error creating ad. Please check the form for errors.')
            }
        })
    }
}

const handleBeforeUnload = (e) => {
    if (!props.editMode && !form.processing && localStorage.getItem(STORAGE_KEY)) {
        e.preventDefault()
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?'
        return e.returnValue
    }
}

onMounted(() => {
    window.addEventListener('beforeunload', handleBeforeUnload)
    if (props.selectedCategory?.id) {
        fetchAttributes(props.selectedCategory.id)
    }
    // Set initial brand and model after component is mounted
    setInitialBrandAndModel()
})

onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload)
})

const emit = defineEmits(['back'])
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