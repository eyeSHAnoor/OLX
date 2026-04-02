<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types';
import { usePage, router, Head } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
// import UserForm from './_partials/UserForm.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { useBreadcrumb } from '@/composables/useBreadcrumb';
// import { useSearchFilter } from '@/composables/useSearchFilter';
import { Icon } from '@iconify/vue';
import UserSubscriptionModal from './_partials/UserSubscriptionModal.vue';
import StatusBadge from '@/components/Application/StatusBadge.vue';

defineOptions({ layout: Layout });

const page = usePage<{
    users: PaginatedData<App.Data.UserData>;
}>();

const users = computed(() => page.props.users);
//console.log('Users data:', page.props);
const { form, reset, isFiltered } = useSearchFilter(route('users.index'));

if (!form.value) {
    form.value = {
        filter: {
            global: ''
        },
        perPage: 10
    };
}

const columns = [
    { accessorKey: 'name', header: 'User', sortable: true },
    { accessorKey: 'email', header: 'Email', sortable: true },
    { accessorKey: 'subscription_status', header: 'Subscription', sortable: false },
    { accessorKey: 'status', header: 'Status', sortable: true },
    { accessorKey: 'created_at', header: 'Joined', sortable: true },
    { accessorKey: 'actions', header: '', sortable: false },
];

const getSubscriptionBadge = (status: string) => {
    const badges = {
        active: 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        pending: 'bg-yellow-100 text-yellow-700 border border-yellow-200',
        none: 'bg-gray-100 text-gray-500 border border-gray-200'
    };
    return badges[status] || badges.none;
};

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
        router.delete(route('users.destroy', user.id), {
            preserveScroll: true
        });
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
                <AppButton label="Add User" icon="radix-icons:plus-circled" @click="handleShowModal({})" class="" />
            </template>
        </PageHeading>

        <Card class="mt-4">
            <CardContent>
                <AppDataTableNew :columns="columns" :data="users?.data" search-placeholder="Search users..."
                    v-model:search="form.filter.global" :pagination-data="users" v-model:perPage="form.perPage"
                    @resetFilter="reset()" :isFiltered="isFiltered">

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
                        <StatusBadge v-if="row.original.subscription"
                            :status="row.original.subscription?.payment_status || 'none'" />
                        <span v-else class="text-sm text-gray-500">No Subscription</span>
                    </template>

                    <template #status-cell="{ row }">
                        <span class="px-2 py-1 text-xs font-medium rounded-lg"
                            :class="getStatusBadge(row.original.status)">
                            {{ row.original.status }}
                        </span>
                    </template>

                    <template #created_at-cell="{ row }">
                        <span class="text-sm text-gray-600">{{ formatDate(row.original.created_at) }}</span>
                    </template>

                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <!-- <AppDataTableActionButton icon="lucide:edit" tooltip="Edit"
                                @click="handleShowModal(row.original)" /> -->
                            <AppDataTableActionButton icon="lucide:eye" tooltip="View Details"
                                @click="handleShowModal(row.original)" v-if="row.original.subscription" />
                            <!-- <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger"
                                @click="handleDeleteUser(row.original)" /> -->
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>

        <UserForm :user="selectedItem" v-model="showModal" />
        <UserSubscriptionModal :user="selectedItem" v-model="showModal" />
    </AppContainer>
</template>