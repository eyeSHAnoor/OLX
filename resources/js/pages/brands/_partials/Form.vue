<template>
    <AppLayout :title="isEdit ? 'Edit Brand' : 'Create Brand'">
        <AppContainer>
            <!-- Page Header -->
            <div class="my-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">
                            {{ isEdit ? `Edit: ${form.name}` : 'Create New Brand' }}
                        </h1>
                        <p class="text-muted-foreground mt-2">
                            {{ isEdit ? 'Update brand details and models' : 'Fill in the details to create a new brand'
                            }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <AppButton label="Cancel" variant="outline" @click="cancel" :disabled="form.processing" />
                        <AppButton :label="isEdit ? 'Update Brand' : 'Create Brand'" icon="lucide:check"
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
                            <CardDescription>General details about the brand</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <ValidationErrors :errors="form.errors" />

                            <TextInput label="Brand Name *" v-model="form.name" :error="form.errors.name"
                                placeholder="Enter brand name" required />

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Assign to Categories</label>
                                <p class="text-xs text-muted-foreground mb-2">
                                    Select categories where this brand will be available
                                </p>
                                <CategoryTreeSelect v-model="form.category_ids" :allCategories="categories"
                                    :error="form.errors.category_ids" :initialSelected="form.category_ids" multiple />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Models Card -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-center justify-between">
                                <div>
                                    <CardTitle>Brand Models</CardTitle>
                                    <CardDescription>Add models/versions for this brand (e.g., iPhone 14, Galaxy S23)
                                    </CardDescription>
                                </div>
                                <AppButton variant="outline" size="sm" @click="addModel">
                                    <Plus class="size-4 mr-2" />
                                    Add Model
                                </AppButton>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div v-if="form.models.length === 0"
                                class="text-center py-8 border-2 border-dashed rounded-md">
                                <Icon icon="lucide:package" class="size-8 text-muted-foreground mx-auto mb-2" />
                                <p class="text-sm text-muted-foreground">No models added yet</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Click "Add Model" to add models or versions for this brand
                                </p>
                            </div>

                            <div v-else class="space-y-3">
                                <div v-for="(model, index) in form.models" :key="index"
                                    class="flex items-center gap-3 p-3 border rounded-lg hover:bg-muted/50">
                                    <div class="flex-1">
                                        <TextInput v-model="model.name" placeholder="Model name (e.g., iPhone 14 Pro)"
                                            size="sm" :error="form.errors[`models.${index}.name`]" />
                                    </div>
                                    <button type="button" @click="removeModel(index)"
                                        class="text-red-500 hover:text-red-700 p-1" title="Remove model">
                                        <Icon icon="lucide:trash-2" class="size-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Tips for models -->
                            <div v-if="form.models.length > 0" class="mt-4 p-3 bg-blue-50 rounded-md">
                                <div class="flex items-start gap-2">
                                    <Icon icon="lucide:info" class="size-4 text-blue-500 mt-0.5" />
                                    <div class="text-xs text-blue-700">
                                        <p class="font-medium mb-1">Tips for models:</p>
                                        <ul class="list-disc list-inside space-y-0.5">
                                            <li>Add specific product versions (e.g., iPhone 14, iPhone 14 Pro)</li>
                                            <li>Include year or generation if applicable (e.g., Samsung Galaxy S23
                                                Ultra)</li>
                                            <li>Models help users find exact products they're looking for</li>
                                        </ul>
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
                                <AppButton :label="isEdit ? 'Update Brand' : 'Create Brand'" icon="lucide:check"
                                    :processing="form.processing" @click="submit"
                                    class="bg-brand-orange hover:bg-brand-orange/80 w-full justify-center" />

                                <AppButton label="Cancel" variant="outline" class="w-full justify-center"
                                    @click="cancel" :disabled="form.processing" />

                                <AppButton v-if="isEdit" label="Delete Brand" variant="danger" icon="lucide:trash-2"
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
                                <div class="bg-muted rounded-lg p-4 text-center">
                                    <Icon icon="lucide:tag" class="size-12 text-muted-foreground mx-auto mb-2" />
                                    <h3 class="font-semibold text-lg">{{ form.name || 'Brand Name' }}</h3>
                                    <div class="text-sm text-muted-foreground mt-1">
                                        {{ form.category_ids.length }} category(s) selected
                                    </div>
                                </div>

                                <div v-if="form.models.length > 0" class="pt-2">
                                    <div class="text-xs font-medium text-muted-foreground mb-2">Models:</div>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="(model, idx) in form.models.slice(0, 5)" :key="idx"
                                            class="px-2 py-1 bg-primary/5 text-primary rounded-md text-xs">
                                            {{ model.name }}
                                        </span>
                                        <span v-if="form.models.length > 5" class="text-xs text-muted-foreground">
                                            +{{ form.models.length - 5 }} more
                                        </span>
                                    </div>
                                </div>

                                <div v-if="form.category_ids.length > 0" class="pt-2 border-t">
                                    <div class="text-xs font-medium text-muted-foreground mb-2">Categories:</div>
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="catId in form.category_ids.slice(0, 3)" :key="catId"
                                            class="px-2 py-0.5 bg-muted rounded-full text-xs">
                                            {{ getCategoryName(catId) }}
                                        </span>
                                        <span v-if="form.category_ids.length > 3" class="text-xs text-muted-foreground">
                                            +{{ form.category_ids.length - 3 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Tips Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-sm">Tips for brands</CardTitle>
                        </CardHeader>
                        <CardContent class="text-sm space-y-2 text-muted-foreground">
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Use official brand names (e.g., "Apple" not "iPhone")</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Add common models for better search results</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Assign to relevant categories only</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Keep model names consistent (e.g., "iPhone 14", "iPhone 14 Pro")</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                                <span>Models help users filter and find products faster</span>
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
import CategoryTreeSelect from '@/components/CategoryTreeSelect.vue'
import Card from '@/components/ui/card/Card.vue'
import CardHeader from '@/components/ui/card/CardHeader.vue'
import CardTitle from '@/components/ui/card/CardTitle.vue'
import CardDescription from '@/components/ui/card/CardDescription.vue'
import CardContent from '@/components/ui/card/CardContent.vue'
import { Icon } from '@iconify/vue'
import { useAlertDialog } from '@/composables/useAlertDialog'
import { useBreadcrumb } from '@/composables/useBreadcrumb'
import { Plus } from 'lucide-vue-next'

const props = defineProps<{
    brand?: App.Data.BrandData & {
        models?: Array<{ id: number; name: string }>
        categories?: App.Data.CategoryData[]
    }
    categories: App.Data.CategoryData[]
}>()

const isEdit = computed(() => !!props.brand)

// Helper function to get category name by ID
const getCategoryName = (categoryId: string | number) => {
    const category = props.categories.find(c => c.id === categoryId)
    return category?.name || 'Unknown'
}

// Form setup
const getDefaultForm = () => ({
    name: props.brand?.name ?? '',
    category_ids: props.brand?.categories?.map(cat => cat.id) ?? [] as (string | number)[],
    models: props.brand?.models?.map(model => ({
        id: model.id,
        name: model.name
    })) ?? [] as Array<{ id?: number; name: string }>
})

const form = useForm(getDefaultForm())

// Model methods
const addModel = () => {
    form.models.push({ name: '' })
}

const removeModel = (index: number) => {
    form.models.splice(index, 1)
}

// Submit form
const submit = () => {
    const formData = new FormData()

    // Basic fields
    formData.append('name', form.name)

    // Categories
    form.category_ids.forEach(id => {
        formData.append('category_ids[]', String(id))
    })

    // Models
    form.models.forEach((model, index) => {
        if (model.id) {
            formData.append(`models[${index}][id]`, String(model.id))
        }
        if (model.name.trim()) {
            formData.append(`models[${index}][name]`, model.name)
        }
    })

    if (isEdit.value && props.brand?.id) {
        formData.append('_method', 'PUT')
        router.post(route('brands.update', props.brand.id), formData, {
            onSuccess: () => {
                router.visit(route('brands.index'))
            },
            onError: (errors) => {
                form.setError(errors)
            }
        })
    } else {
        router.post(route('brands.store'), formData, {
            onSuccess: () => {
                router.visit(route('brands.index'))
            },
            onError: (errors) => {
                form.setError(errors)
            }
        })
    }
}

const cancel = () => {
    router.visit(route('brands.index'))
}

const alert = useAlertDialog()
const destroy = async () => {
    if (!props.brand?.id) return

    const confirmed = await alert.show({
        title: 'Delete Brand',
        description: `Are you sure you want to delete "${props.brand.name}"? This will also delete all associated models and ads.`,
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
    })

    if (confirmed) {
        router.delete(route('brands.destroy', props.brand.id), {
            onSuccess: () => {
                router.visit(route('brands.index'))
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
        { label: 'Brands', href: route('brands.index') },
        { label: isEdit.value ? 'Edit Brand' : 'Create Brand', href: '#' }
    ])
})
</script>