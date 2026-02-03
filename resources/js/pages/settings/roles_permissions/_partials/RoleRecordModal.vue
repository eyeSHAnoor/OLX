<script setup lang="ts">
import FormDetails from '@/pages/settings/roles_permissions/_partials/Form/FormDetails.vue';
import PermissionsFormDetails from '@/pages/settings/roles_permissions/_partials/Form/PermissionsFormDetails.vue';

const { permissions, roles, role } = defineProps<{
    roles: App.Data.RoleData[];
    role: App.Data.RoleData;
    permissions: App.Data.PermissionData[];
}>();

const getDefaultForm = (item: App.Data.RoleData) => {
    return {
        id: item?.id ?? '',
        name: item?.name ?? '',
        description: item?.description ?? '',
        permissions: item?.permissions?.map((p) => p.id) ?? [],
    };
};



const form = useForm({ ...getDefaultForm(role) });
const model = defineModel();

watch(model, (isOpen) => {
    if (isOpen) {
        const newValues = getDefaultForm(role);
        form.defaults(newValues);
        form.reset();
    }
});

const submit = () => {
    if (form.id) {
        form.post(route('roles-permissions.update', form.id), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    } else {
        form.post(route('roles-permissions.store'), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    }
};

const alert = useAlertDialog();

const destroy = async () => {
    const confirmed = await alert.show({
        title: 'Delete Role?',
        description: 'Are you sure you want to delete this role? This action cannot be undone.',
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
    });

    if (confirmed && form.id) {
        form.delete(route('roles-permissions.destroy', form.id), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    }
};
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="w-full !overflow-y-auto px-7 sm:!w-6/12 sm:!max-w-full">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-x-3">
                    {{ form.id ? 'Edit Role' : 'Create Role' }}
                </DialogTitle>
            </DialogHeader>

            <div class="mt-3 grid gap-y-4">
                <ValidationErrors />
                <FormDetails :form />
                <PermissionsFormDetails :form :role :permissions />
            </div>

            <div class="flex items-center justify-end gap-x-3">
                <AppButton v-if="form.id" variant="danger" label="Delete" icon="lucide:trash-2" @click="destroy"
                    size="sm" />

                <AppButton variant="default" :label="form.id ? 'Save Changes' : 'Save'" :processing="form.processing"
                    @click="submit" size="sm" />
            </div>
        </DialogContent>
    </Dialog>
</template>
