<template>
    <AppLayout :title="isEdit ? 'Edit Category' : 'Create Category'">
        <AppContainer>
            <!-- Page Header -->
            <div class="my-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            {{ isEdit ? `Edit: ${form.name}` : 'Create New Category' }}
                        </h1>
                        <p class="text-muted-foreground mt-2">
                            {{ isEdit ?
                                'Update category details and attributes' : 'Fill in the details to create a new category' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <AppButton label="Cancel" variant="outline" @click="cancel" :disabled="form.processing" />
                        <AppButton :label="isEdit ? 'Update Category' : 'Create Category'" icon="lucide:check"
                            :processing="form.processing" @click="submit"
                            class="bg-brand-orange hover:bg-brand-orange/80" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Form -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Info Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Basic Information</CardTitle>
                            <CardDescription>General details about the category</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <ValidationErrors :errors="form.errors" />

                            <TextInput label="Category Name *" v-model="form.name" :error="form.errors.name"
                                placeholder="Enter category name" required />

                            <SelectInput label="Parent Category" v-model="form.parent_id" :error="form.errors.parent_id"
                                placeholder="Select Parent Category (Optional)">
                                <SelectContent>
                                    <SelectItem :value="null">None (Top Level)</SelectItem>
                                    <SelectItem v-for="cat in allCategories" :key="cat.id" :value="cat.id"
                                        :disabled="isEdit && cat.id === category?.id">
                                        {{ cat.name }}
                                    </SelectItem>
                                </SelectContent>
                            </SelectInput>

                            <TextInput label="Position" type="number" v-model="form.position"
                                :error="form.errors.position" help="Order for display (lower numbers appear first)" />

                            <!-- Category Image -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Category Image
                                </label>
                                <input type="file" accept="image/*" @change="handleImageChange"
                                    class="block w-full text-sm text-gray-700 border rounded px-2 py-1" />

                                <!-- Image Preview -->
                                <div v-if="imagePreview" class="mt-2 relative inline-block">
                                    <img :src="imagePreview" alt="Preview" class="h-20 w-auto rounded" />
                                    <button @click="removeNewImage"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1">
                                        <Icon icon="lucide:x" class="size-3" />
                                    </button>
                                </div>
                                <div v-else-if="existingImage" class="mt-2 relative inline-block">
                                    <img :src="existingImage" alt="Existing" class="h-20 w-auto rounded" />
                                    <button @click="removeExistingImage"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1">
                                        <Icon icon="lucide:x" class="size-3" />
                                    </button>
                                    <span class="absolute -bottom-5 left-0 text-xs text-gray-500">Current image</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Brands Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Associated Brands</CardTitle>
                            <CardDescription>Select brands that belong to this category</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="border rounded-md p-3 max-h-48 overflow-y-auto">
                                <div v-for="brand in allBrands" :key="brand.id" class="flex items-center gap-2 mb-2">
                                    <input type="checkbox" :value="brand.id" v-model="form.brand_ids"
                                        class="rounded border-gray-300" />
                                    <label class="text-sm">{{ brand.name }}</label>
                                </div>
                            </div>
                            <p v-if="form.brand_ids.length === 0" class="text-sm text-muted-foreground mt-2">
                                No brands selected
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Attributes Card -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>Category Attributes</CardTitle>
                                    <CardDescription>Define specifications and filters for this category
                                    </CardDescription>
                                </div>
                                <AppButton variant="outline" size="sm" @click="addAttribute">
                                    <Plus class="size-4 mr-2" />
                                    Add Attribute
                                </AppButton>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="form.attributes.length === 0"
                                class="text-center py-8 border-2 border-dashed rounded-md">
                                <Icon icon="lucide:settings" class="size-8 text-muted-foreground mx-auto mb-2" />
                                <p class="text-sm text-muted-foreground">No attributes added yet</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Click "Add Attribute" to create specifications for this category
                                </p>
                            </div>

                            <div v-for="(attr, index) in form.attributes" :key="index"
                                class="border rounded-lg p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium">Attribute #{{ index + 1 }}</h4>
                                    <button type="button" @click="removeAttribute(index)"
                                        class="text-red-500 hover:text-red-700">
                                        <Icon icon="lucide:trash-2" class="size-4" />
                                    </button>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <TextInput label="Attribute Name" v-model="attr.name"
                                        :error="form.errors[`attributes.${index}.name`]"
                                        placeholder="e.g., Color, Size, Material" size="sm" />

                                    <SelectInput label="Type" v-model="attr.type"
                                        :error="form.errors[`attributes.${index}.type`]" size="sm"
                                        @update:model-value="handleTypeChange(attr, index)">
                                        <SelectContent>
                                            <SelectItem value="text">Text</SelectItem>
                                            <SelectItem value="number">Number</SelectItem>
                                            <SelectItem value="select">Select (Dropdown)</SelectItem>
                                            <SelectItem value="checkbox">Checkbox (Multiple)</SelectItem>
                                            <SelectItem value="radio">Radio (Single)</SelectItem>
                                            <SelectItem value="date">Date</SelectItem>
                                        </SelectContent>
                                    </SelectInput>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <SelectInput label="Attribute Group" v-model="attr.attribute_group_id"
                                        :error="form.errors[`attributes.${index}.attribute_group_id`]" size="sm">
                                        <SelectContent>
                                            <SelectItem :value="null">No Group</SelectItem>
                                            <SelectItem v-for="group in attributeGroups" :key="group.id"
                                                :value="group.id">
                                                {{ group.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </SelectInput>

                                    <div class="flex items-center gap-4 pt-2">
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" v-model="attr.is_required" />
                                            Required
                                        </label>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" v-model="attr.is_filterable" />
                                            Filterable
                                        </label>
                                    </div>
                                </div>

                                <!-- Options for Select, Checkbox, and Radio types -->
                                <div v-if="attr.type === 'select' || attr.type === 'checkbox' || attr.type === 'radio'"
                                    class="mt-3 pt-3 border-t">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Options
                                        <span v-if="attr.type === 'checkbox'" class="text-xs text-gray-500 ml-2">
                                            (Users can select multiple)
                                        </span>
                                        <span v-else-if="attr.type === 'radio'" class="text-xs text-gray-500 ml-2">
                                            (Users can select only one)
                                        </span>
                                        <span v-else class="text-xs text-gray-500 ml-2">
                                            (Dropdown selection)
                                        </span>
                                    </label>

                                    <div class="space-y-2">
                                        <div v-for="(opt, optIndex) in attr.options" :key="optIndex"
                                            class="flex gap-2 items-center">
                                            <div class="flex-1">
                                                <TextInput v-model="opt.value" placeholder="Option value" size="sm"
                                                    :error="form.errors[`attributes.${index}.options.${optIndex}.value`]" />
                                            </div>
                                            <button type="button" @click="removeOption(index, optIndex)"
                                                class="text-red-500 hover:text-red-700 p-1" title="Remove option">
                                                <Icon icon="lucide:x" class="size-4" />
                                            </button>
                                        </div>

                                        <button type="button" @click="addOption(index)"
                                            class="text-sm text-blue-500 hover:text-blue-700 flex items-center gap-1">
                                            <Plus class="size-3" />
                                            Add Option
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Actions Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex flex-col gap-2">
                                <AppButton :label="isEdit ? 'Update Category' : 'Create Category'" icon="lucide:check"
                                    :processing="form.processing" @click="submit"
                                    class="bg-brand-orange hover:bg-brand-orange/80 w-full justify-center" />

                                <AppButton label="Cancel" variant="outline" class="w-full justify-center"
                                    @click="cancel" :disabled="form.processing" />

                                <AppButton v-if="isEdit" label="Delete Category" variant="danger" icon="lucide:trash-2"
                                    class="w-full justify-center" @click="destroy" :disabled="form.processing" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Preview Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Preview</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div class="aspect-square rounded-lg bg-muted overflow-hidden">
                                    <img v-if="imagePreview || existingImage" :src="imagePreview || existingImage"
                                        class="w-full h-full object-cover" :alt="form.name || 'Category image'" />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <Icon icon="lucide:folder" class="size-12 text-muted-foreground" />
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <h3 class="font-semibold text-lg">{{ form.name || 'Category Name' }}</h3>
                                    <div class="text-sm text-muted-foreground">
                                        {{ form.parent_id ? 'Subcategory' : 'Top Level Category' }}
                                    </div>
                                    <div v-if="form.attributes.length > 0" class="pt-2">
                                        <div class="text-xs font-medium text-muted-foreground mb-1">Attributes:</div>
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="(attr, idx) in form.attributes.slice(0, 3)" :key="idx"
                                                class="px-2 py-0.5 bg-primary/5 text-primary rounded-full text-xs">
                                                {{ attr.name }}
                                            </span>
                                            <span v-if="form.attributes.length > 3"
                                                class="text-xs text-muted-foreground">
                                                +{{ form.attributes.length - 3 }} more
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Tips Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm">Tips for better categories</CardTitle>
                        </CardHeader>
                        <CardContent class="text-sm space-y-2 text-muted-foreground">
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Use clear, descriptive category names</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Add relevant attributes for filtering</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Set proper parent-child relationships</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Upload a representative category image</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Associate relevant brands</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppContainer>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Card from '@/components/ui/card/Card.vue'
import CardHeader from '@/components/ui/card/CardHeader.vue'
import CardTitle from '@/components/ui/card/CardTitle.vue'
import CardDescription from '@/components/ui/card/CardDescription.vue'
import CardContent from '@/components/ui/card/CardContent.vue'
import { Icon } from '@iconify/vue'
import { useAlertDialog } from '@/composables/useAlertDialog'
import { useBreadcrumb } from '@/composables/useBreadcrumb'

const props = defineProps<{
    category?: App.Data.CategoryData & {
        brands?: App.Data.BrandData[]
        attributes?: Array<App.Data.CategoryAttributeData & {
            options?: App.Data.AttributeOptionData[]
        }>
        files?: Array<{ file_url: string }>
    }
    allCategories: App.Data.CategoryData[]
    allBrands: App.Data.BrandData[]
    attributeGroups: App.Data.AttributeGroupData[]
}>()

const isEdit = computed(() => !!props.category)

// Form setup
const getDefaultForm = () => ({
    name: props.category?.name ?? '',
    parent_id: props.category?.parent_id ?? null,
    position: props.category?.position ?? 0,
    image: null as File | null,
    remove_image: false,
    brand_ids: props.category?.brands?.map(b => b.id) ?? [] as number[],
    attributes: props.category?.attributes?.map(attr => ({
        id: attr.id,
        name: attr.name,
        type: attr.type,
        attribute_group_id: attr.attribute_group_id,
        is_required: attr.is_required ?? false,
        is_filterable: attr.is_filterable ?? false,
        position: attr.position ?? 0,
        options: attr.options?.map(opt => ({
            id: opt.id,
            value: opt.value
        })) ?? []
    })) ?? [] as Array<{
        id?: number
        name: string
        type: string
        attribute_group_id: number | null
        is_required: boolean
        is_filterable: boolean
        position: number
        options: Array<{ id?: number; value: string }>
    }>
})

const form = useForm(getDefaultForm())

// Image handling
const imagePreview = ref<string | null>(null)
const existingImage = computed(() => {
    if (props.category?.files?.length && !form.remove_image) {
        return props.category.files[0].file_url
    }
    return null
})

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (!target.files?.[0]) return

    const file = target.files[0]
    form.image = file
    form.remove_image = false

    const reader = new FileReader()
    reader.onload = (e) => {
        imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
}

const removeNewImage = () => {
    form.image = null
    imagePreview.value = null
}

const removeExistingImage = () => {
    form.remove_image = true
    imagePreview.value = null
}

// Attribute methods
const addAttribute = () => {
    form.attributes.push({
        name: '',
        type: 'text',
        attribute_group_id: null,
        is_required: false,
        is_filterable: false,
        position: form.attributes.length,
        options: []
    })
}

const removeAttribute = (index: number) => {
    form.attributes.splice(index, 1)
}

const addOption = (attrIndex: number) => {
    form.attributes[attrIndex].options.push({ value: '' })
}

const removeOption = (attrIndex: number, optIndex: number) => {
    form.attributes[attrIndex].options.splice(optIndex, 1)
}

const handleTypeChange = (attr: any, index: number) => {
    // Clear options if type is changed from option-based to something else
    if (!['select', 'checkbox', 'radio'].includes(attr.type)) {
        attr.options = []
    }
    // Initialize options array if it doesn't exist for option-based types
    else if (!attr.options) {
        attr.options = []
    }
}

// Submit form
const submit = () => {
    const formData = new FormData()

    // Basic fields
    formData.append('name', form.name)
    if (form.parent_id) formData.append('parent_id', String(form.parent_id))
    formData.append('position', String(form.position))
    if (form.image) formData.append('image', form.image)
    if (form.remove_image) formData.append('remove_image', '1')

    // Brands
    form.brand_ids.forEach(id => {
        formData.append('brand_ids[]', String(id))
    })

    // Attributes
    form.attributes.forEach((attr, index) => {
        if (attr.id) formData.append(`attributes[${index}][id]`, String(attr.id))
        formData.append(`attributes[${index}][name]`, attr.name)
        formData.append(`attributes[${index}][type]`, attr.type)
        if (attr.attribute_group_id) {
            formData.append(`attributes[${index}][attribute_group_id]`, String(attr.attribute_group_id))
        }
        formData.append(`attributes[${index}][is_required]`, attr.is_required ? '1' : '0')
        formData.append(`attributes[${index}][is_filterable]`, attr.is_filterable ? '1' : '0')
        formData.append(`attributes[${index}][position]`, String(index))

        // Only send options for select, checkbox, and radio types
        if (['select', 'checkbox', 'radio'].includes(attr.type) && attr.options) {
            const validOptions = attr.options.filter(opt => opt.value.trim() !== '')
            validOptions.forEach((opt, optIndex) => {
                if (opt.id) formData.append(`attributes[${index}][options][${optIndex}][id]`, String(opt.id))
                formData.append(`attributes[${index}][options][${optIndex}][value]`, opt.value)
            })
        }
    })

    if (isEdit.value && props.category?.id) {
        formData.append('_method', 'PUT')
        router.post(route('categories.update', props.category.id), formData, {
            onSuccess: () => {
                router.visit(route('categories.index'))
            },
            onError: (errors) => {
                form.setError(errors)
            }
        })
    } else {
        router.post(route('categories.store'), formData, {
            onSuccess: () => {
                router.visit(route('categories.index'))
            },
            onError: (errors) => {
                form.setError(errors)
            }
        })
    }
}

const cancel = () => {
    router.visit(route('categories.index'))
}

const alert = useAlertDialog()
const destroy = async () => {
    if (!props.category?.id) return

    const confirmed = await alert.show({
        title: 'Delete Category',
        description: `Are you sure you want to delete "${props.category.name}"? This will also delete all subcategories and related data.`,
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
    })

    if (confirmed) {
        router.delete(route('categories.destroy', props.category.id), {
            onSuccess: () => {
                router.visit(route('categories.index'))
            }
        })
    }
}

// Breadcrumb
const { set, resetList } = useBreadcrumb()
onMounted(() => {
    resetList()
    set([
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Categories', href: route('categories.index') },
        { label: isEdit.value ? 'Edit Category' : 'Create Category', href: '#' }
    ])
})
</script>