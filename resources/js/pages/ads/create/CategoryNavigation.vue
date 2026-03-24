<template>
    <div class="space-y-8">

        <!-- Breadcrumb -->
        <div class="flex items-center flex-wrap gap-2 text-sm">
            <button @click="resetToRoot"
                class="text-brand-teal hover:text-brand-blue font-medium flex items-center gap-1">
                <Icon icon="lucide:home" class="size-4" />
                All Categories
            </button>

            <template v-for="(crumb, index) in breadcrumbs" :key="crumb.id">
                <Icon icon="lucide:chevron-right" class="size-4 text-gray-400" />

                <button @click="navigateTo(index)" :class="[
                    'transition-colors',
                    index === breadcrumbs.length - 1
                        ? 'text-brand-blue font-semibold'
                        : 'text-gray-600 hover:text-brand-teal'
                ]">
                    {{ crumb.name }}
                </button>
            </template>
        </div>

        <!-- Title -->
        <div>
            <h2 class="text-2xl font-semibold text-brand-blue">
                {{ currentTitle }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Choose the most relevant category for your listing
            </p>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="category in currentCategories" :key="category.id" @click="handleClick(category)"
                class="group bg-white border rounded-xl p-5 cursor-pointer transition-all duration-200 hover:shadow-lg hover:border-brand-teal"
                :class="{ 'border-brand-teal bg-brand-teal/5': isSelectedCategory(category) }">
                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-4">

                        <!-- Category Icon -->
                        <div
                            class="w-12 h-12 rounded-lg bg-brand-teal/10 flex items-center justify-center overflow-hidden">
                            <template v-if="category.files && category.files.length > 0">
                                <img :src="category.files[0].file_url" :alt="category.name"
                                    class="w-full h-full object-cover" />
                            </template>
                            <template v-else>
                                <Icon icon="lucide:folder" class="size-6 text-brand-teal" />
                            </template>
                        </div>

                        <!-- Name -->
                        <div>
                            <h3 class="font-semibold text-gray-800">
                                {{ category.name }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ category.children_recursive?.length || 0 }} subcategories
                            </p>
                        </div>
                    </div>

                    <!-- Arrow if not leaf -->
                    <Icon v-if="!isLeaf(category)" icon="lucide:chevron-right"
                        class="size-5 text-gray-400 group-hover:text-brand-teal transition-colors" />
                    <Icon v-else-if="isSelectedCategory(category)" icon="lucide:check-circle"
                        class="size-5 text-brand-teal" />
                </div>
            </div>
        </div>

        <!-- Selected Leaf Category -->
        <div v-if="selectedLeaf" class="mt-8 p-6 border rounded-xl bg-brand-teal/5 border-brand-teal/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Selected Category</p>
                    <p class="text-lg font-semibold text-brand-blue">
                        {{ selectedLeaf.name }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{breadcrumbs.map(c => c.name).join(' / ')}}
                    </p>
                </div>

                <Icon icon="lucide:check-circle" class="size-7 text-brand-teal" />
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
    categories: {
        type: Array,
        required: true
    },
    selectedCategory: {
        type: Object,
        default: null
    },
    categoryPath: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['select', 'next'])

const STORAGE_KEY = 'ad_form_draft'
const currentParentId = ref(null)
const breadcrumbs = ref([])
const selectedLeaf = ref(null)

const rootCategories = computed(() =>
    props.categories.filter(c => !c.parent_id)
)

const currentCategories = computed(() => {
    if (!currentParentId.value) return rootCategories.value
    const parent = findById(currentParentId.value)
    return parent?.children_recursive || []
})

const currentTitle = computed(() => {
    if (!breadcrumbs.value.length) return 'Select Category'
    return `Select Subcategory of ${breadcrumbs.value.at(-1).name}`
})

const isLeaf = (category) =>
    !category.children_recursive || category.children_recursive.length === 0

const isSelectedCategory = (category) => {
    return selectedLeaf.value?.id === category.id
}

const findById = (id, list = props.categories) => {
    for (const item of list) {
        if (item.id === id) return item
        if (item.children_recursive) {
            const found = findById(id, item.children_recursive)
            if (found) return found
        }
    }
    return null
}

const findPathToCategory = (categoryId, currentPath = [], list = props.categories) => {
    for (const item of list) {
        if (item.id === categoryId) {
            return [...currentPath, item]
        }
        if (item.children_recursive) {
            const found = findPathToCategory(categoryId, [...currentPath, item], item.children_recursive)
            if (found) return found
        }
    }
    return null
}

const autoSelectCategory = () => {
    if (props.selectedCategory) {
        const path = findPathToCategory(props.selectedCategory.id)

        if (path && path.length > 0) {
            breadcrumbs.value = path
            const leafCategory = path[path.length - 1]
            selectedLeaf.value = leafCategory

            if (isLeaf(leafCategory)) {
                currentParentId.value = leafCategory.id
                emit('select', leafCategory)
            } else {
                currentParentId.value = leafCategory.id
            }
        }
    }
}

const restoreFromSavedData = () => {
    const savedDraft = localStorage.getItem(STORAGE_KEY)
    if (savedDraft && !props.selectedCategory) {
        try {
            const formData = JSON.parse(savedDraft)
            if (formData.category_id) {
                const category = findById(formData.category_id)
                if (category) {
                    // Restore the category UI
                    const path = findPathToCategory(category.id)
                    if (path && path.length > 0) {
                        breadcrumbs.value = path
                        const leafCategory = path[path.length - 1]
                        selectedLeaf.value = leafCategory
                        currentParentId.value = leafCategory.id

                        // Emit to parent to update form
                        setTimeout(() => {
                            emit('select', leafCategory)
                        }, 0)
                    }
                }
            }
        } catch (e) {
            console.error('Failed to restore category from draft', e)
        }
    }
}

const handleClick = (category) => {
    if (isLeaf(category)) {
        selectedLeaf.value = category
        breadcrumbs.value.push(category)

        // Save category to draft immediately
        saveCategoryToDraft(category.id)

        emit('select', category)
        return
    }

    currentParentId.value = category.id
    breadcrumbs.value.push(category)
    selectedLeaf.value = null
}

// Save only the category ID to draft (not the whole form)
const saveCategoryToDraft = (categoryId) => {
    const savedDraft = localStorage.getItem(STORAGE_KEY)
    let draftData = {}

    if (savedDraft) {
        try {
            draftData = JSON.parse(savedDraft)
        } catch (e) {
            console.error('Failed to parse saved draft', e)
        }
    }

    draftData.category_id = categoryId
    localStorage.setItem(STORAGE_KEY, JSON.stringify(draftData))
}

const navigateTo = (index) => {
    const crumb = breadcrumbs.value[index]
    breadcrumbs.value = breadcrumbs.value.slice(0, index + 1)
    currentParentId.value = crumb.id
    selectedLeaf.value = null
}

const resetToRoot = () => {
    currentParentId.value = null
    breadcrumbs.value = []
    selectedLeaf.value = null
}

onMounted(() => {
    if (props.selectedCategory) {
        autoSelectCategory()
    } else {
        restoreFromSavedData()
    }
})

watch(() => props.selectedCategory, (newCategory) => {
    if (newCategory) {
        autoSelectCategory()
    }
}, { immediate: true })
</script>