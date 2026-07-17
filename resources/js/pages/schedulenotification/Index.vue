<!-- resources/js/Pages/Admin/Notifications/Index.vue -->
<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from "@/types";
import { usePage, router, Head } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { useBreadcrumb } from "@/composables/useBreadcrumb";

defineOptions({ layout: Layout });

// Props and Page setup
const page = usePage<{
    notifications: PaginatedData<App.Data.ScheduledNotificationData>;
}>();

const notifications = computed(() => page.props.notifications);

// Initialize search filter
const { form, reset, isFiltered } = useSearchFilter(
    route("scheduled-notifications.index")
);

// Columns for data table
const columns = [
    { accessorKey: "title", header: "Title", sortable: true, mobileTitle: "Title" },
    { accessorKey: "message", header: "Message", sortable: false, mobileTitle: "Message" },
    {
        accessorKey: "scheduled_at",
        header: "Scheduled",
        sortable: true,
        mobileTitle: "Scheduled",
    },
    { accessorKey: "status", header: "Status", sortable: false, mobileTitle: "Status" },
    { accessorKey: "actions", header: "", sortable: false, mobileTitle: "Actions" },
];

// Modal and breadcrumbs
const { handleShowModal, showModal, selectedItem } = useModal();
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: "Home", href: "/dashboard" },
        { label: "Notifications", href: route("scheduled-notifications.index") },
    ]);
});

// DELETE handler
async function handleDeleteNotification(
    notification: App.Data.ScheduledNotificationData
) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: "Delete Notification",
        description: `Are you sure you want to delete "${notification.title}"?`,
        confirmText: "Delete",
        cancelText: "Cancel",
    });

    if (confirmed) {
        router.delete(route("scheduled-notifications.destroy", notification.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: show success message
            },
        });
    }
}

// Format date
function formatDate(date: string) {
    if (!date) return "";
    return new Date(date).toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}
</script>

<template>
    <AppContainer>

        <Head title="Scheduled Notifications" />

        <PageHeading>
            <template #title>Scheduled Notifications</template>
            <template #links>
                <Button as-child size="sm">
                    <Link :href="route('scheduled-notifications.create')">
                    <Icon icon="radix-icons:plus-circled" class="size-4" /> New
                    </Link>
                </Button>
            </template>
        </PageHeading>

        <Card class="mt-4">
            <CardContent>
                <AppDataTableNew :columns="columns" :data="notifications?.data"
                    search-placeholder="Search notifications..." v-model:search="form.filter.global"
                    :pagination-data="notifications" v-model:perPage="form.perPage" @resetFilter="reset()"
                    :isFiltered="isFiltered">
                    <template #message-cell="{ row }">
                        <div class="max-w-xs truncate" :title="row.original.message">
                            {{ row.original.message }}
                        </div>
                    </template>

                    <template #scheduled_at-cell="{ row }">
                        <div class="text-sm">
                            {{ formatDate(row.original.scheduled_at) }}
                        </div>
                    </template>

                    <template #status-cell="{ row }">
                        <Badge :variant="row.original.is_sent ? 'default' : 'secondary'">
                            {{ row.original.is_sent ? "✅ Sent" : "⏳ Pending" }}
                        </Badge>
                    </template>

                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <!-- <AppDataTableActionButton icon="lucide:edit" tooltip="Edit" :disabled="row.original.is_sent"
                                @click="
                                    router.visit(route('scheduled-notifications.edit', row.original.id))
                                    " /> -->
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger"
                                :disabled="row.original.is_sent" @click="handleDeleteNotification(row.original)" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>

        <NotificationForm :notification="selectedItem" v-model="showModal" />
    </AppContainer>
</template>
