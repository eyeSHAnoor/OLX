<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps<{
    banner?: App.Data.BannerData | null;
    categories: App.Data.CategoryData[];
}>();

const emit = defineEmits(['update:modelValue', 'success']);

const { banner, categories } = props;

const isOpen = defineModel<boolean>('modelValue', { default: false });

const form = useForm({
    title: '',
    image_url: '',
    link: '',
    position: 'homepage' as 'homepage' | 'category' | 'sidebar' | 'floating',
    target_category_id: null as number | null,
    start_date: null as string | null,
    end_date: null as string | null,
    status: true,
});

// Image preview
const imagePreview = ref<string | null>(null);
const imageError = ref(false);

// New: file upload handling
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);

const triggerFileUpload = () => {
    fileInput.value?.click();
};

const onFileSelected = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowed.includes(file.type)) {
        form.errors.image_url = 'Please select a valid image (JPEG, PNG, GIF, or WebP).';
        return;
    }

    form.clearErrors('image_url');
    selectedFile.value = file;
    imagePreview.value = URL.createObjectURL(file);
    imageError.value = false;
};

// Date range
const dateRange = computed({
    get: () => {
        if (form.start_date && form.end_date) {
            return [new Date(form.start_date), new Date(form.end_date)];
        }
        return null;
    },
    set: (value) => {
        if (value && value.length === 2) {
            form.start_date = value[0]?.toISOString().split('T')[0] || null;
            form.end_date = value[1]?.toISOString().split('T')[0] || null;
        } else {
            form.start_date = null;
            form.end_date = null;
        }
    }
});

const positionOptions = [
    { value: 'homepage', label: 'Homepage', icon: 'lucide:home', description: 'Display on the main homepage' },
    { value: 'category', label: 'Category Pages', icon: 'lucide:layout-grid', description: 'Display on category listing pages' },
    { value: 'sidebar', label: 'Sidebar', icon: 'lucide:sidebar', description: 'Display in sidebar widgets' },
    { value: 'floating', label: 'Floating', icon: 'lucide:move', description: 'Floating banner that stays visible' },
];

// Watch for banner changes
watch(() => props.banner, (newBanner) => {
    if (newBanner) {
        form.title = newBanner.title || '';
        form.image_url = newBanner.image_url || '';
        form.link = newBanner.link || '';
        form.position = newBanner.position || 'homepage';
        form.target_category_id = newBanner.target_category_id || null;
        form.start_date = newBanner.start_date || null;
        form.end_date = newBanner.end_date || null;
        form.status = newBanner.status ?? true;

        if (newBanner.image_url) {
            imagePreview.value = newBanner.image_url;
            imageError.value = false;
        }
        selectedFile.value = null; // Reset file selection when editing
    } else {
        form.reset();
        imagePreview.value = null;
        imageError.value = false;
        selectedFile.value = null;
    }
}, { immediate: true });

watch(() => form.image_url, (newUrl) => {
    if (newUrl) {
        imagePreview.value = newUrl;
        imageError.value = false;
    } else if (!selectedFile.value) {
        imagePreview.value = null;
    }
});

const closeModal = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
    imageError.value = false;
    selectedFile.value = null;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            emit('success');
        },
        onError: (errors: any) => console.error('Form errors:', errors),
    };

    if (selectedFile.value) {
        // Send as FormData when a file is attached
        const formData = new FormData();
        formData.append('_method', banner?.id ? 'PUT' : 'POST');
        formData.append('title', form.title);
        formData.append('image', selectedFile.value); // the file field
        formData.append('link', form.link);
        formData.append('position', form.position);
        if (form.target_category_id) formData.append('target_category_id', String(form.target_category_id));
        if (form.start_date) formData.append('start_date', form.start_date);
        if (form.end_date) formData.append('end_date', form.end_date);
        formData.append('status', form.status ? '1' : '0');

        const url = banner?.id
            ? route('banners.update', banner.id)
            : route('banners.store');

        router.post(url, formData, {
            ...options,
            forceFormData: true,
        });
    } else {
        // Standard Inertia request (JSON)
        const payload = { ...form.data(), _method: banner?.id ? 'PUT' : undefined };
        router.post(
            banner?.id
                ? route('banners.update', banner.id)
                : route('banners.store'),
            payload,
            options
        );
    }
};

const testImageUrl = () => {
    if (form.image_url) {
        imageError.value = false;
        imagePreview.value = form.image_url;
    }
};
</script>

<template>
    <Dialog :open="isOpen" @update:open="closeModal">
        <!-- Responsive dialog: full width on mobile, 8/12 on md+ -->
        <DialogContent class="!w-full md:!w-8/12 !max-w-6xl !overflow-y-auto px-4 sm:px-7 max-h-[90vh]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Icon :icon="banner ? 'lucide:edit' : 'lucide:plus-circle'" class="size-5" />
                    <span>{{ banner ? 'Edit Banner' : 'Create New Banner' }}</span>
                </DialogTitle>
                <DialogDescription>
                    {{ banner ? 'Update the banner details below' : 'Fill in the information to create a new banner' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Validation Errors -->
                <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-sm text-red-700">
                        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                </div>

                <!-- Title -->
                <div>
                    <label class="text-sm font-medium block mb-2">
                        Banner Title <span class="text-destructive">*</span>
                    </label>
                    <input v-model="form.title" type="text" placeholder="e.g., Summer Sale Banner"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        :class="{ 'border-destructive': form.errors.title }" />
                    <p v-if="form.errors.title" class="text-sm text-destructive mt-1">{{ form.errors.title }}</p>
                </div>

                <!-- Image URL + Upload -->
                <div>
                    <label class="text-sm font-medium block mb-2">
                        Image URL <span class="text-destructive">*</span>
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="flex-1">
                            <input v-model="form.image_url" type="url" placeholder="https://example.com/banner.jpg"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-destructive': form.errors.image_url }" @blur="testImageUrl" />
                        </div>
                        <Button type="button" variant="secondary" @click="triggerFileUpload" class="w-full sm:w-auto">
                            <Icon icon="lucide:upload" class="size-4" />
                            Gallery
                        </Button>
                    </div>
                    <p v-if="form.errors.image_url" class="text-sm text-destructive mt-1">{{ form.errors.image_url }}
                    </p>

                    <!-- Image Preview -->
                    <div v-if="imagePreview" class="mt-3">
                        <p class="text-xs text-muted-foreground mb-2">Preview:</p>
                        <div
                            class="relative aspect-[16/9] w-full max-w-md rounded-lg overflow-hidden border bg-gray-50">
                            <img :src="imagePreview" alt="Banner preview" class="w-full h-full object-contain"
                                @error="imageError = true" />
                            <div v-if="imageError"
                                class="absolute inset-0 flex items-center justify-center bg-gray-100">
                                <div class="text-center">
                                    <Icon icon="lucide:image-off" class="size-8 text-gray-400 mx-auto mb-2" />
                                    <p class="text-sm text-gray-500">Failed to load image</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden file input -->
                <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileSelected" />

                <!-- Link URL -->
                <div>
                    <label class="text-sm font-medium block mb-2">Link URL (Optional)</label>
                    <input v-model="form.link" type="url" placeholder="https://example.com/offer"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                        :class="{ 'border-destructive': form.errors.link }" />
                    <p class="text-xs text-muted-foreground mt-1">Where users will be redirected when clicking the
                        banner</p>
                    <p v-if="form.errors.link" class="text-sm text-destructive mt-1">{{ form.errors.link }}</p>
                </div>

                <!-- Position -->
                <div>
                    <label class="text-sm font-medium block mb-2">
                        Position <span class="text-destructive">*</span>
                    </label>
                    <!-- Responsive grid: single column on mobile, 2 columns from sm breakpoint -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button v-for="option in positionOptions" :key="option.value" type="button"
                            @click="form.position = option.value"
                            class="flex flex-col items-start p-3 border rounded-lg transition-colors" :class="[
                                form.position === option.value
                                    ? 'border-primary bg-primary/5 ring-2 ring-primary/20'
                                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                            ]">
                            <div class="flex items-center gap-2 mb-1">
                                <Icon :icon="option.icon" class="size-4"
                                    :class="form.position === option.value ? 'text-primary' : 'text-gray-500'" />
                                <span class="text-sm font-medium">{{ option.label }}</span>
                            </div>
                            <p class="text-xs text-left text-muted-foreground">{{ option.description }}</p>
                        </button>
                    </div>
                    <p v-if="form.errors.position" class="text-sm text-destructive mt-1">{{ form.errors.position }}</p>
                </div>

                <!-- Target Category -->
                <div v-if="form.position === 'category'">
                    <label class="text-sm font-medium block mb-2">Target Category (Optional)</label>
                    <select v-model="form.target_category_id"
                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        <option :value="null">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                    <p class="text-xs text-muted-foreground mt-1">Leave empty to show on all category pages</p>
                    <p v-if="form.errors.target_category_id" class="text-sm text-destructive mt-1">{{
                        form.errors.target_category_id }}</p>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="text-sm font-medium block mb-2">Schedule (Optional)</label>
                    <VueDatePicker v-model="dateRange" range :enable-time-picker="false" :format="'MM/dd/yyyy'"
                        :placeholder="'Select start and end dates'" :clearable="true" :min-date="new Date()"
                        class="w-full" :class="{ 'dp-error': form.errors.start_date || form.errors.end_date }" />
                    <p class="text-xs text-muted-foreground mt-1">Leave empty for no date restrictions</p>
                    <p v-if="form.errors.start_date" class="text-sm text-destructive mt-1">{{ form.errors.start_date }}
                    </p>
                    <p v-if="form.errors.end_date" class="text-sm text-destructive mt-1">{{ form.errors.end_date }}</p>
                </div>

                <!-- Status -->
                <div>
                    <label class="text-sm font-medium block mb-2">Status</label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.status" :value="true" class="size-4 text-primary" />
                            <span>Active</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.status" :value="false" class="size-4 text-primary" />
                            <span>Inactive</span>
                        </label>
                    </div>
                    <p v-if="form.errors.status" class="text-sm text-destructive mt-1">{{ form.errors.status }}</p>
                </div>

                <!-- Form Actions -->
                <!-- Stack on mobile, horizontal on sm+ -->
                <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t">
                    <AppButton type="button" variant="outline" @click="closeModal" :disabled="form.processing"
                        class="w-full sm:w-auto">
                        Cancel
                    </AppButton>
                    <AppButton type="submit" :processing="form.processing"
                        :label="banner ? 'Update Banner' : 'Create Banner'" class="w-full sm:w-auto" />
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.dp-error :deep(.dp__input) {
    border-color: #ef4444;
}
</style>