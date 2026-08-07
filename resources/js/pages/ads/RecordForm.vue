<script setup lang="ts">
import { InertiaPageProps } from '@/types';
import { usePage, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { useDropZone } from '@vueuse/core';
import { Plus, X, Tag } from 'lucide-vue-next';
import CardContent from '@/components/ui/card/CardContent.vue';
import axios from 'axios';

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
const features = computed(() => page.props.features);

console.log('Ad data:', ad.value);

interface AdFormData {
  id?: string | number;
  category_id: string | number;
  brand_id: string | number;
  brand_model_id?: string | number;
  ad_title: string;
  description: string;
  price: string | number;
  discount?: string | number;
  location: string;
  city: string;
  region?: string;
  seller_name: string;
  seller_phone: string;
  search_keywords: string[];
  images: File[];
  remove_images: (string | number)[];
  features: {
    feature_id: number | '';
    feature_value_id?: number | '';
    custom_value?: string;
  }[];
  attributes?: Record<number, string | number>;
}

interface AdImageData {
  id: string | number;
  path: string;
  is_primary: boolean;
}

// ---------- Discount related reactive state ----------
const discountType = ref<'percentage' | 'amount'>('percentage');
const discountValue = ref<number>(0);

const discountedPrice = computed(() => {
  const originalPrice = Number(form.price);
  if (isNaN(originalPrice) || originalPrice <= 0) return 0;
  if (discountValue.value < 0) return originalPrice;

  if (discountType.value === 'percentage') {
    if (discountValue.value > 100) return 0;
    const discountAmount = originalPrice * (discountValue.value / 100);
    return Math.max(0, originalPrice - discountAmount);
  } else {
    // Fixed amount
    if (discountValue.value > originalPrice) return 0;
    return Math.max(0, originalPrice - discountValue.value);
  }
});

const discountPercentageDisplay = computed(() => {
  const original = Number(form.price);
  const final = discountedPrice.value;
  if (original && original > 0 && final < original) {
    return ((1 - final / original) * 100).toFixed(0);
  }
  return 0;
});

// Initialise discount fields from existing ad (edit mode)
const initDiscountFromAd = () => {
  if (!ad.value) return;
  const original = Number(ad.value.price);
  const discounted = Number(ad.value.discount);
  if (isNaN(original) || isNaN(discounted) || original <= 0 || discounted >= original) {
    // No discount or invalid data – reset
    discountValue.value = 0;
    discountType.value = 'percentage';
    return;
  }
  const discountAmount = original - discounted;
  // Prefer percentage view if it gives an integer, else amount
  const percent = (discountAmount / original) * 100;
  if (percent === Math.floor(percent)) {
    discountType.value = 'percentage';
    discountValue.value = Math.round(percent);
  } else {
    discountType.value = 'amount';
    discountValue.value = Math.round(discountAmount * 100) / 100;
  }
};

// ---------- Discount related reactive state end ----------

// Dynamic attributes and models
const categoryAttributes = ref<any[]>([]);
const selectedAttributeValues = ref<Record<number, string | number>>({});
const brandModels = ref<any[]>([]);
const isLoadingAttributes = ref(false);
const isLoadingModels = ref(false);
const isInitialLoad = ref(true);
import citiesData from '@/data/cities.json';

const cityOptions = computed(() => {
  return citiesData
    .filter((c: any) => c.country === 'PK')
    .map((c: any) => ({ id: c.name, name: c.name }))
    .sort((a, b) => a.name.localeCompare(b.name));
});

const regions = ref<any[]>([]);
const isLoadingRegions = ref(false);

const regionOptions = computed(() => {
  return regions.value.map((r: any) => ({ id: r.id, name: r.name }));
});

const getDefaultForm = (item: App.Data.AdData | undefined): AdFormData => ({
  id: item?.id ?? '',
  category_id: item?.category_id ?? '',
  brand_id: item?.brand_id ?? '',
  model_id: item?.brand_model_id ?? '',
  ad_title: item?.ad_title ?? '',
  description: item?.description ?? '',
  price: item?.price ?? '',
  discount: item?.discount ?? '', // we'll override later on submit
  city: item?.city ?? '',
  region: item?.region ?? '',
  location: item?.location ?? '',
  seller_name: item?.seller_name ?? '',
  seller_phone: item?.seller_phone ?? '',
  search_keywords: item?.search_keywords ?? [],
  images: [],
  remove_images: [],
  features: item?.features?.map((f: any) => ({
    feature_id: f.id,
    feature_value_id: f.pivot?.feature_value_id ?? '',
    custom_value: f.pivot?.custom_value ?? '',
  })) ?? [],
  attributes: item?.attributes?.reduce((acc: any, attr: any) => {
    const attributeId = attr.category_attribute_id || attr.attribute?.id;
    if (attributeId) {
      acc[attributeId] = attr.value;
    }
    return acc;
  }, {}) ?? {},
});

const addFeatureRow = () => {
  form.features.push({
    feature_id: '',
    feature_value_id: '',
    custom_value: '',
  });
};

const removeFeatureRow = (index: number) => {
  form.features.splice(index, 1);
};

const form = useForm<AdFormData>({ ...getDefaultForm(ad.value) });
const existingImages = ref<AdImageData[]>(ad.value?.images || []);
const newKeyword = ref('');

const fetchRegions = async (cityName: string) => {
  if (!cityName) {
    regions.value = [];
    return;
  }
  isLoadingRegions.value = true;
  try {
    const response = await axios.get(`/regions/${encodeURIComponent(cityName)}`);
    regions.value = response.data.regions || [];
  } catch (error) {
    console.error('Failed to fetch regions:', error);
    regions.value = [];
  } finally {
    isLoadingRegions.value = false;
  }
};

watch(() => form.city, (newCity, oldCity) => {
  if (newCity !== oldCity) {
    form.region = '';
    if (newCity) {
      fetchRegions(newCity);
    } else {
      regions.value = [];
    }
  }
});

const filteredBrands = computed(() => {
  if (!form.category_id) return [];
  return brands.value.filter((brand) =>
    brand.categories?.some((cat: any) => cat.id == form.category_id)
  );
});

const fetchAttributes = async (categoryId: string | number) => {
  isLoadingAttributes.value = true;
  try {
    const response = await axios.get(`/categories/${categoryId}/attributes`);
    if (response.data.success) {
      categoryAttributes.value = response.data.attributes;
    } else {
      categoryAttributes.value = response.data.attributes || [];
    }

    if (ad.value?.attributes && ad.value.attributes.length > 0) {
      ad.value.attributes.forEach((attr: any) => {
        const attributeId = attr.category_attribute_id || attr.attribute?.id;
        if (attributeId && attr.value) {
          selectedAttributeValues.value[attributeId] = attr.value;
        }
      });
    }
  } catch (error) {
    console.error('Failed to load attributes:', error);
    categoryAttributes.value = [];
  } finally {
    isLoadingAttributes.value = false;
  }
};

const fetchModels = async (brandId: string | number) => {
  isLoadingModels.value = true;
  try {
    const response = await axios.get(`/brands/${brandId}/models`);
    if (response.data.success) {
      brandModels.value = response.data.models;
    } else {
      brandModels.value = response.data.models || [];
    }
  } catch (error) {
    console.error('Failed to load models:', error);
    brandModels.value = [];
  } finally {
    isLoadingModels.value = false;
  }
};

watch(() => form.category_id, async (newCategoryId, oldCategoryId) => {
  if (isInitialLoad.value && ad.value?.category_id == newCategoryId) {
    return;
  }
  if (newCategoryId && newCategoryId !== oldCategoryId) {
    if (!isInitialLoad.value) {
      form.brand_id = '';
      form.model_id = '';
    }
    brandModels.value = [];
    selectedAttributeValues.value = {};
    await fetchAttributes(newCategoryId);
  } else if (!newCategoryId) {
    categoryAttributes.value = [];
    brandModels.value = [];
  }
}, { immediate: true });

watch(() => form.brand_id, async (newBrandId, oldBrandId) => {
  if (isInitialLoad.value && ad.value?.brand_id == newBrandId) {
    return;
  }
  if (newBrandId && newBrandId !== oldBrandId) {
    await fetchModels(newBrandId);
    if (!isInitialLoad.value) {
      form.model_id = '';
    }
  } else if (!newBrandId) {
    brandModels.value = [];
    if (!isInitialLoad.value) {
      form.model_id = '';
    }
  }
}, { immediate: true });

onMounted(async () => {
  if (ad.value) {
    form.category_id = ad.value.category_id;
    form.brand_id = ad.value.brand_id;
    form.model_id = ad.value.brand_model_id || '';

    if (ad.value.category_id) {
      await fetchAttributes(ad.value.category_id);
    }
    if (ad.value?.city) {
      await fetchRegions(ad.value.city);
      if (ad.value.region) {
        form.region = ad.value.region;
      }
    }
    if (ad.value.brand_id) {
      await fetchModels(ad.value.brand_id);
      if (ad.value.brand_model_id && brandModels.value.length > 0) {
        const modelExists = brandModels.value.some(model => model.id == ad.value.brand_model_id);
        if (modelExists) {
          form.model_id = ad.value.brand_model_id;
        }
      }
    }

    // Initialise discount fields from existing ad
    initDiscountFromAd();
  }
  setTimeout(() => {
    isInitialLoad.value = false;
  }, 500);
});

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
  if (form.ad_title) {
    const titleWords = form.ad_title.toLowerCase()
      .replace(/[^a-z0-9\s]/gi, '')
      .split(/\s+/)
      .filter(word => word.length > 2);
    keywords.push(...titleWords);
  }
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

  const uniqueKeywords = [...new Set(keywords)];
  const newKeywords = uniqueKeywords.filter(keyword =>
    !form.search_keywords.includes(keyword)
  );

  const availableSlots = 10 - form.search_keywords.length;
  if (availableSlots > 0) {
    form.search_keywords.push(...newKeywords.slice(0, availableSlots));
  }
};

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
  // Attach the computed discounted price to the form data
  form.discount = discountedPrice.value;

  const formData = {
    ...form,
    attributes: selectedAttributeValues.value,
    search_keywords: form.search_keywords.filter(keyword => keyword.trim() !== '')
  };

  if (form.id) {
    formData.transform((data: any) => ({
      ...data,
      _method: 'PUT',
    })).post(route('ads.update', form.id), {
      preserveScroll: true,
    });
  } else {
    formData.post(route('ads.store'), {
      preserveScroll: true
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
});
</script>

<template>
  <AppContainer>

    <Head :title="ad ? `Edit: ${ad.ad_title}` : 'Create New Ad'" />

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
            @click="submit" class="bg-brand-orange hover:bg-brand-orange/80" />
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
            <ValidationErrors />

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

              <SelectInput v-if="brandModels.length > 0" label="Model" v-model="form.model_id"
                :error="form.errors.model_id" placeholder="Select model" :disabled="isLoadingModels">
                <SelectContent>
                  <SelectItem v-for="model in brandModels" :key="model.id" :value="model.id">
                    {{ model.name }}
                  </SelectItem>
                </SelectContent>
              </SelectInput>

              <TextInput label="Price *" v-model="form.price" :error="form.errors.price" type="number"
                placeholder="0.00" required />

              <!-- Discount Section -->
              <div class="space-y-3 rounded-lg border p-4 col-span-2">
                <h4 class="text-sm font-medium">Discount (optional)</h4>
                <div class="flex items-center gap-4">
                  <label class="flex items-center gap-2">
                    <input type="radio" v-model="discountType" value="percentage" class="radio" />
                    Percentage
                  </label>
                  <label class="flex items-center gap-2">
                    <input type="radio" v-model="discountType" value="amount" class="radio" />
                    Amount
                  </label>
                </div>
                <div class="flex items-center gap-2">
                  <input v-model.number="discountValue" type="number" class="w-full px-3 py-2 border rounded-md" min="0"
                    :max="discountType === 'percentage' ? 100 : form.price" />
                  <span v-if="discountType === 'percentage'" class="text-sm">%</span>
                  <span v-else class="text-sm">off</span>
                </div>
                <div class="text-sm text-muted-foreground">
                  Discounted Price:
                  <span class="font-semibold">{{ discountedPrice.toFixed(2) }}Pkr</span>
                  <span v-if="discountValue > 0 && discountedPrice < form.price" class="ml-2">
                    ({{ discountPercentageDisplay }}% off)
                  </span>
                </div>
              </div>
              <!-- End Discount Section -->

              <TextInput label="Location *" v-model="form.location" :error="form.errors.location"
                placeholder="Enter location" required />

              <div>
                <label class="text-sm font-medium block mb-2">City *</label>
                <SearchableSelectInput v-model="form.city" :items="cityOptions" key-by="id"
                  :searchable-fields="['name']" placeholder="Select City" :error="form.errors.city">
                  <template #item="{ item }">
                    <div class="flex w-full cursor-pointer items-center px-3 py-2 text-left text-sm hover:bg-gray-100">
                      <span>{{ item.name }}</span>
                    </div>
                  </template>
                  <template #selected="{ item }">
                    {{ item?.name ?? 'Select City' }}
                  </template>
                </SearchableSelectInput>
                <p v-if="form.errors.city" class="text-sm text-destructive mt-1">{{ form.errors.city }}
                </p>
              </div>
              <div v-if="form.city">
                <label class="text-sm font-medium block mb-2">Region / Area <span
                    class="text-muted-foreground text-xs font-normal">(optional)</span></label>
                <SearchableSelectInput v-model="form.region" :items="regionOptions" key-by="name"
                  :searchable-fields="['name']" placeholder="Select Region" :disabled="isLoadingRegions"
                  :error="form.errors.region">
                  <template #item="{ item }">
                    <div class="flex w-full cursor-pointer items-center px-3 py-2 text-left text-sm hover:bg-gray-100">
                      <span>{{ item.name }}</span>
                    </div>
                  </template>
                  <template #selected="{ item }">
                    <span v-if="isLoadingRegions">Loading regions...</span>
                    <span v-else>{{ item?.name ?? 'All areas' }}</span>
                  </template>
                </SearchableSelectInput>
                <p v-if="form.errors.region" class="text-sm text-destructive mt-1">{{ form.errors.region
                }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <TextInput label="Seller Name *" v-model="form.seller_name" :error="form.errors.seller_name"
                placeholder="Enter seller name" required />

              <TextInput label="Seller Phone *" v-model="form.seller_phone" :error="form.errors.seller_phone"
                placeholder="Enter phone number" required />
            </div>

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

              <div class="flex gap-2">
                <div class="flex-1 relative">
                  <input v-model="newKeyword" @keydown="handleKeywordKeydown"
                    placeholder="Type keyword and press Enter..."
                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                    :class="{ 'border-destructive': form.errors.search_keywords }" />
                  <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">
                    {{ form.search_keywords.length }}/20
                  </span>
                </div>
                <AppButton @click="addKeyword" variant="outline"
                  :disabled="!newKeyword.trim() || form.search_keywords.length >= 20">
                  <Plus class="size-4" />
                </AppButton>
              </div>

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
              </div>
            </div>
          </CardContent>
        </Card>

        <Card v-if="categoryAttributes.length > 0" class="mt-6">
          <CardHeader>
            <CardTitle>Product Specifications</CardTitle>
            <CardDescription>
              Fill in the specifications for your product
              <span v-if="isLoadingAttributes" class="ml-2 text-xs">Loading...</span>
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <table class="w-full border rounded-md">
                <thead class="bg-muted">
                  <tr>
                    <th class="p-3 text-left font-medium">Attribute</th>
                    <th class="p-3 text-left font-medium">Value</th>
                    <th class="p-3 text-left font-medium w-24">Required</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="attr in categoryAttributes" :key="attr.id" class="border-t">
                    <td class="p-3 align-top">
                      <div class="font-medium">{{ attr.name }}</div>
                    </td>
                    <td class="p-3">
                      <select v-if="attr.type === 'select'" v-model="selectedAttributeValues[attr.id]"
                        class="w-full px-3 py-2 border rounded-md">
                        <option value="">Select {{ attr.name }}</option>
                        <option v-for="option in attr.options" :key="option.id" :value="option.id">
                          {{ option.value }}
                        </option>
                      </select>
                      <input v-else v-model="selectedAttributeValues[attr.id]" type="text"
                        :placeholder="`Enter ${attr.name.toLowerCase()}`" class="w-full px-3 py-2 border rounded-md" />
                    </td>
                    <td class="p-3 text-center align-top">
                      <span v-if="attr.is_required" class="text-red-500 font-medium">Yes</span>
                      <span v-else class="text-muted-foreground">No</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>

        <Card class="mt-6">
          <CardHeader>
            <CardTitle>Ad Features</CardTitle>
            <CardDescription>
              Add custom features to your ad
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium">Features</h3>
                <AppDataTableActionButton icon="lucide:plus" tooltip="Add Feature" @click="addFeatureRow" />
              </div>

              <table class="w-full border rounded-md overflow-hidden">
                <thead class="bg-muted">
                  <tr class="text-sm">
                    <th class="p-2 text-left">Feature</th>
                    <th class="p-2 text-left">Value</th>
                    <th class="p-2 w-12"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, index) in form.features" :key="index" class="border-t">
                    <td class="p-2">
                      <select v-model="row.feature_id" class="w-full border rounded px-2 py-1">
                        <option value="">Select</option>
                        <option v-for="f in features" :key="f.id" :value="f.id">
                          {{ f.name }}
                        </option>
                      </select>
                    </td>
                    <td class="p-2">
                      <template v-if="row.feature_id">
                        <select v-model="row.feature_value_id" class="w-full border rounded px-2 py-1">
                          <option value="">Custom</option>
                          <option v-for="v in features.find(f => f.id === row.feature_id)?.values || []" :key="v.id"
                            :value="v.id">
                            {{ v.value }}
                          </option>
                        </select>
                        <input v-if="!row.feature_value_id" v-model="row.custom_value" placeholder="Enter value"
                          class="mt-1 w-full border rounded px-2 py-1" />
                      </template>
                    </td>
                    <td class="p-2">
                      <button @click="removeFeatureRow(index)">
                        <X class="size-4 text-red-500" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>

        <Card class="mt-6">
          <CardHeader>
            <CardTitle>Images</CardTitle>
            <CardDescription>
              Upload up to 10 images of your product
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6">
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
              <input type="file" ref="fileInput" multiple accept="image/*" @change="onFilesSelected" class="hidden" />
            </div>

            <div v-if="existingImages.length > 0 || imagePreviews.length > 0" class="space-y-4">
              <h3 class="text-sm font-medium">Uploaded Images</h3>
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
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

                <div v-for="(preview, index) in imagePreviews" :key="`new-${index}`"
                  class="relative group rounded-lg overflow-hidden border">
                  <img :src="preview" class="w-full h-32 object-cover" :alt="`New image ${index}`" />
                  <button @click="removeNewImage(index)"
                    class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                    <Icon icon="lucide:x" class="size-3" />
                  </button>
                  <div class="absolute top-2 left-2 bg-blue-500 text-white px-2 py-1 rounded text-xs font-medium">
                    New
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <Card>
          <CardHeader>
            <CardTitle>Actions</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex flex-col gap-2">
              <AppButton :label="ad ? 'Update Ad' : 'Publish Ad'" icon="lucide:check" :processing="form.processing"
                @click="submit" class="bg-brand-orange hover:bg-brand-orange/80 w-full justify-center" />

              <AppButton label="Cancel" variant="outline" class="w-full justify-center"
                @click="router.visit(route('ads.index'))" :disabled="form.processing" />

              <AppButton v-if="ad" label="Delete Ad" variant="danger" icon="lucide:trash-2"
                class="w-full justify-center" @click="destroy" :disabled="form.processing" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Preview</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div class="aspect-square rounded-lg bg-muted overflow-hidden">
                <div v-if="primaryImage || imagePreviews.length > 0" class="w-full h-full">
                  <img v-if="primaryImage" :src="`/storage/${primaryImage.path}`" class="w-full h-full object-cover"
                    alt="Primary product image" />
                  <img v-else-if="imagePreviews.length > 0" :src="imagePreviews[0]" class="w-full h-full object-cover"
                    alt="New product image" />
                </div>
              </div>
              <div class="space-y-2">
                <h3 class="font-semibold line-clamp-1">{{ form.ad_title || 'Ad Title' }}</h3>
                <div class="flex items-center justify-between">
                  <span class="text-lg font-bold text-primary">
                    {{ form.price ? `$${Number(form.price).toLocaleString()}` : '$0.00' }}
                  </span>
                </div>
              </div>
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