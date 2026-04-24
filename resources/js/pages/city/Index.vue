<script setup lang="ts">
import { PaginatedData } from '@/types';
import { usePage, router, Head, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { useBreadcrumb } from '@/composables/useBreadcrumb';
import useSearchFilter from '@/composables/useSearchFilter';
import { Icon } from '@iconify/vue';
import Card from '@/components/ui/card/Card.vue';
import CardContent from '@/components/ui/card/CardContent.vue';
import TextInput from '@/components/TextInput.vue';

defineOptions({ layout: Layout });

interface PageProps {
    cities: PaginatedData<App.Data.CityData>;
}

const page = usePage<PageProps>();
const cities = computed(() => page.props.cities);

// ✅ SAME PATTERN AS BRANDS (IMPORTANT)
const { form, reset, isFiltered } = useSearchFilter(route('cities.index'));

// Columns
const columns = [
    { accessorKey: 'name', header: 'Name', sortable: true, mobileTitle: 'Name' },
    { accessorKey: 'country', header: 'Country', sortable: true, mobileTitle: 'Country' },
    { accessorKey: 'regions_count', header: 'Regions', sortable: false, mobileTitle: 'Regions' },
    { accessorKey: 'created_at', header: 'Created', sortable: true, mobileTitle: 'Created' },
    { accessorKey: 'actions', header: '', sortable: false, mobileTitle: 'Actions' },
];

// Breadcrumb
const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Cities', href: route('cities.index') },
    ]);
});

// Delete
async function handleDeleteCity(city: App.Data.CityData) {
    if (!city?.id) return;

    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: 'Delete City',
        description: `Are you sure you want to delete "${city.name}"? This will also delete all associated regions.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
    });

    if (confirmed) {
        router.delete(route('cities.destroy', city.id), {
            preserveScroll: true,
        });
    }
}

// Apply filters
const applyFilters = () => {
    router.get(route('cities.index'), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filter
const clearFilter = (key: string) => {
    form.value.filter[key] = '';

    router.get(route('cities.index'), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Format date
const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <AppContainer>

        <Head title="Cities" />

        <PageHeading>
            <template #title>Cities</template>

            <template #subtitle>
                Manage cities and their regions
            </template>

            <template #links>
                <Button as-child size="sm">
                    <!-- <Link :href="route('cities.create')">
                        <Icon icon="radix-icons:plus-circled" class="size-4" />
                        New
                    </Link> -->
                </Button>
            </template>
        </PageHeading>

        <Card class="mt-4">
            <CardContent>

                <AppDataTableNew v-if="cities?.data?.length" :columns="columns" :data="cities.data"
                    search-placeholder="Search cities by name or country..." v-model:search="form.filter.global"
                    :pagination-data="cities" v-model:perPage="form.perPage" @resetFilter="reset()"
                    :isFiltered="isFiltered">

                    <!-- Filters -->
                    <template #filters>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <TextInput v-model="form.filter.country" placeholder="Filter by country"
                                @keyup.enter="applyFilters" class="w-full" />
                        </div>
                    </template>

                    <!-- Name -->
                    <template #name-cell="{ row }">
                        <div class="flex items-center gap-2">
                            <Icon icon="lucide:building-2" class="size-4 text-muted-foreground" />
                            <span class="font-medium">{{ row.original.name }}</span>
                        </div>
                    </template>

                    <!-- Regions -->
                    <template #regions_count-cell="{ row }">
                        <div class="flex items-center gap-1">
                            <Icon icon="lucide:map" class="size-3 text-muted-foreground" />
                            <span class="font-mono">
                                {{ row.original.regions_count ?? 0 }} region(s)
                            </span>
                        </div>
                    </template>

                    <!-- Created -->
                    <template #created_at-cell="{ row }">
                        <span>{{ formatDate(row.original.created_at) }}</span>
                    </template>

                    <!-- Actions -->
                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <!--
                            <AppDataTableActionButton
                                icon="lucide:trash-2"
                                tooltip="Delete"
                                variant="danger"
                                @click="handleDeleteCity(row.original)"
                            />
                            -->
                        </div>
                    </template>

                </AppDataTableNew>

                <!-- Empty -->
                <div v-else class="text-center py-8 text-muted-foreground">
                    No cities found.
                </div>

            </CardContent>
        </Card>
    </AppContainer>
</template>