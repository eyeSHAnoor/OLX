<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types';
import { usePage, router, Head } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import BroadcastMessageForm from './_partials/BroadcastMessageForm.vue';
import axios from 'axios';
import { useAlertDialog } from '@/composables/useAlertDialog';

// Define the shape of a broadcast message (since we don't have a Data class)
interface BroadcastMessage {
    id: number;
    title: string;
    body: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

defineOptions({ layout: Layout });

// Props from Inertia
const page = usePage();

const messages = computed(() => page.props.messages);

// Search filter setup
const { form, reset, isFiltered } = useSearchFilter(route('broadcast-messages.index'));

// Columns for the data table
const columns = [
    { accessorKey: 'title', header: 'Title', sortable: true, mobileTitle: 'Title' },
    { accessorKey: 'body', header: 'Message', sortable: false, mobileTitle: 'Message' },
    { accessorKey: 'is_active', header: 'Active', sortable: true, mobileTitle: 'Active' },
    { accessorKey: 'actions', header: '', sortable: false, mobileTitle: 'Actions' },
];

// Modal for editing (optional – you can remove if not needed)
const { handleShowModal, showModal, selectedItem } = useModal();

// Breadcrumb setup
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Broadcast Messages', href: route('broadcast-messages.index') },
    ]);
});

const alert = useAlertDialog();

async function broadcastMessage(message) {
    const confirmed = await alert.show({
        title: 'Broadcast Message',
        description: `Are you sure you want to broadcast "${message.title}" to all users?`,
        confirmText: 'Yes, Broadcast',
        cancelText: 'Cancel',
    });

    if (!confirmed) return;

    try {
        await router.post(route('broadcast-message.send', message.id));
        alert.show({ title: 'Success', description: 'Message broadcasted successfully.' });
    } catch (e) {
        alert.show({ title: 'Error', description: 'Failed to broadcast message.' });
    }
}

// Delete handler
async function handleDeleteMessage(message: BroadcastMessage) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: 'Delete Message',
        description: `Are you sure you want to delete "${message.title}"?`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
    });

    if (confirmed) {
        router.delete(route('broadcast-messages.destroy', message.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: show success message
            },
        });
    }
}
</script>

<template>
    <AppContainer>

        <Head title="Broadcast Messages" />

        <PageHeading>
            <template #title>Broadcast Messages</template>
            <template #links>
                <Button size="sm" @click="handleShowModal(null)">
                    <Icon icon="radix-icons:plus-circled" class="size-4" />
                    New
                </Button>
            </template>
        </PageHeading>

        <Card class="mt-4">
            <CardContent>
                <AppDataTableNew :columns="columns" :data="messages?.data" search-placeholder="Search messages..."
                    v-model:search="form.filter.global" :pagination-data="messages" v-model:perPage="form.perPage"
                    @resetFilter="reset()" :isFiltered="isFiltered">
                    <template #filters>
                        <!-- Add any additional filters here if needed -->
                    </template>

                    <!-- Custom rendering for the 'body' column – truncate long messages -->
                    <template #body-cell="{ row }">
                        <div class="max-w-md truncate">
                            {{ row.original.body }}
                        </div>
                    </template>

                    <!-- Custom badge for active status -->
                    <template #is_active-cell="{ row }">
                        <Badge :variant="row.original.is_active ? 'default' : 'secondary'">
                            {{ row.original.is_active ? 'Active' : 'Inactive' }}
                        </Badge>
                    </template>

                    <!-- Action buttons -->
                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton icon="lucide:edit" tooltip="Edit"
                                @click="handleShowModal(row.original)" />
                            <AppDataTableActionButton icon="lucide:send" tooltip="Broadcast"
                                @click="broadcastMessage(row.original)" />
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger"
                                @click="handleDeleteMessage(row.original)" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>

        <!-- Optional modal for editing – requires BroadcastMessageForm component -->
        <BroadcastMessageForm :message="selectedItem" v-model="showModal" />
    </AppContainer>
</template>