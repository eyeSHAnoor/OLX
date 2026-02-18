<script setup lang="ts">
import { InertiaPageProps } from '@/types';
import { usePage, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { useDropZone } from '@vueuse/core';
import { Plus, X, Tag } from 'lucide-vue-next';

defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    ad?: App.Data.AdData;
    categories: App.Data.CategoryData[];
    brands: App.Data.BrandData[];
}

const page = usePage<PageProps>();
const ad = computed(() => page.props.ad);
const categories = computed(() => page.props.categories);
const brands = computed(() => page.props.brands);

interface AdFormData {
    id?: string | number;
    category_id: string | number;
    brand_id: string | number;
    ad_title: string;
    description: string;
    price: string | number;
    location: string;
    city: string;
    seller_name: string;
    seller_phone: string;
    search_keywords: string[];
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
    city: item?.city ?? '',
    location: item?.location ?? '',
    seller_name: item?.seller_name ?? '',
    seller_phone: item?.seller_phone ?? '',
    search_keywords: item?.search_keywords ?? [],
    images: [],
    remove_images: [],
});

const form = useForm<AdFormData>({ ...getDefaultForm(ad.value) });
const existingImages = ref<AdImageData[]>(ad.value?.images || []);
const newKeyword = ref('');

const filteredBrands = computed(() => {
    if (!form.category_id) return [];
    return brands.value.filter((brand) =>
        brand.categories?.some((cat: any) => cat.id == form.category_id)
    );
});

watch(() => form.category_id, () => {
    form.brand_id = '';
});

// Search Keywords Handling
const addKeyword = () => {
    if (newKeyword.value.trim()) {
        const keyword = newKeyword.value.trim().toLowerCase();
        if (!form.search_keywords.includes(keyword)) {
            form.search_keywords.push(keyword);
        }
        newKeyword.value = '';
    }
};

const removeKeyword = (index: number) => {
    form.search_keywords.splice(index, 1);
};

const handleKeywordKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' || event.key === ',') {
        event.preventDefault();
        addKeyword();
    }
};

const generateKeywords = () => {
    if (!form.ad_title && !form.description && !form.category_id) return;

    const keywords: string[] = [];

    // Add title words
    if (form.ad_title) {
        const titleWords = form.ad_title.toLowerCase()
            .replace(/[^a-z0-9\s]/gi, '')
            .split(/\s+/)
            .filter(word => word.length > 2);
        keywords.push(...titleWords);
    }

    // Add category and brand if selected
    if (form.category_id) {
        const category = categories.value.find(c => c.id == form.category_id);
        if (category?.name) {
            keywords.push(category.name.toLowerCase());
        }
    }

    if (form.brand_id && filteredBrands.value.length > 0) {
        const brand = filteredBrands.value.find(b => b.id == form.brand_id);
        if (brand?.name) {
            keywords.push(brand.name.toLowerCase());
        }
    }

    // Add city and location
    if (form.city) {
        keywords.push(form.city.toLowerCase());
    }
    if (form.location) {
        const locationWords = form.location.toLowerCase()
            .replace(/[^a-z0-9\s]/gi, '')
            .split(/\s+/)
            .filter(word => word.length > 2);
        keywords.push(...locationWords);
    }

    // Add unique keywords only
    const uniqueKeywords = [...new Set(keywords)];

    // Filter out existing keywords
    const newKeywords = uniqueKeywords.filter(keyword =>
        !form.search_keywords.includes(keyword)
    );

    // Add new keywords (limit to 10 total)
    const availableSlots = 10 - form.search_keywords.length;
    if (availableSlots > 0) {
        form.search_keywords.push(...newKeywords.slice(0, availableSlots));
    }
};

// File upload handling
const imagePreviews = ref<string[]>([]);

const onFilesSelected = (event: Event) => {
    const files = Array.from((event.target as HTMLInputElement).files || []);
    handleFiles(files);
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

        existingImages.value = existingImages.value.map(img => ({
            ...img,
            is_primary: img.id === imageId
        }));
    } catch (error) {
        console.error('Failed to set primary image:', error);
    }
};

const submit = () => {
    const formData = {
        ...form,
        search_keywords: form.search_keywords.filter(keyword => keyword.trim() !== '')
    };

    if (form.id) {
        formData.transform((data: any) => ({
            ...data,
            _method: 'PUT',
        })).post(route('ads.update', form.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('ads.index'));
            },
        });
    } else {
        formData.post(route('ads.store'), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('ads.index'));
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
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('ads.index'));
            },
        });
    }
};

// Watch for ad changes
watch(
    () => ad.value,
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

const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Ads', href: route('ads.index') },
        { label: 'Create & Edit', href: route('ads.create') }
    ]);
});

const primaryImage = computed(() => {
    return existingImages.value.find(img => img.is_primary)
        || existingImages.value[0]
        || null
})
</script>

<template>
    <AppContainer>

        <Head :title="ad ? `Edit: ${ad.ad_title}` : 'Create New Ad'" />

        <!-- Page Header -->
        <div class="my-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ ad ? `Edit: ${ad.ad_title}` : 'Create New Ad' }}
                    </h1>
                    <p class="text-muted-foreground mt-2">
                        {{ ad ?
                            'Update your advertisement details' : 'Fill in the details to create a new advertisement' }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <AppButton label="Cancel" variant="outline" @click="router.visit(route('ads.index'))"
                        :disabled="form.processing" />
                    <AppButton :label="ad ? 'Update Ad' : 'Create Ad'" icon="lucide:check" :processing="form.processing"
                        @click="submit" class="bg-yellow-500 hover:bg-yellow-600" />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Ad Information</CardTitle>
                        <CardDescription>
                            Basic details about your advertisement
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Validation Errors -->
                        <ValidationErrors />

                        <!-- Basic Information Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <TextInput label="Ad Title *" v-model="form.ad_title" :error="form.errors.ad_title"
                                placeholder="Enter ad title" required />

                            <SelectInput label="Category *" v-model="form.category_id" :error="form.errors.category_id"
                                placeholder="Select category" required>
                                <SelectContent>
                                    <SelectItem v-for="category in categories" :key="category.id" :value="category.id">
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

                            <TextInput label="Price *" v-model="form.price" :error="form.errors.price" type="number"
                                placeholder="0.00" required />

                            <TextInput label="Location *" v-model="form.location" :error="form.errors.location"
                                placeholder="Enter location" required />

                            <TextInput label="City *" v-model="form.city" :error="form.errors.city"
                                placeholder="Enter City" required />
                        </div>

                        <!-- Seller Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <TextInput label="Seller Name *" v-model="form.seller_name" :error="form.errors.seller_name"
                                placeholder="Enter seller name" required />

                            <TextInput label="Seller Phone *" v-model="form.seller_phone"
                                :error="form.errors.seller_phone" placeholder="Enter phone number" required />
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="text-sm font-medium block mb-2">Description *</label>
                            <textarea v-model="form.description" rows="4"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                :class="{ 'border-destructive': form.errors.description }"
                                placeholder="Describe your ad in detail..."></textarea>
                            <p v-if="form.errors.description" class="text-sm text-destructive mt-1">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Search Keywords Section -->
                        <div class="space-y-4 pt-4 border-t">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-medium">Search Keywords</h3>
                                    <p class="text-sm text-muted-foreground mt-1">
                                        Add keywords to help users find your ad. Press Enter or comma to add.
                                    </p>
                                </div>
                                <AppButton @click="generateKeywords" variant="outline" size="sm"
                                    :disabled="!form.ad_title && !form.description">
                                    <Tag class="size-4 mr-2" />
                                    Auto-generate
                                </AppButton>
                            </div>

                            <!-- Keyword Input -->
                            <div class="flex gap-2">
                                <div class="flex-1 relative">
                                    <input v-model="newKeyword" @keydown="handleKeywordKeydown"
                                        placeholder="Type keyword and press Enter..."
                                        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                                        :class="{ 'border-destructive': form.errors.search_keywords }" />
                                    <span
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">
                                        {{ form.search_keywords.length }}/20
                                    </span>
                                </div>
                                <AppButton @click="addKeyword" variant="outline"
                                    :disabled="!newKeyword.trim() || form.search_keywords.length >= 20">
                                    <Plus class="size-4" />
                                </AppButton>
                            </div>

                            <!-- Keywords List -->
                            <div v-if="form.search_keywords.length > 0" class="space-y-2">
                                <div class="flex flex-wrap gap-2">
                                    <div v-for="(keyword, index) in form.search_keywords" :key="index"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary/10 text-primary rounded-full text-sm">
                                        <span>{{ keyword }}</span>
                                        <button @click="removeKeyword(index)"
                                            class="ml-1 text-primary/70 hover:text-primary transition-colors">
                                            <X class="size-3" />
                                        </button>
                                    </div>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    Click the × icon to remove a keyword
                                </p>
                            </div>
                            <div v-else class="text-center py-6 border-2 border-dashed rounded-md">
                                <Tag class="size-8 text-muted-foreground mx-auto mb-2" />
                                <p class="text-sm text-muted-foreground">No keywords added yet</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Add keywords manually or use "Auto-generate" to create from your ad content
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Images Section -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle>Images</CardTitle>
                        <CardDescription>
                            Upload up to 10 images of your product
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Image Upload Area -->
                        <div @click="$refs.fileInput?.click()"
                            class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-8 text-center cursor-pointer hover:bg-muted/50 transition-colors">
                            <div class="flex flex-col items-center justify-center">
                                <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                                    <Icon icon="lucide:upload" class="size-6 text-primary" />
                                </div>
                                <p class="text-sm font-medium">Drop images here or click to upload</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    PNG, JPG, GIF up to 2MB each
                                </p>
                            </div>
                            <input type="file" ref="fileInput" multiple accept="image/*" @change="onFilesSelected"
                                class="hidden" />
                        </div>

                        <!-- Image Preview Grid -->
                        <div v-if="existingImages.length > 0 || imagePreviews.length > 0" class="space-y-4">
                            <h3 class="text-sm font-medium">Uploaded Images</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                <!-- Existing Images -->
                                <div v-for="image in existingImages" :key="image.id"
                                    class="relative group rounded-lg overflow-hidden border">
                                    <img :src="`/storage/${image.path}`" class="w-full h-32 object-cover"
                                        :alt="`Product image ${image.id}`" />
                                    <div
                                        class="absolute inset-0 bg-black/0 group-hover:bg-black/50 transition-colors flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                                        <button @click="setPrimaryImage(image.id)" :class="{
                                            'bg-primary text-white': image.is_primary,
                                            'bg-white/90 text-gray-800 hover:bg-white': !image.is_primary
                                        }" class="text-xs px-2 py-1 rounded transition-colors">
                                            {{ image.is_primary ? 'Primary' : 'Set Primary' }}
                                        </button>
                                        <button @click="removeExistingImage(image.id)"
                                            class="text-xs bg-red-500 text-white hover:bg-red-600 px-2 py-1 rounded transition-colors">
                                            Remove
                                        </button>
                                    </div>
                                    <div v-if="image.is_primary"
                                        class="absolute top-2 left-2 bg-primary text-white px-2 py-1 rounded text-xs font-medium">
                                        Primary
                                    </div>
                                </div>

                                <!-- New Images -->
                                <div v-for="(preview, index) in imagePreviews" :key="`new-${index}`"
                                    class="relative group rounded-lg overflow-hidden border">
                                    <img :src="preview" class="w-full h-32 object-cover" :alt="`New image ${index}`" />
                                    <button @click="removeNewImage(index)"
                                        class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                        <Icon icon="lucide:x" class="size-3" />
                                    </button>
                                    <div
                                        class="absolute top-2 left-2 bg-blue-500 text-white px-2 py-1 rounded text-xs font-medium">
                                        New
                                    </div>
                                </div>
                            </div>

                            <!-- Image Count -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">
                                    Total images: {{ existingImages.length + form.images.length }} / 10
                                </span>
                                <span v-if="form.errors.images" class="text-destructive">
                                    {{ form.errors.images }}
                                </span>
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
                            <AppButton :label="ad ? 'Update Ad' : 'Publish Ad'" icon="lucide:check"
                                :processing="form.processing" @click="submit"
                                class="bg-yellow-500 hover:bg-yellow-600 w-full justify-center" />

                            <AppButton label="Cancel" variant="outline" class="w-full justify-center"
                                @click="router.visit(route('ads.index'))" :disabled="form.processing" />

                            <AppButton v-if="ad" label="Delete Ad" variant="danger" icon="lucide:trash-2"
                                class="w-full justify-center" @click="destroy" :disabled="form.processing" />
                        </div>

                        <!-- Keywords Preview -->
                        <div v-if="form.search_keywords.length > 0" class="pt-4 border-t">
                            <h4 class="text-sm font-medium mb-2">Search Keywords Preview</h4>
                            <div class="flex flex-wrap gap-1.5">
                                <span v-for="(keyword, index) in form.search_keywords.slice(0, 8)" :key="index"
                                    class="px-2 py-1 bg-muted text-muted-foreground rounded-full text-xs">
                                    {{ keyword }}
                                </span>
                                <span v-if="form.search_keywords.length > 8"
                                    class="px-2 py-1 bg-muted text-muted-foreground rounded-full text-xs">
                                    +{{ form.search_keywords.length - 8 }} more
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Ad Preview Card -->
                <Card>
                    <CardHeader>
                        <CardTitle>Preview</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <!-- Main Image -->
                            <div class="aspect-square rounded-lg bg-muted overflow-hidden">
                                <div v-if="primaryImage || imagePreviews.length > 0" class="w-full h-full">
                                    <!-- Existing primary image -->
                                    <img v-if="primaryImage" :src="`/storage/${primaryImage.path}`"
                                        class="w-full h-full object-cover" alt="Primary product image" />
                                    <img v-else-if="imagePreviews.length > 0" :src="imagePreviews[0]"
                                        class="w-full h-full object-cover" alt="New product image" />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <Icon icon="lucide:image" class="size-12 text-muted-foreground" />
                                    </div>
                                </div>
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <Icon icon="lucide:image" class="size-12 text-muted-foreground" />
                                </div>
                            </div>

                            <!-- Ad Details -->
                            <div class="space-y-2">
                                <h3 class="font-semibold line-clamp-1">{{ form.ad_title || 'Ad Title' }}</h3>
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-primary">
                                        {{ form.price ? `$${Number(form.price).toLocaleString()}` : '$0.00' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 text-sm text-muted-foreground">
                                    <Icon icon="lucide:map-pin" class="size-3" />
                                    <span class="truncate">{{ form.location || 'Location' }}</span>
                                </div>
                                <div v-if="form.search_keywords.length > 0" class="pt-2">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="(keyword, index) in form.search_keywords.slice(0, 3)" :key="index"
                                            class="px-2 py-0.5 bg-primary/5 text-primary rounded text-xs">
                                            {{ keyword }}
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
                        <CardTitle class="text-sm">Tips for better ads</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm space-y-2 text-muted-foreground">
                        <div class="flex items-start gap-2">
                            <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                            <span>Add relevant keywords for better search results</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                            <span>Use clear, high-quality images</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                            <span>Write detailed descriptions</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                            <span>Set competitive pricing</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <Icon icon="lucide:check-circle" class="size-4 text-green-500 mt-0.5" />
                            <span>Provide accurate contact information</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppContainer>
</template>

<style scoped>
input:focus,
textarea:focus {
    outline: none;
    ring: 2px;
}
</style>