<template>
    <Dialog v-model:open="model">
        <DialogContent class="!w-full !overflow-y-auto px-7 xl:!w-8/12">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-x-3">
                    Category Details
                </DialogTitle>
            </DialogHeader>

            <div class="mt-3 grid gap-y-4">
                <!-- Validation Errors -->
                <ValidationErrors />

                <!-- Parent Category -->
                <SelectInput label="Parent Category" :id="`parent_id`" v-model="form.parent_id"
                    :error="form.errors.parent_id" placeholder="Select Parent Category" wrapper-class="w-full">
                    <SelectContent>
                        <SelectItem v-for="(item, index) in allCategories" :key="index" :value="item.id">
                            {{ item.name }}
                        </SelectItem>
                    </SelectContent>
                </SelectInput>

                <!-- Category Name -->
                <TextInput label="Category Name" id="name" v-model="form.name" :error="form.errors.name" />

                <!-- Category Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Category Image
                    </label>
                    <input type="file" accept="image/*" @change="handleImageChange"
                        class="block w-full text-sm text-gray-700 border rounded px-2 py-1" />
                    <!-- Preview uploaded image -->
                    <div v-if="imagePreview" class="mt-2">
                        <img :src="imagePreview" alt="Preview" class="h-20 w-auto rounded" />
                    </div>
                    <!-- Show existing image if editing and no new file selected -->
                    <div v-else-if="category?.files?.length" class="mt-2">
                        <img :src="category.files[0].file_url" alt="Existing" class="h-20 w-auto rounded" />
                    </div>
                </div>
            </div>

            <!-- Dialog Footer -->
            <DialogFooter>
                <div class="flex items-center justify-between gap-1">
                    <!-- Delete button -->
                    <AppButton v-if="form.id" label="Delete" icon="lucide:trash-2" variant="danger" size="sm"
                        :processing="form.processing" @click="destroy" />
                    <!-- Submit button -->
                    <AppButton size="sm" :processing="form.processing" @click="submit" class="ml-auto" />
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import TextInput from '@/components/TextInput.vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'

const { category, allCategories } = defineProps<{
    category?: App.Data.CategoryData
    allCategories: App.Data.CategoryData[]
}>()

const model = defineModel()

// Form setup
const getDefaultForm = (item?: App.Data.CategoryData) => ({
    id: item?.id ?? '',
    name: item?.name ?? '',
    parent_id: item?.parent_id ?? '',
})

// Initialize form without image
const form = useForm({
    ...getDefaultForm(category),
    image: null as File | null | string,
})

watch(model, (isOpen) => {
    if (isOpen) {
        const newValues = getDefaultForm(category)
        form.defaults({
            ...newValues,
            image: null,
        })
        form.reset()
        imageFile.value = null
        imagePreview.value = null
    }
})

// Image upload state
const imageFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement
    if (!target.files || !target.files[0]) return

    imageFile.value = target.files[0]
    form.image = imageFile.value // Assign to form

    // Preview
    const reader = new FileReader()
    reader.onload = (e) => {
        imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(imageFile.value)
}

// Form submit - CORRECTED VERSION
const submit = () => {
    // Create FormData
    const formData = new FormData()

    // Add all form fields
    formData.append('name', form.name)
    formData.append('parent_id', form.parent_id)

    // Add image if exists
    if (form.image instanceof File) {
        formData.append('image', form.image)
    }

    // For update
    if (form.id) {
        // Use post method with _method PUT for FormData
        formData.append('_method', 'PUT')

        // Use Inertia's visit method for FormData
        router.visit(route('categories.update', form.id), {
            method: 'post',
            data: formData,
            preserveScroll: true,
            onSuccess: () => {
                model.value = false
                form.reset()
            },
            onError: (errors) => {
                form.setError(errors)
            }
        })
    }
    // For create
    else {
        // Use Inertia's visit method for FormData
        router.visit(route('categories.store'), {
            method: 'post',
            data: formData,
            preserveScroll: true,
            onSuccess: () => {
                model.value = false
                form.reset()
            },
            onError: (errors) => {
                form.setError(errors)
            }
        })
    }
}

// Alternative solution using form.transform() method
const submitAlternative = () => {
    // Transform the form to FormData before sending
    form.transform((data) => {
        const formData = new FormData()

        // Add regular fields
        Object.entries(data).forEach(([key, value]) => {
            if (key !== 'image' && value !== null) {
                formData.append(key, value as string)
            }
        })

        // Add image file
        if (data.image instanceof File) {
            formData.append('image', data.image)
        }

        return formData
    })

    if (form.id) {
        form.put(route('categories.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                model.value = false
                form.reset()
            },
        })
    } else {
        form.post(route('categories.store'), {
            preserveScroll: true,
            onSuccess: () => {
                model.value = false
                form.reset()
            },
        })
    }
}

// Delete
const alert = useAlertDialog()
const destroy = async () => {
    const confirmed = await alert.show({
        title: 'Delete Category',
        description: 'Are you sure you want to delete this Category? This action cannot be undone.',
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
    })

    if (confirmed && form.id) {
        form.delete(route('categories.destroy', form.id), {
            onSuccess: () => (model.value = false),
        })
    }
}
</script>