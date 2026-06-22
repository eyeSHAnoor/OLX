<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from "@/types";
import { usePage, router, Head } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import PermissionForm from "./_partials/PermissionForm.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { useBreadcrumb } from "@/composables/useBreadcrumb";

defineOptions({ layout: Layout });

// Props and Page setup
const page = usePage<{
    permissions: PaginatedData<App.Models.SubscriptionPermission>;
}>();

const permissions = computed(() => page.props.permissions);

// Initialize search filter
const { form, reset, isFiltered } = useSearchFilter(
    route("subscription-permissions.index")
);

if (!form.value) {
    form.value = {
        filter: { global: "" },
        perPage: 10,
    };
}

// Columns for data table
const columns = [
    { accessorKey: "name", header: "Name", sortable: true, mobileTitle: "Name" },
    { accessorKey: "label", header: "Label", sortable: true, mobileTitle: "Label" },
    {
        accessorKey: "created_at",
        header: "Created",
        sortable: true,
        mobileTitle: "Created",
    },
    { accessorKey: "actions", header: "", sortable: false, mobileTitle: "Actions" },
];

// Modal and breadcrumbs
const { handleShowModal, showModal, selectedItem } = useModal();
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: "Home", href: "/dashboard" },
        { label: "Permissions", href: route("subscription-permissions.index") },
    ]);
});

// DELETE handler
async function handleDeletePermission(permission: App.Models.SubscriptionPermission) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: "Delete Permission",
        description: `Are you sure you want to delete "${permission.name}"?`,
        confirmText: "Delete",
        cancelText: "Cancel",
    });

    if (confirmed) {
        router.delete(route("subscription-permissions.destroy", permission.id), {
            preserveScroll: true,
        });
    }
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString();
}
</script>

<template>
    <AppContainer>

        <Head title="Subscription Permissions" />

        <PageHeading>
            <template #title>Permissions</template>
            <template #links>
                <AppButton label="New Permission" icon="radix-icons:plus-circled" @click="handleShowModal({})" />
            </template>
        </PageHeading>

        <Card class="mt-4">
            <CardContent>
                <AppDataTableNew :columns="columns" :data="permissions?.data" search-placeholder="Search permissions..."
                    v-model:search="form.filter.global" :pagination-data="permissions" v-model:perPage="form.perPage"
                    @resetFilter="reset()" :isFiltered="isFiltered">
                    <template #created_at-cell="{ row }">
                        <span>{{ formatDate(row.original.created_at) }}</span>
                    </template>

                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton icon="lucide:edit" tooltip="Edit"
                                @click="handleShowModal(row.original)" />
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger"
                                @click="handleDeletePermission(row.original)" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>

        <PermissionForm :permission="selectedItem" v-model="showModal" />
    </AppContainer>
</template>
