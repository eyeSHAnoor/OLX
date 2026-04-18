<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types';
import { usePage, router, Head } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { useBreadcrumb } from '@/composables/useBreadcrumb';
import { useSearchFilter } from '@/composables/useSearchFilter';
import UserSubscriptionModal from './_partials/UserSubscriptionModal.vue';
import StatusBadge from '@/components/Application/StatusBadge.vue';
import AppContainer from '@/components/Application/AppContainer.vue';
import PageHeading from '@/components/Application/PageHeading.vue';
import AppButton from '@/components/Application/AppButton.vue';
import { Card, CardContent } from '@/components/ui/card';

defineOptions({ layout: Layout });

const page = usePage<{
    users: PaginatedData<App.Data.UserData>;
}>();

const users = computed(() => page.props.users);

// Use search/filter composable (uncomment if you have it)
// const { form, reset, isFiltered } = useSearchFilter(route('users.index'));
// Otherwise use the simple reactive form:
const form = ref({
    filter: {
        global: '',
        subscription_payment_status: ''
    },
    perPage: 10
});

const isFiltered = computed(() => {
    return form.value.filter.global !== '' || form.value.filter.subscription_payment_status !== '';
});

function reset() {
    form.value.filter.global = '';
    form.value.filter.subscription_payment_status = '';
    form.value.perPage = 10;
    activePaymentFilter.value = 'all';
    router.get(route('users.index'), form.value, { preserveState: true, preserveScroll: true });
}

// If you have useSearchFilter, replace the above with:
// const { form, reset, isFiltered } = useSearchFilter(route('users.index'));
// and initialize form.value if needed.

// Payment status filter tabs
const paymentOptions = [
    { value: 'all', label: 'All' },
    { value: 'pending', label: 'Pending' },
    { value: 'completed', label: 'Completed' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'expired', label: 'Expired' },
    { value: 'none', label: 'No Subscription' },
];

const activePaymentFilter = ref<'all' | 'pending' | 'completed' | 'rejected' | 'expired' | 'none'>('all');

// Sync activePaymentFilter with form value on mount
watch(activePaymentFilter, (newVal) => {
    if (newVal === 'all') {
        form.value.filter.subscription_payment_status = '';
    } else {
        form.value.filter.subscription_payment_status = newVal;
    }
    // Trigger backend filter
    router.get(route('users.index'), form.value, { preserveState: true, preserveScroll: true });
});

// Initialize from URL query params on mount
onMounted(() => {
    const urlStatus = form.value.filter.subscription_payment_status;
    if (urlStatus && urlStatus !== '') {
        activePaymentFilter.value = urlStatus as any;
    } else {
        activePaymentFilter.value = 'all';
    }
});

const columns = [
    { accessorKey: 'name', header: 'User', sortable: true },
    { accessorKey: 'email', header: 'Email', sortable: true },
    { accessorKey: 'subscription_status', header: 'Subscription', sortable: false },
    { accessorKey: 'status', header: 'Status', sortable: true },
    { accessorKey: 'purchases', header: 'Subscription starts_at', sortable: true },
    { accessorKey: 'created_at', header: 'Joined', sortable: true },
    { accessorKey: 'actions', header: '', sortable: false },
];

const getStatusBadge = (status: string) => {
    const badges = {
        active: 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        inactive: 'bg-gray-100 text-gray-700 border border-gray-200'
    };
    return badges[status] || badges.inactive;
};

const { handleShowModal, showModal, selectedItem } = useModal();
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Users', href: route('users.index') }
    ]);
});

async function handleDeleteUser(user: App.Data.UserData) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: 'Delete User',
        description: `Are you sure you want to delete "${user.name}"?`,
        confirmText: 'Delete',
        cancelText: 'Cancel'
    });
    if (confirmed) {
        router.delete(route('users.destroy', user.id), { preserveScroll: true });
    }
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <AppContainer>

        <Head title="Users" />

        <PageHeading>
            <template #title>Users</template>
            <template #links>
                <AppButton label="Add User" icon="radix-icons:plus-circled" @click="handleShowModal({})" />
            </template>
        </PageHeading>

        <Card class="mt-4">
            <CardContent>
                <!-- FILTER TABS (like internal orders example) -->
                <ul
                    class="mt-2 mb-5 flex flex-wrap border-b border-gray-200 text-center text-sm font-medium text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    <li v-for="option in paymentOptions" :key="option.value" class="me-2 cursor-pointer"
                        @click="activePaymentFilter = option.value">
                        <span :class="[
                            'inline-flex items-center gap-x-1 rounded-t-lg px-4 py-2 capitalize',
                            activePaymentFilter === option.value
                                ? 'border-b border-primary text-primary dark:text-blue-500'
                                : 'hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300'
                        ]">
                            {{ option.label }}
                        </span>
                    </li>
                </ul>

                <!-- DATA TABLE with built-in search and reset -->
                <AppDataTableNew :columns="columns" :data="users?.data" search-placeholder="Search users..."
                    v-model:search="form.filter.global" :pagination-data="users" v-model:perPage="form.perPage"
                    @resetFilter="reset()" :isFiltered="isFiltered">
                    <!-- Custom cell templates -->
                    <template #name-cell="{ row }">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white text-sm font-semibold">
                                {{ row.original.name.charAt(0).toUpperCase() }}
                            </div>
                            <span class="font-medium">{{ row.original.name }}</span>
                        </div>
                    </template>

                    <template #email-cell="{ row }">
                        <span class="text-sm">{{ row.original.email }}</span>
                    </template>

                    <template #subscription_status-cell="{ row }">
                        <div v-if="row.original.subscription">
                            <StatusBadge :status="row.original.subscription?.payment_status || 'none'" />
                            <!-- <StatusBadge v-else :status="row.original.subscription?.status || 'none'" /> -->
                        </div>
                        <span v-else class="text-sm text-gray-500">No Subscription</span>
                    </template>

                    <template #status-cell="{ row }">
                        <span class="px-2 py-1 text-xs font-medium rounded-lg"
                            :class="getStatusBadge(row.original.status)">
                            {{ row.original.status }}
                        </span>
                    </template>

                    <template #purchases-cell="{ row }">
                        <div v-if="row.original.subscription">
                            <span v-if="row.original.subscription?.starts_at" class="text-sm text-gray-600">
                                {{ formatDate(row.original.subscription?.starts_at) }}
                            </span>
                            <span v-else class="text-sm text-gray-500">approve it to accept or reject</span>
                        </div>
                    </template>

                    <template #created_at-cell="{ row }">
                        <span class="text-sm text-gray-600">{{ formatDate(row.original.created_at) }}</span>
                    </template>

                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton icon="lucide:eye" tooltip="View Details"
                                @click="handleShowModal(row.original)" v-if="row.original.subscription" />
                            <!-- Uncomment if delete needed -->
                            <!-- <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger" @click="handleDeleteUser(row.original)" /> -->
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>

        <!-- Modals -->
        <!-- <UserForm :user="selectedItem" v-model="showModal" /> -->
        <UserSubscriptionModal :user="selectedItem" v-model="showModal" />
    </AppContainer>
</template>