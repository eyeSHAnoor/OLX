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

            <div class="border-b border-gray-200 pb-4 px-3">
                <h2 class="text-3xl font-light text-gray-900">{{ editMode ? 'Edit Ad' : 'Ad Details' }}</h2>
                <p class="text-gray-500 mt-1 text-sm">
                    {{ editMode ? 'Update your ad information below' : 'Fill in the information below to create your ad'
                    }}
                </p>
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

        <!-- Form (hidden while submitting) -->
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
                                <SelectItem :value="null">Select Brand</SelectItem>
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
                                <SelectItem :value="null">Select Model</SelectItem>
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

                    <!-- Discount (NEW) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Discount (%)
                            <span class="text-gray-400 text-xs font-normal">(optional)</span>
                        </label>
                        <div class="flex items-start gap-4">
                            <div class="flex-1">
                                <div class="relative">
                                    <input v-model.number="form.discount" type="number" min="0" max="100" step="0.01"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-colors"
                                        placeholder="e.g. 10" />
                                    <span class="absolute right-4 top-2.5 text-gray-500">%</span>
                                </div>
                                <p v-if="form.errors.discount" class="text-red-500 text-xs mt-1.5">
                                    {{ form.errors.discount }}
                                </p>
                            </div>
                            <!-- Discounted Price Preview -->
                            <div v-if="form.price !== null && form.price !== '' && form.discount !== null && form.discount !== ''"
                                class="flex-shrink-0 bg-gray-50 rounded-lg px-4 ">
                                <p class="text-xs text-gray-500">After discount</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ discountedPrice }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specifications (collapsible) -->
            <div v-if="categoryAttributes.length > 0"
                class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <!-- Header – click to toggle -->
                <button type="button" @click="showAttributes = !showAttributes"
                    class="w-full px-6 py-4 bg-gray-50 hover:bg-gray-100 transition-colors flex justify-between items-center">
                    <div class="text-left">
                        <h3 class="font-medium text-gray-900">Product Specifications</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ showAttributes ? 'Hide specifications' : 'Add detailed specs (optional)' }}
                        </p>
                    </div>
                    <!-- Chevron icon that rotates -->
                    <svg :class="showAttributes ? 'rotate-180' : ''"
                        class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Expandable content with smooth animation -->
                <Transition name="specs-slide">
                    <div v-if="showAttributes" class="p-6">
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
                </Transition>
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
                        <!-- Region (appears after city selected) -->
                        <div v-if="form.city">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Region / Area <span class="text-gray-400 text-xs font-normal">(optional)</span>
                            </label>
                            <SearchableSelectInput v-model="form.region" :items="regionOptions" key-by="name"
                                :searchable-fields="['name']" placeholder="Select Region" :disabled="isLoadingRegions">
                                <template #item="{ item }">
                                    <div
                                        class="flex w-3/4 cursor-pointer items-center px-3 py-2 text-left text-sm hover:bg-gray-100">
                                        <span>{{ item.name }}</span>
                                    </div>
                                </template>
                                <template #selected="{ item }">
                                    <span v-if="isLoadingRegions">Loading regions...</span>
                                    <span v-else>{{ item?.name ?? 'All areas' }}</span>
                                </template>
                            </SearchableSelectInput>
                            <p v-if="form.errors.region" class="text-red-500 text-xs mt-1.5">
                                {{ form.errors.region }}
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
                    <p class="text-xs text-gray-500 mt-1">
                        Upload up to 10 images (JPEG, PNG, JPG, GIF) – images are compressed automatically
                    </p>
                </div>

                <div class="p-6">
                    <div class="mb-6">
                        <input type="file" multiple accept="image/*" @change="handleImageUpload" class="hidden"
                            ref="fileInput" />
                        <button type="button" @click="$refs.fileInput.click()" :disabled="isCompressing"
                            class="w-full px-4 py-8 border-2 border-dashed border-gray-300 rounded-lg hover:border-gray-400 transition-colors flex flex-col items-center justify-center"
                            :class="{ 'opacity-50 cursor-not-allowed': isCompressing }">
                            <svg v-if="!isCompressing" class="w-8 h-8 text-gray-400 mb-2" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <svg v-else class="animate-spin h-8 w-8 text-gray-400 mb-2"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>
                            <span class="text-sm text-gray-600">
                                {{ isCompressing ? 'Compressing images...' : 'Click to upload images' }}
                            </span>
                            <span v-if="!isCompressing" class="text-xs text-gray-400 mt-1">or drag and drop</span>
                        </button>
                        <p v-if="compressionError" class="text-red-500 text-xs mt-2">{{ compressionError }}</p>
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
                <button type="submit" :disabled="form.processing || isCompressing"
                    class="px-8 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium">
                    <span v-if="form.processing">{{ editMode ? 'Updating...' : 'Posting...' }}</span>
                    <span v-else>{{ editMode ? 'Update Ad' : 'Post Ad' }}</span>
                </button>
            </div>
        </form>

        <!-- Full‑page loading spinner (shown during submission) -->
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
import imageCompression from 'browser-image-compression'
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

console.log('AdDetailsForm props:', props.adData)

const STORAGE_KEY = 'ad_form_draft'

// Dynamic attributes and models
const categoryAttributes = ref([])
const selectedAttributeValues = ref({})
const brandModels = ref([])
const localSelectedBrand = ref(null)
const localSelectedModel = ref(null)
const isLoadingModels = ref(false)

const regions = ref([])
const isLoadingRegions = ref(false)

// Show/hide specifications panel – initially collapsed
const showAttributes = ref(false)

// Region options formatted for SearchableSelectInput
const regionOptions = computed(() => {
    return regions.value.map(region => ({
        name: region.name,
    }))
})

// Fetch regions when city changes
const fetchRegions = async (cityName) => {
    if (!cityName) {
        regions.value = []
        return
    }

    isLoadingRegions.value = true
    try {
        const response = await axios.get(`/regions/${encodeURIComponent(cityName)}`)
        if (response.data.regions) {
            regions.value = response.data.regions
        } else {
            regions.value = []
        }
    } catch (error) {
        console.error('Failed to fetch regions:', error)
        regions.value = []
    } finally {
        isLoadingRegions.value = false
    }
}

// Image compression state
const isCompressing = ref(false)
const compressionError = ref('')

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

        // Auto-select model if editing
        if (props.editMode && props.adData) {
            const modelId = props.adData.brand_model_id || props.adData.model_id
            if (modelId && brandModels.value.length > 0) {
                const modelExists = brandModels.value.some(model => model.id == modelId)
                if (modelExists) {
                    const model = brandModels.value.find(m => m.id == modelId)
                    localSelectedModel.value = model
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
    form.model_id = localSelectedModel.value?.id || null
}

const availableBrands = computed(() => props.selectedCategory?.brands || [])

// --- NEW: computed discounted price ---
const discountedPrice = computed(() => {
    const price = parseFloat(form.price)
    const discount = parseFloat(form.discount)
    if (isNaN(price) || isNaN(discount) || discount < 0 || discount > 100) {
        return null
    }
    const discounted = price * (1 - discount / 100)
    return discounted.toFixed(2) // formatted as currency string
})

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
        discount: (() => {
            if (props.editMode && props.adData) {
                // If editing and discount exists, calculate percentage from discounted price
                if (props.adData.discount && props.adData.price && props.adData.discount !== props.adData.price) {
                    const originalPrice = parseFloat(props.adData.price);
                    const discountedPrice = parseFloat(props.adData.discount);
                    if (!isNaN(originalPrice) && !isNaN(discountedPrice) && originalPrice > 0) {
                        const percentage = ((originalPrice - discountedPrice) / originalPrice) * 100;
                        return percentage.toFixed(2);
                    }
                }
                return null;
            }
            return formData.discount || null;
        })(),
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

// Watch for city changes
watch(() => form.city, (newCity, oldCity) => {
    if (newCity !== oldCity) {
        // Clear previously selected region when city changes
        form.region = null
        if (newCity) {
            fetchRegions(newCity)
        } else {
            regions.value = []
        }
    }
})

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
        const brand = props.selectedCategory.brands.find(b => b.id === props.adData.brand_id)
        if (brand) {
            localSelectedBrand.value = brand
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

// Image upload with compression
const handleImageUpload = async (event) => {
    const files = Array.from(event.target.files)
    const totalAfterUpload = totalImages.value + files.length

    if (totalAfterUpload > 10) {
        alert('Maximum 10 images allowed')
        event.target.value = ''
        return
    }

    isCompressing.value = true
    compressionError.value = ''

    const compressionOptions = {
        maxSizeMB: 1,
        maxWidthOrHeight: 1920,
        useWebWorker: true
    }

    for (const file of files) {
        if (!file.type.startsWith('image/')) continue

        try {
            const compressedFile = await imageCompression(file, compressionOptions)
            form.images.push(compressedFile)

            // Generate preview from original file (or compressed, but original is fine for preview)
            const reader = new FileReader()
            reader.onload = e => imagePreviews.value.push(e.target.result)
            reader.readAsDataURL(file)
        } catch (error) {
            console.error('Compression failed for', file.name, error)
            compressionError.value = `Failed to compress ${file.name}. It will be uploaded as-is.`
            // Fallback: add original file
            form.images.push(file)
            const reader = new FileReader()
            reader.onload = e => imagePreviews.value.push(e.target.result)
            reader.readAsDataURL(file)
        }
    }

    isCompressing.value = false
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
    form.discount = null // NEW: reset discount
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
    showAttributes.value = false
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
        discount: discountedPrice.value || form.price, // Send discounted price instead of percentage
        location: form.location,
        city: form.city,
        region: form.region,
        seller_name: form.seller_name,
        seller_phone: form.seller_phone,
        search_keywords: form.search_keywords,
        features: form.features.filter(f => f.feature_id),
        attributes: selectedAttributeValues.value,
        remove_images: form.remove_images
    }

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
            headers: { 'Content-Type': 'multipart/form-data' },
            onSuccess: () => {
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
            headers: { 'Content-Type': 'multipart/form-data' },
            onSuccess: () => {
                clearDraftData()
                resetFormState()
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
    if (props.editMode && props.adData?.city) {
        fetchRegions(props.adData.city)
        // Optionally set the region if it exists
        if (props.adData.region) {
            form.region = props.adData.region
        }
    }
    setInitialBrandAndModel()
})

onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload)
})

const emit = defineEmits(['back'])
</script>

<style scoped>
input[type='number']::-webkit-inner-spin-button,
input[type='number']::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type='number'] {
    -moz-appearance: textfield;
}

/* Smooth slide-open for specifications */
.specs-slide-enter-active,
.specs-slide-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}

.specs-slide-enter-from,
.specs-slide-leave-to {
    max-height: 0;
    opacity: 0;
    padding-top: 0;
    padding-bottom: 0;
}

.specs-slide-enter-to,
.specs-slide-leave-from {
    max-height: 2000px;
    opacity: 1;
}
</style>