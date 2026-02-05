<script setup lang="ts">
import { CardContent } from '@/components/ui/card';
import { usePage, router } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import { useDropZone } from '@vueuse/core';

const { ad, categories, brands } = defineProps<{
    ad?: App.Data.AdData;
    categories: App.Data.CategoryData[];
    brands: App.Data.BrandData[];
}>();

interface AdFormData {
    id?: string | number;
    category_id: string | number;
    brand_id: string | number;
    ad_title: string;
    description: string;
    price: string | number;
    location: string;
    seller_name: string;
    seller_phone: string;
    images: File[];
    remove_images: (string | number)[];
}

interface AdImageData {
    id: string | number;
    path: string;
    is_primary: boolean;
}

const getDefaultForm = (item: App.Data.AdData | undefined): AdFormData => ({
    id: item?.id ?? '',
    category_id: item?.category_id ?? '',
    brand_id: item?.brand_id ?? '',
    ad_title: item?.ad_title ?? '',
    description: item?.description ?? '',
    price: item?.price ?? '',
    location: item?.location ?? '',
    seller_name: item?.seller_name ?? '',
    seller_phone: item?.seller_phone ?? '',
    images: [],
    remove_images: [],
});

const page = usePage();
const form = useForm<AdFormData>({ ...getDefaultForm(ad) });
const model = defineModel();

const existingImages = ref<AdImageData[]>(ad?.images || []);

const filteredBrands = computed(() => {
    if (!form.category_id) return [];

    return brands.filter(brand =>
        brand.categories?.some(
            (cat: any) => cat.id == form.category_id
        )
    );
});
watch(() => form.category_id, () => {
    form.brand_id = '';
});

watch(model, (isOpen) => {
    if (isOpen) {
        const newValues = getDefaultForm(ad);
        form.defaults(newValues);
        form.reset();
        existingImages.value = ad?.images || [];
        form.remove_images = [];
        form.images = [];
        imagePreviews.value = [];
    }
});

// File upload handling
const imagePreviews = ref<string[]>([]);

const onFilesSelected = (event: Event) => {
    const files = Array.from((event.target as HTMLInputElement).files || []);
    handleFiles(files);
    // Reset the file input to allow selecting same files again
    (event.target as HTMLInputElement).value = '';
};

const { isOverDropZone } = useDropZone(document.body, {
    onDrop: (files) => {
        handleFiles(Array.from(files));
    },
});

const handleFiles = (files: File[]) => {
    const validFiles = files.filter(file =>
        file.type.startsWith('image/') &&
        ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(file.type)
    );

    if (validFiles.length + form.images.length > 10) {
        alert('Maximum 10 images allowed');
        return;
    }

    validFiles.forEach(file => {
        form.images.push(file);

        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target?.result as string);
        };
        reader.readAsDataURL(file);
    });
};

const removeNewImage = (index: number) => {
    form.images.splice(index, 1);
    imagePreviews.value.splice(index, 1);
};

const removeExistingImage = (imageId: string | number) => {
    const index = existingImages.value.findIndex(img => img.id === imageId);
    if (index > -1) {
        existingImages.value.splice(index, 1);
        // Make sure remove_images is initialized as an array
        if (!Array.isArray(form.remove_images)) {
            form.remove_images = [];
        }
        form.remove_images.push(imageId);
    }
};

const setPrimaryImage = async (imageId: string | number) => {
    if (!form.id) return;

    try {
        await router.post(route('ads.set-primary-image', form.id), {
            image_id: imageId,
        }, {
            preserveScroll: true,
        });

        // Update local state
        existingImages.value = existingImages.value.map(img => ({
            ...img,
            is_primary: img.id === imageId
        }));
    } catch (error) {
        console.error('Failed to set primary image:', error);
    }
};

const submit = () => {
    // Create FormData object
    const formData = new FormData();

    // Add regular form fields
    const regularFields = ['category_id', 'brand_id', 'ad_title', 'description', 'price', 'location', 'seller_name', 'seller_phone'];
    regularFields.forEach(field => {
        if (form[field as keyof AdFormData] !== null && form[field as keyof AdFormData] !== undefined) {
            formData.append(field, form[field as keyof AdFormData].toString());
        }
    });

    // Add new images
    if (form.images && form.images.length > 0) {
        form.images.forEach((file, index) => {
            formData.append(`images[${index}]`, file);
        });
    } else {
        // If no new images, send empty array to prevent undefined
        formData.append('images', '[]');
    }

    // Add images to remove
    if (form.remove_images && form.remove_images.length > 0) {
        form.remove_images.forEach((id, index) => {
            formData.append(`remove_images[${index}]`, id.toString());
        });
    } else {
        // If no images to remove, send empty array
        formData.append('remove_images', '[]');
    }

    // Add _method for PUT if updating
    if (form.id) {
        formData.append('_method', 'PUT');
    }

    // Use Inertia's form with transform
    const submitForm = useForm({
        id: form.id,
        category_id: form.category_id,
        brand_id: form.brand_id,
        ad_title: form.ad_title,
        description: form.description,
        price: form.price,
        location: form.location,
        seller_name: form.seller_name,
        seller_phone: form.seller_phone,
        images: form.images,
        remove_images: form.remove_images,
    }, {
        // Transform the data before sending
        transform: (data) => {
            const formData = new FormData();

            // Add regular fields
            Object.keys(data).forEach(key => {
                if (key === 'images' && data.images && data.images.length > 0) {
                    // Handle images as files
                    data.images.forEach((file: File, index: number) => {
                        formData.append(`images[${index}]`, file);
                    });
                } else if (key === 'remove_images' && data.remove_images && data.remove_images.length > 0) {
                    // Handle remove_images as array
                    data.remove_images.forEach((id: string | number, index: number) => {
                        formData.append(`remove_images[${index}]`, id.toString());
                    });
                } else if (key !== 'images' && key !== 'remove_images' && data[key as keyof typeof data] !== null && data[key as keyof typeof data] !== undefined) {
                    // Add other fields
                    formData.append(key, data[key as keyof typeof data].toString());
                }
            });

            // Add _method for PUT
            if (form.id) {
                formData.append('_method', 'PUT');
            }

            return formData;
        },
    });

    // Submit the form
    if (form.id) {
        submitForm.post(route('ads.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                model.value = false;
                submitForm.reset();
            },
        });
    } else {
        submitForm.post(route('ads.store'), {
            preserveScroll: true,
            onSuccess: () => {
                model.value = false;
                submitForm.reset();
            },
        });
    }
};

// Alternative simpler submit method
const submitSimpler = () => {
    // Create a new form instance with all data
    const submitForm = useForm({
        id: form.id,
        category_id: form.category_id,
        brand_id: form.brand_id,
        ad_title: form.ad_title,
        description: form.description,
        price: form.price,
        location: form.location,
        seller_name: form.seller_name,
        seller_phone: form.seller_phone,
        images: form.images,
        remove_images: form.remove_images,
    });

    // Use Inertia's file upload handling
    if (form.id) {
        submitForm.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(route('ads.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                model.value = false;
                submitForm.reset();
            },
        });
    } else {
        submitForm.post(route('ads.store'), {
            preserveScroll: true,
            onSuccess: () => {
                model.value = false;
                submitForm.reset();
            },
        });
    }
};

const alert = useAlertDialog();
const destroy = async () => {
    if (!form.id) return;

    const confirmed = await alert.show({
        title: 'Delete Ad',
        description: `Are you sure you want to delete "${form.ad_title}"? This action cannot be undone.`,
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
    });

    if (confirmed) {
        form.delete(route('ads.destroy', form.id), {
            onSuccess: () => (model.value = false),
        });
    }
};

watch(
    () => ad,
    (newAd) => {
        if (newAd) {
            existingImages.value = newAd.images || [];
            form.defaults(getDefaultForm(newAd));
            form.reset();
            imagePreviews.value = [];
        }
    },
    { immediate: true }
);
</script>

<template>
    <Dialog v-model:open="model" @open="imagePreviews = []">
        <DialogContent class="!w-8/12 !max-w-6xl !overflow-y-auto px-7 max-h-[90vh]">
            <DialogHeader class="!px-0 !pb-0">
                <DialogTitle>
                    {{ ad ? `Edit Ad: ${ad.ad_title}` : 'Create New Ad' }}
                </DialogTitle>
            </DialogHeader>

            <div class="mt-3 space-y-4 overflow-y-auto pr-2">
                <ValidationErrors />

                <Card>
                    <CardContent class="space-y-4 pt-4">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <TextInput label="Ad Title *" v-model="form.ad_title" :error="form.errors.ad_title"
                                placeholder="Enter ad title" required />

                            <div class="grid grid-cols-2 gap-4">
                                <SelectInput label="Category *" v-model="form.category_id"
                                    :error="form.errors.category_id" placeholder="Select category" required>
                                    <SelectContent>
                                        <SelectItem v-for="category in categories" :key="category.id"
                                            :value="category.id">
                                            {{ category.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </SelectInput>

                                <SelectInput label="Brand *" v-model="form.brand_id" :error="form.errors.brand_id"
                                    placeholder="Select brand" required>
                                    <SelectContent>
                                        <SelectItem v-for="brand in filteredBrands" :key="brand.id" :value="brand.id">
                                            {{ brand.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </SelectInput>
                            </div>

                            <TextInput label="Price *" v-model="form.price" :error="form.errors.price" type="number"
                                placeholder="0.00" required />

                            <TextInput label="Location *" v-model="form.location" :error="form.errors.location"
                                placeholder="Enter location" required />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <TextInput label="Seller Name *" v-model="form.seller_name" :error="form.errors.seller_name"
                                placeholder="Enter seller name" required />

                            <TextInput label="Seller Phone *" v-model="form.seller_phone"
                                :error="form.errors.seller_phone" placeholder="Enter phone number" required />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="text-sm font-medium">Description *</label>
                            <textarea v-model="form.description" rows="4"
                                class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-destructive': form.errors.description }"
                                placeholder="Describe your ad in detail..."></textarea>
                            <p v-if="form.errors.description" class="text-sm text-destructive mt-1">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Images Section -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Images</label>
                            <p class="text-xs text-muted-foreground mb-2">
                                Upload up to 10 images. Drag & drop or click to select.
                            </p>

                            <!-- Image Upload Area -->
                            <div @click="$refs.fileInput?.click()"
                                class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-6 text-center cursor-pointer hover:bg-muted/50 transition-colors">
                                <Icon icon="lucide:upload" class="size-8 mx-auto text-muted-foreground mb-2" />
                                <p class="text-sm font-medium">Drop images here or click to upload</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    PNG, JPG, GIF up to 2MB each
                                </p>
                                <input type="file" ref="fileInput" multiple accept="image/*" @change="onFilesSelected"
                                    class="hidden" />
                            </div>

                            <!-- Image Preview Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mt-4">
                                <!-- Existing Images -->
                                <div v-for="image in existingImages" :key="image.id"
                                    class="relative group rounded-lg overflow-hidden border">
                                    <img :src="`/storage/${image.path}`" class="w-full h-32 object-cover" />
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/50 transition-colors
                                        flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                                        <Button size="xs" variant="secondary" @click="setPrimaryImage(image.id)"
                                            :class="{ 'bg-primary text-white': image.is_primary }">
                                            {{ image.is_primary ? 'Primary' : 'Set Primary' }}
                                        </Button>
                                        <Button size="xs" variant="destructive" @click="removeExistingImage(image.id)">
                                            Remove
                                        </Button>
                                    </div>
                                    <div v-if="image.is_primary"
                                        class="absolute top-2 left-2 bg-primary text-white px-2 py-1 rounded text-xs">
                                        Primary
                                    </div>
                                </div>

                                <!-- New Images -->
                                <div v-for="(preview, index) in imagePreviews" :key="`new-${index}`"
                                    class="relative group rounded-lg overflow-hidden border">
                                    <img :src="preview" class="w-full h-32 object-cover" />
                                    <Button size="xs" variant="destructive"
                                        class="absolute top-2 right-2 opacity-0 group-hover:opacity-100"
                                        @click="removeNewImage(index)">
                                        Remove
                                    </Button>
                                    <div class="absolute top-2 left-2 bg-blue-500 text-white px-2 py-1 rounded text-xs">
                                        New
                                    </div>
                                </div>
                            </div>

                            <div class="text-xs text-muted-foreground mt-2">
                                Total images: {{ existingImages.length + form.images.length }} / 10
                            </div>

                            <p v-if="form.errors.images" class="text-sm text-destructive">
                                {{ form.errors.images }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <DialogFooter>
                    <div class="flex items-center justify-between gap-1 w-full">
                        <AppButton v-if="form.id" label="Delete" icon="lucide:trash-2" variant="danger" size="sm"
                            :processing="form.processing" @click="destroy" />
                        <div class="ml-auto flex items-center gap-2">
                            <AppButton size="sm" variant="outline" label="Cancel" @click="model = false"
                                :disabled="form.processing" />
                            <!-- Use the simpler submit method -->
                            <AppButton size="sm" :processing="form.processing" label="Save" @click="submitSimpler" />
                        </div>
                    </div>
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>