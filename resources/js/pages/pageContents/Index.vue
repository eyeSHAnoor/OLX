<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types';
import { usePage, router, Head, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { useBreadcrumb } from '@/composables/useBreadcrumb';
import { Icon } from '@iconify/vue';

defineOptions({ layout: Layout });

// Props
const page = usePage<{
    pageContents: PaginatedData<App.Data.PageContentData>;
}>();

const pageContents = computed(() => page.props.pageContents);

// Search filter (same as banner)
const { form, reset, isFiltered } = useSearchFilter(route('page-contents.index'));

if (!form.value) {
    form.value = {
        filter: { global: '' },
        perPage: 10
    };
}

// Table columns
const columns = [
    { accessorKey: 'page_key', header: 'Page Key', sortable: true, mobileTitle: 'Key' },
    { accessorKey: 'title', header: 'Title', sortable: true, mobileTitle: 'Title' },
    { accessorKey: 'subtitle', header: 'Subtitle', sortable: false, mobileTitle: 'Subtitle' },
    { accessorKey: 'is_active', header: 'Status', sortable: true, mobileTitle: 'Status' },
    { accessorKey: 'updated_at', header: 'Last Updated', sortable: true, mobileTitle: 'Updated' },
    { accessorKey: 'actions', header: '', sortable: false, mobileTitle: 'Actions' },
];

// Breadcrumbs
const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: 'Home', href: '/dashboard' },
        { label: 'Page Contents', href: route('page-contents.index') }
    ]);
});

// Delete handler
async function handleDelete(pageContent: App.Data.PageContentData) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: 'Delete Page Content',
        description: `Are you sure you want to delete "${pageContent.page_key}"? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'danger'
    });
    if (confirmed) {
        router.delete(route('page-contents.destroy', pageContent.id), {
            preserveScroll: true,
        });
    }
}

// Toggle status
function toggleStatus(pageContent: App.Data.PageContentData) {
    router.patch(route('page-contents.toggle-status', pageContent.id), {}, {
        preserveScroll: true,
    });
}

// Helper to format date
function formatDate(date: string) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
}
</script>

<template>
    <AppContainer>

        <Head title="Page Contents" />

        <PageHeading>
            <template #title>Page Contents</template>
            <template #description>Manage dynamic content for your pages (About, Contact, etc.)</template>
            <template #links>
                <Link :href="route('page-contents.create')">
                    <AppButton label="New Page Content" icon="radix-icons:plus-circled" />
                </Link>
            </template>
        </PageHeading>

        <Card class="mt-6">
            <CardContent class="p-0">
                <AppDataTableNew :columns="columns" :data="pageContents?.data"
                    search-placeholder="Search by page key, title..." v-model:search="form.filter.global"
                    :pagination-data="pageContents" v-model:perPage="form.perPage" @resetFilter="reset()"
                    :isFiltered="isFiltered">

                    <template #filters>
                        <!-- Status filter -->
                        <SelectInput v-model="form.filter.is_active" placeholder="All Status" class="w-36">
                            <SelectContent>
                                <SelectItem :value="null">All Status</SelectItem>
                                <SelectItem :value="true">Active</SelectItem>
                                <SelectItem :value="false">Inactive</SelectItem>
                            </SelectContent>
                        </SelectInput>
                    </template>

                    <!-- Page Key column -->
                    <template #page_key-cell="{ row }">
                        <div class="font-mono text-sm font-medium">
                            {{ row.original.page_key }}
                        </div>
                    </template>

                    <!-- Title column -->
                    <template #title-cell="{ row }">
                        <div>
                            <p class="font-medium">{{ row.original.title || '—' }}</p>
                            <p v-if="row.original.subtitle" class="text-xs text-muted-foreground">
                                {{ row.original.subtitle }}
                            </p>
                        </div>
                    </template>

                    <!-- Status column with toggle -->
                    <template #is_active-cell="{ row }">
                        <div class="flex items-center gap-2">
                            <button @click="toggleStatus(row.original)"
                                class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                :class="row.original.is_active ? 'bg-green-600' : 'bg-gray-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                    :class="row.original.is_active ? 'translate-x-5' : 'translate-x-0.5'" />
                            </button>
                            <span class="text-sm" :class="row.original.is_active ? 'text-green-600' : 'text-gray-500'">
                                {{ row.original.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </template>

                    <!-- Updated At column -->
                    <template #updated_at-cell="{ row }">
                        <span class="text-sm text-muted-foreground">
                            {{ formatDate(row.original.updated_at) }}
                        </span>
                    </template>

                    <!-- Actions column -->
                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <Link :href="route('page-contents.edit', row.original.id)">
                                <AppDataTableActionButton icon="lucide:edit" tooltip="Edit" />
                            </Link>
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger"
                                @click="handleDelete(row.original)" />
                        </div>
                    </template>

                    <!-- Empty state -->
                    <template #empty>
                        <div class="text-center py-12">
                            <div class="flex justify-center mb-4">
                                <div class="p-4 bg-primary/10 rounded-full">
                                    <Icon icon="lucide:file-text" class="size-12 text-primary" />
                                </div>
                            </div>
                            <h3 class="text-lg font-medium mb-2">No page contents found</h3>
                            <p class="text-muted-foreground mb-4">Get started by creating your first page content</p>
                            <Link :href="route('page-contents.create')">
                                <AppButton label="Create Page Content" icon="radix-icons:plus-circled" />
                            </Link>
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>
    </AppContainer>
</template>