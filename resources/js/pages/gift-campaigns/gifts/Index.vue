<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { router } from '@inertiajs/vue3';

defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    gifts: PaginatedData<App.Data.GiftData>;
}

const page = usePage<PageProps>();
const gifts = computed(() => page.props.gifts);

// Use search filter
const { form, reset, isFiltered } = useSearchFilter(route('gifts.index'));

// Columns for data table
const columns = [
    {
        accessorKey: 'name',
        header: 'Gift Name',
        sortable: true,
        mobileTitle: 'Name',
        cellClass: 'font-medium'
    },
    {
        accessorKey: 'image',
        header: 'Image',
        sortable: false,
        mobileTitle: 'Image'
    },
    {
        accessorKey: 'description',
        header: 'Description',
        sortable: false,
        mobileTitle: 'Description'
    },
    {
        accessorKey: 'quantity',
        header: 'Quantity',
        sortable: true,
        mobileTitle: 'Qty'
    },
    {
        accessorKey: 'is_active',
        header: 'Status',
        sortable: true,
        mobileTitle: 'Status'
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

// Breadcrumbs
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Gifts', href: route('gifts.index') }
    ]);
});

// DELETE handler
async function handleDeleteGift(gift: App.Data.GiftData) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: 'Delete Gift',
        description: `Are you sure you want to delete "${gift.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel'
    });

    if (confirmed) {
        router.delete(route('gifts.destroy', gift.id), {
            preserveScroll: true,
        });
    }
}

// Toggle status handler
async function handleToggleStatus(gift: App.Data.GiftData) {
    const alert = useAlertDialog();
    const action = gift.is_active ? 'deactivate' : 'activate';
    const confirmed = await alert.show({
        title: `${action.charAt(0).toUpperCase() + action.slice(1)} Gift`,
        description: `Are you sure you want to ${action} "${gift.name}"?`,
        confirmText: 'Yes',
        cancelText: 'Cancel'
    });

    if (confirmed) {
        router.patch(route('gifts.toggle-status', gift.id), {}, {
            preserveScroll: true,
        });
    }
}

const clearFilter = (filterKey: string) => {
    form.filter[filterKey] = '';
    form.get(route('gifts.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

// Format quantity with color coding
const getQuantityClass = (quantity: number) => {
    if (quantity === 0) return 'text-red-600 font-bold';
    if (quantity < 10) return 'text-orange-600 font-semibold';
    return 'text-green-600 font-semibold';
};
</script>

<template>
    <AppContainer>

        <Head title="Gifts" />

        <PageHeading>
            <template #title>Gifts Management</template>
            <template #subtitle>
                Manage your gift inventory for campaigns
            </template>
            <template #links>
                <Button as-child size="sm">
                    <Link :href="route('gifts.create')">
                    <Icon icon="radix-icons:plus-circled" class="size-4" /> Add Gift
                    </Link>
                </Button>
            </template>
        </PageHeading>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Gifts</p>
                            <p class="text-2xl font-bold">{{ gifts?.total || 0 }}</p>
                        </div>
                        <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center">
                            <Icon icon="lucide:gift" class="size-6 text-primary" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Active Gifts</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{gifts?.data?.filter(g => g.is_active).length || 0}}
                            </p>
                        </div>
                        <div class="size-12 rounded-full bg-green-100 flex items-center justify-center">
                            <Icon icon="lucide:check-circle" class="size-6 text-green-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Low Stock</p>
                            <p class="text-2xl font-bold text-orange-600">
                                {{gifts?.data?.filter(g => g.quantity > 0 && g.quantity < 10).length || 0}} </p>
                        </div>
                        <div class="size-12 rounded-full bg-orange-100 flex items-center justify-center">
                            <Icon icon="lucide:alert-triangle" class="size-6 text-orange-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Out of Stock</p>
                            <p class="text-2xl font-bold text-red-600">
                                {{gifts?.data?.filter(g => g.quantity === 0).length || 0}}
                            </p>
                        </div>
                        <div class="size-12 rounded-full bg-red-100 flex items-center justify-center">
                            <Icon icon="lucide:x-circle" class="size-6 text-red-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="mt-4">
            <CardContent>
                <AppDataTableNew :columns="columns" :data="gifts?.data"
                    search-placeholder="Search gifts by name or description..." v-model:search="form.filter.global"
                    :pagination-data="gifts" v-model:perPage="form.perPage" @resetFilter="reset()"
                    :isFiltered="isFiltered">

                    <template #filters>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Status Filter -->
                            <SelectInput v-model="form.filter.is_active" placeholder="Filter by status"
                                @update:model-value="form.get(route('gifts.index'), { preserveState: true, preserveScroll: true })">
                                <SelectContent>
                                    <SelectItem value="">All Status</SelectItem>
                                    <SelectItem value="1">Active</SelectItem>
                                    <SelectItem value="0">Inactive</SelectItem>
                                </SelectContent>
                            </SelectInput>
                        </div>

                        <!-- Active Filters Display -->
                        <div v-if="isFiltered" class="flex flex-wrap gap-2 mb-4">
                            <div v-if="form.filter.is_active === '1'"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                Status: Active
                                <button @click="clearFilter('is_active')" class="ml-1 hover:text-green-600">
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>
                            </div>
                            <div v-if="form.filter.is_active === '0'"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm">
                                Status: Inactive
                                <button @click="clearFilter('is_active')" class="ml-1 hover:text-red-600">
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>
                            </div>
                        </div>
                    </template>

                    <template #name-cell="{ row }">
                        <div class="flex items-center gap-3">
                            <div class="size-10 flex-shrink-0 rounded-lg overflow-hidden bg-muted">
                                <img v-if="row.original.image" :src="`/storage/${row.original.image}`"
                                    :alt="row.original.name" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <Icon icon="lucide:gift" class="size-5 text-muted-foreground" />
                                </div>
                            </div>
                            <div>
                                <span class="font-medium block">{{ row.original.name }}</span>
                            </div>
                        </div>
                    </template>

                    <template #image-cell="{ row }">
                        <div class="flex items-center">
                            <span v-if="row.original.image" class="text-xs text-green-600 flex items-center gap-1">
                                <Icon icon="lucide:check-circle" class="size-3" />
                                Uploaded
                            </span>
                            <span v-else class="text-xs text-muted-foreground flex items-center gap-1">
                                <Icon icon="lucide:image-off" class="size-3" />
                                No image
                            </span>
                        </div>
                    </template>

                    <template #description-cell="{ row }">
                        <p class="text-sm text-muted-foreground truncate max-w-[200px]">
                            {{ row.original.description || 'No description' }}
                        </p>
                    </template>

                    <template #quantity-cell="{ row }">
                        <div class="flex items-center gap-2">
                            <span :class="getQuantityClass(row.original.quantity)">
                                {{ row.original.quantity }}
                            </span>
                            <span v-if="row.original.quantity === 0"
                                class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                Out of stock
                            </span>
                            <span v-else-if="row.original.quantity < 10"
                                class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">
                                Low stock
                            </span>
                        </div>
                    </template>

                    <template #is_active-cell="{ row }">
                        <button @click="handleToggleStatus(row.original)"
                            :class="row.original.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full cursor-pointer hover:opacity-80 transition-opacity">
                            <span :class="row.original.is_active ? 'bg-green-500' : 'bg-red-500'"
                                class="size-2 rounded-full mr-1.5">
                            </span>
                            {{ row.original.is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </template>

                    <template #created_at-cell="{ row }">
                        <DateTimeCell :value="row.original.created_at" />
                    </template>

                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton icon="lucide:edit" tooltip="Edit Gift"
                                @click="router.visit(route('gifts.edit', row.original))" />
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete Gift" variant="danger"
                                @click="handleDeleteGift(row.original)" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>
    </AppContainer>
</template>