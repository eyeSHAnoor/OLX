<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { InertiaPageProps, PaginatedData } from '@/types';
import Layout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import UserRoleModal from './_partial/UserRoleModal.vue';
import { useInitials } from '@/composables/useInitials';
import useHelpers from '@/composables/useHelpers';
import useSearchFilter from '@/composables/useSearchFilter.js';
import { useBreadcrumb } from '@/composables/useBreadcrumb';
defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    users: PaginatedData<App.Data.UserData>;
    roles: App.Data.RoleData[];

}

const page = usePage<PageProps>();
const users = computed(() => page.props.users);
const roles = computed(() => page.props.roles);

const { form, reset, isFiltered } = useSearchFilter(route('user-roles.index'));
const { formatDate } = useHelpers();
const { getInitials } = useInitials();

const columns = [
    { accessorKey: 'member', header: 'Member', sortable: false, mobileTitle: 'Member' },
    { accessorKey: 'role', header: 'Role', sortable: false, mobileTitle: 'Role' },
    { accessorKey: 'status', header: 'Status', sortable: false, mobileTitle: 'Status' },
    // { accessorKey: 'last_login', header: 'Last Login', sortable: false, mobileTitle: 'Last Login' },
    { accessorKey: 'created', header: 'Created', sortable: false, mobileTitle: 'Created' },
    { accessorKey: 'actions', header: '', enableHiding: false, mobileTitle: 'Actions' },
];

// Use modal composable
const { handleShowModal, showModal, selectedItem } = useModal();

const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Users', href: route('user-roles.index') }
    ]);
});

//console.log(users);

</script>

<template>
    <AppContainer>

        <Head title="Users Management" />

        <PageHeading>
            <template #title>
                <HeadingSmall title="Users" description="Manage your users here" />
            </template>
            <template #links>
                <!-- Open modal for new user -->
                <AppButton label="New User" icon="radix-icons:plus-circled" @click="handleShowModal(null)" />
            </template>
        </PageHeading>

        <SettingsLayout>
            <Card>
                <CardContent>
                    <UsersStats class="pb-5" />

                    <AppDataTableNew :columns="columns" :data="users?.data" :search-placeholder="'Search users...'"
                        v-model:search="form.filter.global" :pagination-data="users" v-model:perPage="form.perPage"
                        @resetFilter="reset()" :isFiltered="isFiltered">

                        <template #member-cell="{ row }">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-x-2">
                                <Avatar class="h-8 w-8 overflow-hidden rounded-full">
                                    <AvatarImage :src="row.original.avatar" :alt="row.original.name" />
                                    <AvatarFallback
                                        class="bg-neutral-300 sm:bg-neutral-200 rounded-full text-black dark:text-white">
                                        {{ getInitials(row.original.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <p>{{ row.original.name }}</p>
                                    <p class="text-muted-foreground">{{ row.original.email }}</p>
                                </div>
                            </div>
                        </template>

                        <template #status-cell="{ row }">
                            <StatusBadge :status="row.original.status" variant="subtle" />
                        </template>

                        <template #last_login-cell="{ row }">
                            {{ formatDate(row.original.last_login) }}
                        </template>

                        <template #created-cell="{ row }">
                            {{ formatDate(row.original.created_at) }}
                        </template>

                        <template #actions-cell="{ row }">
                            <div class="flex items-center justify-end">
                                <!-- Open modal for editing user -->
                                <AppDataTableActionButton icon="lucide:edit" tooltip="Edit"
                                    @click="handleShowModal(row.original)" />
                            </div>
                        </template>
                    </AppDataTableNew>
                </CardContent>
            </Card>
        </SettingsLayout>

        <!-- Modal for create/edit -->
        <UserRoleModal :user="selectedItem" v-model="showModal" :roles="roles" />

    </AppContainer>
</template>
