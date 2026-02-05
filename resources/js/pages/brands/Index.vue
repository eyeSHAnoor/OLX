<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import BrandForm from './_partials/BrandForm.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { router } from '@inertiajs/vue3';

defineOptions({ layout: Layout });

// Props and Page setup
const page = usePage<{
    brands: PaginatedData<App.Data.BrandData>;
    categories: App.Data.CategoryData[];
}>();

const brands = computed(() => page.props.brands);
const categories = computed(() => page.props.categories);
const { form, reset, isFiltered } = useSearchFilter(route('brands.index'));
// Columns for data table
const columns = [
    { accessorKey: 'name', header: 'Name', sortable: true, mobileTitle: 'Name' },
    { accessorKey: 'categories', header: 'Categories', sortable: false, mobileTitle: 'Categories' },
    { accessorKey: 'actions', header: '', sortable: false, mobileTitle: 'Actions' },
];

// Modal and breadcrumbs
const { handleShowModal, showModal, selectedItem } = useModal();
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Brands', href: route('brands.index') }
    ]);
});

// DELETE handler as a normal async function
async function handleDeleteBrand(brand: App.Data.BrandData) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: 'Delete Brand',
        description: `Are you sure you want to delete "${brand.name}"?`,
        confirmText: 'Delete',
        cancelText: 'Cancel'
    });

    if (confirmed) {
        router.delete(route('brands.destroy', brand.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: show success message
            }
        });
    }
}
</script>


<template>
    <AppContainer>

        <Head title="Brands" />

        <PageHeading>
            <template #title>Brands</template>
            <template #links>
                <AppButton label="New Brand" icon="radix-icons:plus-circled" class="bg-blue-800"
                    @click="handleShowModal({})" />
            </template>
        </PageHeading>

        <Card class="mt-4">
            <CardContent>
                <AppDataTableNew :columns="columns" :data="brands?.data" search-placeholder="Search brands..."
                    v-model:search="form.filter.global" :pagination-data="brands" v-model:perPage="form.perPage"
                    @resetFilter="reset()" :isFiltered="isFiltered">
                    <template #filters>
                        <!-- You can add filters here if needed -->
                    </template>

                    <template #categories-cell="{ row }">
                        <div v-if="row.original.categories?.length" class="flex flex-wrap gap-1">
                            <span v-for="category in row.original.categories" :key="category.id"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                {{ category.name }}
                            </span>
                        </div>
                        <span v-else class="text-sm text-muted-foreground italic">No categories</span>
                    </template>

                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton icon="lucide:edit" tooltip="Edit"
                                @click="handleShowModal(row.original)" />
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger"
                                @click="handleDeleteBrand(row.original)" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>

        <BrandForm :brand="selectedItem" v-model="showModal" :categories="categories" />
    </AppContainer>
</template>
