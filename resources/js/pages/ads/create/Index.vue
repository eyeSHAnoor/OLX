<template>
    <OLXLayout>
        <div class="max-w-8/10 mx-auto space-y-12 py-10">
            <!-- Subscription Check -->
            <div v-if="!canCreateAd" class="bg-white rounded-lg shadow-sm p-8 text-center">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <h2 class="text-2xl font-semibold text-gray-900 mb-3">Subscription Required</h2>

                    <p class="text-gray-600 mb-6">
                        You need an active subscription to post ads.
                        Please purchase a subscription plan to start posting your ads.
                    </p>

                    <div class="space-y-3">
                        <Link :href="route('subscriptions.index')"
                            class="inline-block w-full px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-medium">
                            View Subscription Plans
                        </Link>

                        <p class="text-sm text-gray-500">
                            Your current status:
                            <span class="font-medium capitalize">{{ user?.subscription_status }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ad Creation Flow (only shown if user can create ads) -->
            <template v-else>
                <!-- Progress Steps -->
                <div class="mb-8">
                    <div class="flex items-center justify-center space-x-4">
                        <div v-for="(step, index) in steps" :key="index" class="flex items-center">
                            <div class="flex items-center">
                                <div :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium',
                                    currentStep > index + 1 ? 'bg-green-500 text-white' :
                                        currentStep === index + 1 ? 'bg-primary text-white' :
                                            'bg-gray-200 text-gray-600'
                                ]">
                                    <span v-if="currentStep > index + 1">✓</span>
                                    <span v-else>{{ index + 1 }}</span>
                                </div>
                                <span :class="[
                                    'ml-2 text-sm font-medium',
                                    currentStep === index + 1 ? 'text-gray-900' : 'text-gray-500'
                                ]">{{ step }}</span>
                            </div>
                            <div v-if="index < steps.length - 1" class="w-16 h-0.5 mx-4 bg-gray-200"></div>
                        </div>
                    </div>
                </div>

                <!-- Category Navigation -->
                <CategoryNavigation v-if="currentStep === 1" :categories="categories"
                    :selected-category="selectedCategory" :category-path="categoryPath" @select="handleCategorySelect"
                    @next="goToNextStep" />

                <!-- Step 2: Ad Details Form -->
                <AdDetailsForm v-else-if="currentStep === 2" :selected-category="selectedCategory"
                    :selected-brand="selectedBrand" @back="goToPreviousStep" @submit="handleSubmit" :user="user"
                    :features="features" />
            </template>
        </div>
    </OLXLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import OLXLayout from '@/Layouts/OLXLayout.vue'
import CategoryNavigation from './CategoryNavigation.vue'
import AdDetailsForm from './AdDetailsForm.vue'

const props = defineProps({
    categories: {
        type: Array,
        required: true
    }
})

const page = usePage()
console.log(page.props);

const steps = ['Category', 'Details']
const currentStep = ref(1)
const selectedCategory = ref(null)
const selectedBrand = ref(null)
const categoryPath = ref([])

// Get user from page props
const user = computed(() => page.props.auth?.user)
const features = computed(() => page.props.features)

// Check if user can create ads based on subscription status
const canCreateAd = computed(() => {
    if (!user.value) return false

    // Allow if subscription status is 'active' or 'completed' (adjust based on your status values)
    const allowedStatuses = ['active', 'completed', 'approved', 'subscribed']
    return allowedStatuses.includes(user.value.subscription_status)
})

// Check if category is leaf (no children)
const isLeafCategory = (category) => {
    return !category.children_recursive || category.children_recursive.length === 0
}

// Get available brands based on selected category
const availableBrands = computed(() => {
    if (!selectedCategory.value) return []
    return selectedCategory.value.brands || []
})

// Update category path when selected category changes
watch(selectedCategory, (newCategory) => {
    if (newCategory) {
        updateCategoryPath(newCategory)
    }
})

const updateCategoryPath = (category) => {
    // This would need the full category tree to build the path
    // For now, we'll just show the current category
    categoryPath.value = [category]
}

const handleCategorySelect = (category) => {
    selectedCategory.value = category
    selectedBrand.value = null // Reset brand when category changes

    if (isLeafCategory(category)) {
        currentStep.value = 2
    }
}

const handleBrandSelect = (brand) => {
    selectedBrand.value = brand
}

const goToNextStep = () => {
    if (currentStep.value === 1) {
        // Validate that we have a leaf category selected
        if (!selectedCategory.value || !isLeafCategory(selectedCategory.value)) {
            alert('Please select a final subcategory (leaf category)')
            return
        }
    }

    if (currentStep.value < steps.length) {
        currentStep.value++
    }
}

const goToPreviousStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--
    }
}

const handleSubmit = (adData) => {
    // Handle ad submission
    console.log('Ad submitted:', adData)
}
</script>