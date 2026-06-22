<script setup lang="ts">
import { CardContent } from "@/components/ui/card";
import { useForm } from "@inertiajs/vue3";
import { watch } from "vue";
import { useAlertDialog } from "@/composables/useAlertDialog";

const { permission } = defineProps<{
    permission?: App.Models.SubscriptionPermission;
}>();

interface PermissionFormData {
    id?: string | number;
    name: string;
    label: string;
}

const getDefaultForm = (
    item: App.Models.SubscriptionPermission | undefined
): PermissionFormData => ({
    id: item?.id ?? "",
    name: item?.name ?? "",
    label: item?.label ?? "",
});

const form = useForm<PermissionFormData>({ ...getDefaultForm(permission) });

const model = defineModel<boolean>();

watch(model, (isOpen) => {
    if (isOpen) {
        const newValues = getDefaultForm(permission);
        form.defaults(newValues);
        form.reset();
    }
});

// Submit
const submit = () => {
    if (form.id) {
        form.put(route("subscription-permissions.update", form.id), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    } else {
        form.post(route("subscription-permissions.store"), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    }
};

// Delete
const alert = useAlertDialog();

const destroy = async () => {
    if (!form.id) return;

    const confirmed = await alert.show({
        title: "Delete Permission",
        description: `Are you sure you want to delete "${form.name}"?`,
        confirmText: "Yes, Delete",
        cancelText: "Cancel",
    });

    if (confirmed) {
        form.delete(route("subscription-permissions.destroy", form.id), {
            onSuccess: () => (model.value = false),
        });
    }
};
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="!w-7/12 max-w-4xl !overflow-y-auto px-7 max-h-[90vh]">
            <DialogHeader>
                <DialogTitle>
                    {{
                        permission ? `Edit Permission: ${permission.name}` : "Create New Permission"
                    }}
                </DialogTitle>
            </DialogHeader>

            <div class="space-y-6">
                <ValidationErrors />

                <Card>
                    <CardContent class="space-y-6 pt-6">
                        <div class="space-y-4">
                            <h3 class="font-semibold border-b pb-2">Permission Details</h3>

                            <div class="grid grid-cols-2 gap-4">
                                <TextInput label="Name (slug)" v-model="form.name" />
                                <TextInput label="Label (Display Name)" v-model="form.label" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-between">
                    <AppButton v-if="form.id" variant="danger" @click="destroy"> Delete </AppButton>
                    <div class="flex gap-2">
                        <AppButton @click="model = false"> Cancel </AppButton>
                        <AppButton @click="submit">
                            {{ form.id ? "Update" : "Create" }}
                        </AppButton>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
