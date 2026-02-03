<script setup lang="ts">
import Layout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import RoleRecordModal from '@/pages/settings/roles_permissions/_partials/RoleRecordModal.vue';
import { InertiaPageProps } from '@/types';

defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    roles: App.Data.RoleData[];
    permissions: App.Data.PermissionData[];
}

const page = usePage<PageProps>();
const roles = computed(() => page.props.roles.filter(role => role.name !== 'super_admin'));
const permissions = computed(() => page.props.permissions);


// console.log(roles)


const { formatDate, getImage } = useHelpers();
const { handleShowModal, showModal, selectedItem } = useModal();
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Roles Management', href: route('roles-permissions.index') },
    ]);
});
</script>

<template>
    <AppContainer>

        <Head title="Roles Management" />

        <PageHeading>
            <template #title>
                <HeadingSmall title="Roles Management" description="Manage user roles and permissions" />
            </template>

            <template #links>
                <AppButton label="New Role" icon="radix-icons:plus-circled" @click="handleShowModal({})" />
            </template>
        </PageHeading>

        <SettingsLayout>
            <div class="flex items-center flex-wrap gap-7">
                <Card v-for="role in roles" :key="role.id"
                    class="sm:w-60 hover:border-primary transition-all duration-150 cursor-pointer">
                    <CardContent>
                        <div class="flex items-center gap-x-3">
                            <Icon icon="lucide:folder-key" class="size-7 text-primary" />
                            <div class="grid">
                                <h3 class="font-medium">{{ role.name }}</h3>
                                <span class="text-xs text-muted-foreground">
                                    {{ role.users_count ?? 0 }} members
                                </span>
                            </div>
                        </div>
                        <p class="text-muted-foreground text-xs mt-3">
                            {{ role.description }}
                        </p>

                        <div class="flex items-center justify-end gap-x-1">
                            <AppButton label="Edit" size="xs" icon="lucide:edit" icon-size="size-3" variant="outline"
                                @click="handleShowModal(role)" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </SettingsLayout>

        <RoleRecordModal :role="selectedItem" :roles :permissions v-model="showModal" />
    </AppContainer>
</template>
