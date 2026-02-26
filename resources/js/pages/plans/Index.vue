<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types';
import { usePage, router, Head } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import PlanForm from './_partials/PlanForm.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { useBreadcrumb } from '@/composables/useBreadcrumb';

defineOptions({ layout: Layout });

// Props and Page setup
const page = usePage<{
    plans: PaginatedData<App.Models.Plan>;
}>();

const plans = computed(() => page.props.plans);

// Initialize search filter
const { form, reset, isFiltered } = useSearchFilter(route('plans.index'));

// Ensure form has proper structure if useSearchFilter might return null
if (!form.value) {
    form.value = {
        filter: {
            global: ''
        },
        perPage: 10
    };
}

// Columns for data table
const columns = [
    { accessorKey: 'name', header: 'Name', sortable: true, mobileTitle: 'Name' },
    { accessorKey: 'price', header: 'Price', sortable: true, mobileTitle: 'Price' },
    { accessorKey: 'duration_days', header: 'Duration (Days)', sortable: true, mobileTitle: 'Duration' },
    { accessorKey: 'actions', header: '', sortable: false, mobileTitle: 'Actions' },
];

// Modal and breadcrumbs
const { handleShowModal, showModal, selectedItem } = useModal();
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Plans', href: route('plans.index') }
    ]);
});

// DELETE handler as a normal async function
async function handleDeletePlan(plan: App.Models.Plan) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: 'Delete Plan',
        description: `Are you sure you want to delete "${plan.name}"?`,
        confirmText: 'Delete',
        cancelText: 'Cancel'
    });

    if (confirmed) {
        router.delete(route('plans.destroy', plan.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: show success message
            }
        });
    }
}

// Format price for display
function formatPrice(price: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    }).format(price);
}

// Format duration for display
function formatDuration(days: number): string {
    if (days === 1) return '1 day';
    if (days === 7) return '1 week';
    if (days === 30) return '1 month';
    if (days === 365) return '1 year';
    return `${days} days`;
}
</script>

<template>
    <AppContainer>

        <Head title="Plans" />

        <PageHeading>
            <template #title>Plans</template>
            <template #links>
                <AppButton label="New Plan" icon="radix-icons:plus-circled" @click="handleShowModal({})" class="" />
            </template>
        </PageHeading>

        <Card class="mt-4">
            <CardContent>
                <AppDataTableNew :columns="columns" :data="plans?.data" search-placeholder="Search plans..."
                    v-model:search="form.filter.global" :pagination-data="plans" v-model:perPage="form.perPage"
                    @resetFilter="reset()" :isFiltered="isFiltered">
                    <template #filters>
                        <!-- You can add filters here if needed -->
                    </template>

                    <template #price-cell="{ row }">
                        <span class="font-semibold">
                            {{ formatPrice(row.original.price) }}
                        </span>
                    </template>

                    <template #duration_days-cell="{ row }">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" :class="{
                            'bg-blue-100 text-blue-800': row.original.duration_days <= 7,
                            'bg-green-100 text-green-800': row.original.duration_days > 7 && row.original.duration_days <= 30,
                            'bg-purple-100 text-purple-800': row.original.duration_days > 30
                        }">
                            {{ formatDuration(row.original.duration_days) }}
                        </span>
                    </template>

                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton icon="lucide:edit" tooltip="Edit"
                                @click="handleShowModal(row.original)" />
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger"
                                @click="handleDeletePlan(row.original)" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>

        <PlanForm :plan="selectedItem" v-model="showModal" />
    </AppContainer>
</template>