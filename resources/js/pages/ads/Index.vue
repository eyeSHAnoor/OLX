<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import AdForm from './_partials/AdForm.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { router } from '@inertiajs/vue3';

defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    ads: PaginatedData<App.Data.AdData>;
    categories: App.Data.CategoryData[];
    brands: App.Data.BrandData[];
}

const page = usePage<PageProps>();
const ads = computed(() => page.props.ads);
const categories = computed(() => page.props.categories);
const brands = computed(() => page.props.brands);

console.log(page.props);

// Use search filter
const { form, reset, isFiltered } = useSearchFilter(route('ads.index'));



// Columns for data table
const columns = [
    {
        accessorKey: 'ad_title',
        header: 'Title',
        sortable: true,
        mobileTitle: 'Title',
        cellClass: 'font-medium'
    },
    {
        accessorKey: 'category',
        header: 'Category',
        sortable: false,
        mobileTitle: 'Category'
    },
    {
        accessorKey: 'brand',
        header: 'Brand',
        sortable: false,
        mobileTitle: 'Brand'
    },
    {
        accessorKey: 'price',
        header: 'Price',
        sortable: true,
        mobileTitle: 'Price'
    },
    {
        accessorKey: 'location',
        header: 'Location',
        sortable: false,
        mobileTitle: 'Location'
    },
    {
        accessorKey: 'seller_name',
        header: 'Seller',
        sortable: false,
        mobileTitle: 'Seller'
    },
    {
        accessorKey: 'images_count',
        header: 'Images',
        sortable: false,
        mobileTitle: 'Images'
    },
    {
        accessorKey: 'created_at',
        header: 'Created',
        sortable: true,
        mobileTitle: 'Created'
    },
    {
        accessorKey: 'actions',
        header: '',
        sortable: false,
        mobileTitle: 'Actions'
    },
];

// Modal and breadcrumbs
const { handleShowModal, showModal, selectedItem } = useModal();
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Ads', href: route('ads.index') }
    ]);
});

// Format price
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(price);
};

// DELETE handler
async function handleDeleteAd(ad: App.Data.AdData) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: 'Delete Ad',
        description: `Are you sure you want to delete "${ad.ad_title}"?`,
        confirmText: 'Delete',
        cancelText: 'Cancel'
    });

    if (confirmed) {
        router.delete(route('ads.destroy', ad.id), {
            preserveScroll: true,
        });
    }
}

// Apply specific filters
const applyCategoryFilter = (categoryId: string | number) => {
    form.filter.category_id = categoryId.toString();
    form.get(route('ads.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const applyBrandFilter = (brandId: string | number) => {
    form.filter.brand_id = brandId.toString();
    form.get(route('ads.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilter = (filterKey: string) => {
    form.filter[filterKey] = '';
    form.get(route('ads.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};
console.log(route('ads.create'));

</script>

<template>
    <AppContainer>

        <Head title="Ads" />

        <PageHeading>
            <template #title>Ads Management</template>
            <template #subtitle>
                Manage your marketplace ads
            </template>
            <template #links>
                <Button as-child size="sm">
                    <Link :href="route('ads.create')">
                    <Icon icon="radix-icons:plus-circled" class="size-4" /> New
                    </Link>
                </Button>
            </template>
        </PageHeading>

        <Card class="mt-4">
            <CardContent>
                <AppDataTableNew :columns="columns" :data="ads?.data"
                    search-placeholder="Search ads by title, description, location..."
                    v-model:search="form.filter.global" :pagination-data="ads" v-model:perPage="form.perPage"
                    @resetFilter="reset()" :isFiltered="isFiltered">
                    <template #filters>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <!-- Price Range Filter -->
                            <div class="flex gap-2">
                                <TextInput v-model="form.filter.min_price" placeholder="Min Price" type="number"
                                    @keyup.enter="form.get(route('ads.index'), { preserveState: true, preserveScroll: true })"
                                    class="flex-1" />
                                <TextInput v-model="form.filter.max_price" placeholder="Max Price" type="number"
                                    @keyup.enter="form.get(route('ads.index'), { preserveState: true, preserveScroll: true })"
                                    class="flex-1" />
                            </div>
                        </div>

                        <!-- Active Filters Display -->
                        <div v-if="isFiltered" class="flex flex-wrap gap-2 mb-4">
                            <div v-if="form.filter.category_id"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                Category: {{categories.find(c => c.id == form.filter.category_id)?.name}}
                                <button @click="clearFilter('category_id')" class="ml-1 hover:text-blue-600">
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>
                            </div>
                            <div v-if="form.filter.brand_id"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                Brand: {{brands.find(b => b.id == form.filter.brand_id)?.name}}
                                <button @click="clearFilter('brand_id')" class="ml-1 hover:text-green-600">
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>
                            </div>
                            <div v-if="form.filter.min_price"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-sm">
                                Min: {{ formatPrice(Number(form.filter.min_price)) }}
                                <button @click="clearFilter('min_price')" class="ml-1 hover:text-purple-600">
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>
                            </div>
                            <div v-if="form.filter.max_price"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-sm">
                                Max: {{ formatPrice(Number(form.filter.max_price)) }}
                                <button @click="clearFilter('max_price')" class="ml-1 hover:text-orange-600">
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>
                            </div>
                        </div>
                    </template>

                    <template #ad_title-cell="{ row }">
                        <div class="flex items-center gap-2">
                            <div class="size-10 flex-shrink-0 rounded overflow-hidden bg-muted">
                                <img v-if="row.original.images?.length"
                                    :src="`/storage/${row.original.images.find(img => img.is_primary)?.path || row.original.images[0].path}`"
                                    :alt="row.original.ad_title" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <Icon icon="lucide:image" class="size-4 text-muted-foreground" />
                                </div>
                            </div>
                            <span class="font-medium truncate">{{ row.original.ad_title }}</span>
                        </div>
                    </template>

                    <template #category-cell="{ row }">
                        <span v-if="row.original.category"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                            {{ row.original.category.name }}
                        </span>
                        <span v-else class="text-sm text-muted-foreground italic">No category</span>
                    </template>

                    <template #brand-cell="{ row }">
                        <span v-if="row.original.brand"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                            {{ row.original.brand.name }}
                        </span>
                        <span v-else class="text-sm text-muted-foreground italic">No brand</span>
                    </template>

                    <template #price-cell="{ row }">
                        <span class="font-semibold text-primary">
                            {{ formatPrice(row.original.price) }}
                        </span>
                    </template>

                    <template #location-cell="{ row }">
                        <div class="flex items-center gap-1">
                            <span class="truncate">{{ row.original.location }}</span>
                        </div>
                    </template>

                    <template #seller_name-cell="{ row }">
                        <div class="flex items-center gap-1">
                            <span>{{ row.original.seller_name }}</span>
                        </div>
                    </template>

                    <template #images_count-cell="{ row }">
                        <div class="flex items-center gap-1">
                            <Icon icon="lucide:image" class="size-3 text-muted-foreground" />
                            <span>{{ row.original.images_count || 0 }}</span>
                        </div>
                    </template>

                    <template #created_at-cell="{ row }">
                        <DateTimeCell :value="row.original.created_at" />
                    </template>

                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton icon="lucide:edit" tooltip="Edit"
                                @click="router.visit(route('ads.edit', row.original))" />
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger"
                                @click="handleDeleteAd(row.original)" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>


        <AdForm :ad="selectedItem" v-model="showModal" :categories="categories" :brands="brands" />
    </AppContainer>
</template>