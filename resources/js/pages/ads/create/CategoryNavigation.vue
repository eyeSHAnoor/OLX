<template>
    <div class="space-y-8">

        <!-- Breadcrumb -->
        <div class="flex items-center flex-wrap gap-2 text-sm">
            <button @click="resetToRoot" class="font-medium flex items-center gap-1" :class="theme.textAccent">
                <Icon icon="lucide:home" class="size-4" />
                All Categories
            </button>

            <template v-for="(crumb, index) in breadcrumbs" :key="crumb.id">
                <Icon icon="lucide:chevron-right" class="size-4" :class="theme.textMuted" />

                <button @click="navigateTo(index)" :class="[
                    'transition-colors',
                    index === breadcrumbs.length - 1
                        ? `${theme.text} font-semibold`
                        : `${theme.textMuted} ${theme.hover}`
                ]">
                    {{ crumb.name }}
                </button>
            </template>
        </div>

        <!-- Title -->
        <div>
            <h2 class="text-2xl font-semibold" :class="theme.textAccent">
                {{ currentTitle }}
            </h2>
            <p class="text-sm mt-1" :class="theme.textMuted">
                Choose the most relevant category for your listing
            </p>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="category in currentCategories" :key="category.id" @click="handleClick(category)"
                class="group border rounded-xl p-5 cursor-pointer transition-all duration-200 hover:shadow-lg" :class="[
                    theme.card,
                    theme.border,
                    isSelectedCategory(category) ? theme.border : theme.borderHover
                ]"
                :style="isSelectedCategory(category) ? { borderColor: getThemeColor(), backgroundColor: getThemeColorWithOpacity(0.05) } : {}">
                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-4">

                        <!-- Category Icon -->
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center overflow-hidden"
                            :class="theme.bgLight">
                            <template v-if="category.files && category.files.length > 0">
                                <img :src="category.files[0].file_url" :alt="category.name"
                                    class="w-full h-full object-cover" />
                            </template>
                            <template v-else>
                                <Icon icon="lucide:folder" class="size-6" :class="theme.icon" />
                            </template>
                        </div>

                        <!-- Name -->
                        <div>
                            <h3 class="font-semibold" :class="theme.text">
                                {{ category.name }}
                            </h3>
                            <p class="text-xs mt-1" :class="theme.textMuted">
                                {{ category.children_recursive?.length || 0 }} subcategories
                            </p>
                        </div>
                    </div>

                    <!-- Arrow if not leaf -->
                    <Icon v-if="!isLeaf(category)" icon="lucide:chevron-right" class="size-5 transition-colors"
                        :class="theme.textMuted" />
                    <Icon v-else-if="isSelectedCategory(category)" icon="lucide:check-circle" class="size-5"
                        :class="theme.icon" />
                </div>
            </div>
        </div>

        <!-- Selected Leaf Category -->
        <div v-if="selectedLeaf" class="mt-8 p-6 border rounded-xl" :class="[theme.card, theme.border]"
            :style="{ borderColor: getThemeColor(), backgroundColor: getThemeColorWithOpacity(0.05) }">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm" :class="theme.textMuted">Selected Category</p>
                    <p class="text-lg font-semibold" :class="theme.textAccent">
                        {{ selectedLeaf.name }}
                    </p>
                    <p class="text-xs mt-1" :class="theme.textMuted">
                        {{breadcrumbs.map(c => c.name).join(' / ')}}
                    </p>
                </div>

                <Icon icon="lucide:check-circle" class="size-7" :class="theme.icon" />
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Icon } from '@iconify/vue'
import { useTheme } from '@/Composables/useTheme'

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

const { theme } = useTheme()

// Helper to get theme color
const getThemeColor = () => {
    const colorMap = {
        'premium': '#f26822', // brand-orange
        'pro': '#3b5fb5', // brand-blue
        'free': '#00c2bb', // brand-teal
    }

    const activePlan = theme.value?.bg?.includes('black') ? 'premium'
        : theme.value?.bg?.includes('blue') ? 'pro'
            : 'free'

    return colorMap[activePlan] || '#00c2bb'
}

// Helper to get theme color with opacity
const getThemeColorWithOpacity = (opacity) => {
    const color = getThemeColor()
    const r = parseInt(color.slice(1, 3), 16)
    const g = parseInt(color.slice(3, 5), 16)
    const b = parseInt(color.slice(5, 7), 16)
    return `rgba(${r}, ${g}, ${b}, ${opacity})`
}
</script>