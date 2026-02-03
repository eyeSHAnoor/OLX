<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

const props = defineProps<{
    user: App.Data.UserData | null;
    roles: App.Data.RoleData[];
    modelValue: boolean; // v-model:open
}>();

const emit = defineEmits(['update:modelValue']); // to sync modal open/close

const { titleCase } = useHelpers();
const alert = useAlertDialog();

// Local modal ref (like defineModel in your Position modal)
const model = ref(props.modelValue);

// Sync local ref with parent v-model
watch(() => props.modelValue, val => model.value = val);
watch(model, val => emit('update:modelValue', val));

watch(
    [() => model.value, () => props.user],
    ([isOpen, user]) => {
        if (isOpen && user) { // only reset if modal is open and user exists
            const defaults = getDefaultForm(user);
            form.defaults(defaults);
            form.reset();
        }
    },
    { immediate: true }
);

// Generate default form values
const getDefaultForm = (item: App.Data.UserData | null) => ({
    id: item?.id ?? '',
    name: item?.name ?? '',
    email: item?.email ?? '',
    status: item?.status ?? 'active',
    password: '',
    password_confirmation: '',
    phone: item?.phone ?? '',
    // roles: item?.roles?.map(r => r.id) ?? [],
    roles: item?.roles?.[0]?.id ?? null,
});

// Initialize form
const form = useForm(getDefaultForm(props.user));

// Watch modal open or user change to reset form
watch(model, (isOpen) => {
    if (isOpen) {
        const defaults = getDefaultForm(props.user);
        form.defaults(defaults);
        form.reset();
    }
}, { immediate: true });

// Submit handler
const submit = () => {

    if (form.id) {
        form.post(route('user-roles.update', form.id), {
            preserveScroll: true,
            onSuccess: () => model.value = false,
        });
    } else {
        form.post(route('user-roles.create'), {
            preserveScroll: true,
            onSuccess: () => model.value = false,
        });
    }
};


// Delete handler
const destroy = async () => {
    if (!form.id) return;

    const confirmed = await alert.show({
        title: 'Delete User?',
        description: `Are you sure you want to delete user "${form.name}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
    });

    if (confirmed) {
        form.delete(route('user-roles.destroy', form.id), {
            preserveScroll: true,
            onSuccess: () => model.value = false,
        });
    }
};
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="!w-6/12 !overflow-y-auto px-7">
            <DialogHeader class="!px-0 !pb-0">
                <DialogTitle>{{ form.id ? 'Edit User' : 'New User' }}</DialogTitle>
            </DialogHeader>

            <div class="mt-3 grid gap-y-4">
                <ValidationErrors />

                <!-- Name & Email -->
                <TextInput label="Name" id="name" v-model="form.name" :error="form.errors.name" />
                <TextInput label="Email" id="email" v-model="form.email" :error="form.errors.email" type="email" />

                <!-- Roles -->
                <SelectInput label="Roles" id="roles" v-model="form.roles" :error="form.errors.roles">
                    <SelectContent>
                        <SelectItem v-for="role in props.roles" :key="role.id" :value="role.id">
                            {{ role.name }}
                        </SelectItem>
                    </SelectContent>
                </SelectInput>

                <!-- Status -->
                <SelectInput label="Status" id="status" v-model="form.status" :error="form.errors.status">
                    <SelectContent>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="inactive">Inactive</SelectItem>
                    </SelectContent>
                </SelectInput>

                <div class="grid  items-center">


                    <TextInput label="Phone" id="phone" v-model="form.phone" :error="form.errors.phone"
                        class="col-span-2" placeholder="Enter phone number like +92**********" />
                </div>
                <Separator />


                <!-- Password -->
                <TextInput label="Password" id="password" v-model="form.password" :error="form.errors.password"
                    type="password" />
                <TextInput label="Confirm Password" id="password_confirmation" v-model="form.password_confirmation"
                    :error="form.errors.password_confirmation" type="password" />

                <DialogFooter class="gap-4">
                    <AppButton v-if="form.id" size="xs" @click="destroy" icon="lucide:trash-2" label="Delete"
                        variant="danger" />
                    <AppButton size="xs" :processing="form.processing" @click="submit"
                        :label="form.id ? 'Update' : 'Create'" />
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>
