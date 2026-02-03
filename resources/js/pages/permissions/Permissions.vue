<script setup lang="ts">
import { InertiaPageProps } from '@/types';
import { InertiaForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import Layout from '@/layouts/AppLayout.vue';

defineOptions({ layout: Layout });

// import { useI18n } from 'vue-i18n';
// const { t } = useI18n();

interface PageProps extends InertiaPageProps {
    roles: App.Data.RoleData[];
    permissions: App.Data.PermissionData[];
}

const page = usePage<PageProps>();
const roles = computed(() => page.props.roles);
const permissions = computed(() => page.props.permissions);

const { titleCase } = useHelpers();

const form = useForm({
    permission_id: null,
    role_ids: [],
});

const hasRole = (permission, roleId) => {
    return permission.roles.some((r) => r.id === roleId);
};

const toggleRole = (permissionId, roleId, checked) => {
    const selectedPermission = permissions.value?.find((p) => p.id === permissionId);
    const currentRoles = selectedPermission.roles.map((r) => r.id);

    let updatedRoles = checked ? [...new Set([...currentRoles, roleId])] : currentRoles.filter((id) => id !== roleId);

    form.permission_id = permissionId;
    form.role_ids = updatedRoles;

    // form.post(route('permissions.update'), {
    //     preserveScroll: true,
    //     onFinish: () => form.reset()
    // })
};

const submit = () => {
    form.post(route('permissions.update'), {
        preserveScroll: true,
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AppContainer>

        <Head title="Roles and Permissions" />

        <PageHeading>{{ $t("settingsrole_permissions.title") }}</PageHeading>

        <Card class="">
            <!--                <CardHeader>-->
            <!--                    <CardTitle>General Tax Rate</CardTitle>-->
            <!--                </CardHeader>-->
            <CardContent>
                <div class="max-h-[700px] overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ $t("settingsrole_permissions.permissions") }}</TableHead>
                                <template v-for="role in roles" :key="role.id">
                                    <TableHead class="text-center whitespace-normal break-words max-w-[100px]">{{
                                        titleCase(role.name) }}</TableHead>
                                </template>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="permission in permissions" :key="permission.id">
                                <TableCell class="whitespace-normal break-words max-w-[100px]">{{
                                    titleCase(permission.name) }}</TableCell>
                                <template v-for="role in roles" :key="role.id">
                                    <TableCell class="text-center">
                                        <CheckboxInput :model-value="hasRole(permission, role.id)"
                                            @update:modelValue="checked => toggleRole(permission.id, role.id, checked)"
                                            class="h-4 w-4" />
                                    </TableCell>
                                </template>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
            <CardFooter class="flex justify-end px-6 pb-6">
                <AppButton @click="submit" :processing="form.processing" />
            </CardFooter>
        </Card>
    </AppContainer>
</template>
