<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch, defineProps, defineModel } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useAlertDialog } from '@/composables/useAlertDialog';

// Define the shape of a broadcast message (matching the model)
interface BroadcastMessageData {
    id?: number;
    title: string;
    body: string;
    is_active: boolean;
}

const props = defineProps<{
    message?: BroadcastMessageData;
}>();

const model = defineModel<boolean>();

// Helper to get default form values
const getDefaultForm = (item: BroadcastMessageData | undefined): BroadcastMessageData => ({
    id: item?.id ?? undefined,
    title: item?.title ?? '',
    body: item?.body ?? '',
    is_active: item?.is_active ?? false,
});

// Use Inertia's useForm hook
const form = useForm(getDefaultForm(props.message));

// Watch modal open to reset form when editing different message
watch(model, (isOpen) => {
    if (isOpen) {
        const newValues = getDefaultForm(props.message);
        form.defaults(newValues);
        form.reset();
    }
});

// Submit handler
const submit = () => {
    if (form.id) {
        form.put(route('broadcast-messages.update', form.id), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    } else {
        form.post(route('broadcast-messages.store'), {
            preserveScroll: true,
            onSuccess: () => (model.value = false),
        });
    }
};

// Delete handler (only when editing)
const alert = useAlertDialog();
const destroy = async () => {
    if (!form.id) return;

    const confirmed = await alert.show({
        title: 'Delete Message',
        description: `Are you sure you want to delete "${form.title}"? This action cannot be undone.`,
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
    });

    if (confirmed) {
        form.delete(route('broadcast-messages.destroy', form.id), {
            onSuccess: () => (model.value = false),
        });
    }
};
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="!w-6/12 !overflow-y-auto px-7">
            <DialogHeader class="!px-0 !pb-0">
                <DialogTitle>
                    {{ message ? `Edit Message: ${message.title}` : 'Create New Broadcast Message' }}
                </DialogTitle>
            </DialogHeader>

            <div class="mt-3 grid gap-y-4">
                <ValidationErrors />

                <Card>
                    <CardContent class="space-y-4 pt-4">
                        <TextInput label="Title" v-model="form.title" :error="form.errors.title"
                            placeholder="Enter message title" required />

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Message Body</label>
                            <textarea v-model="form.body" rows="5"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Enter the message content..."
                                :class="{ 'border-destructive': form.errors.body }" />
                            <p v-if="form.errors.body" class="text-sm text-destructive">{{ form.errors.body }}</p>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium">Active Status</label>
                            <Switch v-model:checked="form.is_active"
                                :class="form.errors.is_active ? 'border-destructive' : ''" />
                        </div>
                        <p v-if="form.errors.is_active" class="text-sm text-destructive">{{ form.errors.is_active }}</p>
                        <p class="text-xs text-muted-foreground">
                            Active messages will be shown to users.
                        </p>
                    </CardContent>
                </Card>

                <DialogFooter>
                    <div class="flex items-center justify-between gap-1 w-full">
                        <AppButton v-if="form.id" label="Delete" icon="lucide:trash-2" variant="danger" size="sm"
                            :processing="form.processing" @click="destroy" />
                        <div class="ml-auto flex items-center gap-2">
                            <AppButton size="sm" variant="outline" label="Cancel" @click="model = false"
                                :disabled="form.processing" />
                            <AppButton size="sm" :processing="form.processing" label="Save" @click="submit" />
                        </div>
                    </div>
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>