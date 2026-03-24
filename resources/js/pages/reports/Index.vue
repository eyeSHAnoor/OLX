<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types';
import { usePage, router, Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import Layout from '@/layouts/AppLayout.vue';
import { useAlertDialog } from '@/composables/useAlertDialog';
import { useBreadcrumb } from '@/composables/useBreadcrumb';
import useModal from '@/composables/useModal';
import { Icon } from '@iconify/vue'
import ReportDetailsModal from './_partials/ReportDetailsModal.vue';

defineOptions({ layout: Layout });

// Props and Page setup
const page = usePage<{
    reports: PaginatedData<any>;
    filters: any;
    statuses: Record<string, string>;
    reasons: Record<string, string>;
}>();

const reports = computed(() => page.props.reports);
const statuses = computed(() => page.props.statuses);
const reasons = computed(() => page.props.reasons);

// Initialize search filter
const { form, reset, isFiltered } = useSearchFilter(route('reports.index'));

// Ensure form has proper structure
if (!form.value) {
    form.value = {
        filter: {
            search: '',
            status: 'all',
            reason: 'all',
            date_from: '',
            date_to: ''
        },
        perPage: 15
    };
}

// Columns for data table
const columns = [
    { accessorKey: 'id', header: 'ID', sortable: true, mobileTitle: 'ID' },
    { accessorKey: 'reported_user', header: 'Reported User', sortable: false, mobileTitle: 'Reported User' },
    { accessorKey: 'reporter', header: 'Reported By', sortable: false, mobileTitle: 'Reporter' },
    { accessorKey: 'reason', header: 'Reason', sortable: true, mobileTitle: 'Reason' },
    { accessorKey: 'status', header: 'Status', sortable: true, mobileTitle: 'Status' },
    { accessorKey: 'created_at', header: 'Date', sortable: true, mobileTitle: 'Date' },
    { accessorKey: 'actions', header: '', sortable: false, mobileTitle: 'Actions' },
];

// Modal state
const { handleShowModal, showModal, selectedItem } = useModal();
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: 'Dashboard', href: '/dashboard' },
        { label: 'Reports', href: route('reports.index') }
    ]);
});

// Stats calculations
const totalReports = computed(() => reports.value?.total || 0);
const pendingReports = computed(() =>
    reports.value?.data?.filter((r: any) => r.status === 'pending').length || 0
);
const resolvedReports = computed(() =>
    reports.value?.data?.filter((r: any) => r.status === 'resolved').length || 0
);
const scamReports = computed(() =>
    reports.value?.data?.filter((r: any) => r.reason === 'scam').length || 0
);

// Helper functions
const getStatusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'reviewed': 'bg-blue-100 text-blue-800',
        'resolved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getReasonBadgeClass = (reason: string) => {
    const classes: Record<string, string> = {
        'scam': 'bg-red-100 text-red-800',
        'spam': 'bg-orange-100 text-orange-800',
        'abusive': 'bg-purple-100 text-purple-800',
        'fake_listing': 'bg-pink-100 text-pink-800',
        'inappropriate': 'bg-yellow-100 text-yellow-800',
        'other': 'bg-gray-100 text-gray-800',
    };
    return classes[reason] || 'bg-gray-100 text-gray-800';
};

const getReasonIcon = (reason: string) => {
    const icons: Record<string, string> = {
        'scam': 'lucide:alert-triangle',
        'spam': 'lucide:mail',
        'abusive': 'lucide:alert-octagon',
        'fake_listing': 'lucide:copy-x',
        'inappropriate': 'lucide:ban',
        'other': 'lucide:help-circle',
    };
    return icons[reason] || 'lucide:flag';
};

const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Get initials for avatar
const getInitials = (name: string) => {
    if (!name) return 'U';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

// Get avatar color
const getAvatarColor = (name: string) => {
    const colors = [
        'bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500',
        'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500'
    ];
    if (!name) return colors[0];
    const index = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) % colors.length;
    return colors[index];
};

// Delete handler
async function handleDeleteReport(report: any) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: 'Delete Report',
        description: `Are you sure you want to delete report #${report.id}? This action cannot be undone.`,
        confirmText: 'Delete',
        cancelText: 'Cancel',
        variant: 'danger'
    });

    if (confirmed) {
        router.delete(route('admin.reports.destroy', report.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: show success message
            }
        });
    }
}

// Bulk update handler
async function handleBulkUpdate() {
    const selectedIds = selectedReports.value.map(r => r.id);
    if (selectedIds.length === 0) return;

    const alert = useAlertDialog();
    const status = await alert.show({
        title: 'Update Reports',
        description: `Select a status to apply to ${selectedIds.length} selected reports:`,
        confirmText: 'Update',
        cancelText: 'Cancel',
        variant: 'info',
        input: 'select',
        inputOptions: statuses.value,
        inputValue: 'reviewed'
    });

    if (status) {
        router.post(route('admin.reports.bulk-update'), {
            ids: selectedIds,
            status: status
        }, {
            preserveScroll: true
        });
    }
}

// Selected reports for bulk actions
const selectedReports = ref<any[]>([]);
</script>

<template>
    <AppContainer>

        <Head title="Reports" />

        <PageHeading>
            <template #title>User Reports</template>
            <template #description>Manage and respond to user reports</template>
            <template #links>
                <AppButton v-if="selectedReports.length > 0" label="Bulk Update" icon="lucide:layers" variant="outline"
                    @click="handleBulkUpdate" />
            </template>
        </PageHeading>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Reports</p>
                            <p class="text-2xl font-bold">{{ totalReports }}</p>
                        </div>
                        <div class="p-3 bg-primary/10 rounded-full">
                            <Icon icon="lucide:flag" class="size-6 text-primary" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ pendingReports }}</p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <Icon icon="lucide:clock" class="size-6 text-yellow-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Resolved</p>
                            <p class="text-2xl font-bold text-green-600">{{ resolvedReports }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <Icon icon="lucide:check-circle" class="size-6 text-green-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Scam Reports</p>
                            <p class="text-2xl font-bold text-red-600">{{ scamReports }}</p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <Icon icon="lucide:alert-triangle" class="size-6 text-red-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="mt-6">
            <CardContent class="p-0">
                <AppDataTableNew :columns="columns" :data="reports?.data"
                    search-placeholder="Search by user, reporter, reason..." v-model:search="form.filter.search"
                    :pagination-data="reports" v-model:perPage="form.perPage" @resetFilter="reset()"
                    :isFiltered="isFiltered" v-model:selected="selectedReports" :selectable="true">
                    <template #filters>
                        <!-- Status Filter -->
                        <SelectInput v-model="form.filter.status" placeholder="All Status" class="w-36">
                            <SelectContent>
                                <SelectItem :value="'all'">All Status</SelectItem>
                                <SelectItem v-for="(label, value) in statuses" :key="value" :value="value">
                                    {{ label }}
                                </SelectItem>
                            </SelectContent>
                        </SelectInput>

                        <!-- Reason Filter -->
                        <SelectInput v-model="form.filter.reason" placeholder="All Reasons" class="w-40">
                            <SelectContent>
                                <SelectItem :value="'all'">All Reasons</SelectItem>
                                <SelectItem v-for="(label, value) in reasons" :key="value" :value="value">
                                    {{ label }}
                                </SelectItem>
                            </SelectContent>
                        </SelectInput>

                        <!-- Date From -->
                        <input type="date" v-model="form.filter.date_from"
                            class="w-36 px-3 py-1.5 text-sm border rounded-md" placeholder="From Date" />

                        <!-- Date To -->
                        <input type="date" v-model="form.filter.date_to"
                            class="w-36 px-3 py-1.5 text-sm border rounded-md" placeholder="To Date" />
                    </template>

                    <!-- ID Column -->
                    <template #id-cell="{ row }">
                        <span class="font-mono text-sm">#{{ row.original.id }}</span>
                    </template>

                    <!-- Reported User Column -->
                    <template #reported_user-cell="{ row }">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full text-white text-xs font-semibold"
                                :class="getAvatarColor(row.original.reported_user?.name)">
                                {{ getInitials(row.original.reported_user?.name) }}
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ row.original.reported_user?.name || 'N/A' }}</p>
                                <p class="text-xs text-muted-foreground">{{ row.original.reported_user?.email }}</p>
                            </div>
                        </div>
                    </template>

                    <!-- Reporter Column -->
                    <template #reporter-cell="{ row }">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full text-white text-xs font-semibold"
                                :class="getAvatarColor(row.original.reporter?.name)">
                                {{ getInitials(row.original.reporter?.name) }}
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ row.original.reporter?.name || 'N/A' }}</p>
                                <p class="text-xs text-muted-foreground">{{ row.original.reporter?.email }}</p>
                            </div>
                        </div>
                    </template>

                    <!-- Reason Column -->
                    <template #reason-cell="{ row }">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium"
                            :class="getReasonBadgeClass(row.original.reason)">
                            <Icon :icon="getReasonIcon(row.original.reason)" class="size-3" />
                            {{ reasons?.[row.original.reason] || row.original.reason }}
                        </span>
                    </template>

                    <!-- Status Column -->
                    <template #status-cell="{ row }">
                        <span class="px-2 py-1 rounded-full text-xs font-medium"
                            :class="getStatusBadgeClass(row.original.status)">
                            {{ statuses?.[row.original.status] || row.original.status }}
                        </span>
                    </template>

                    <!-- Date Column -->
                    <template #created_at-cell="{ row }">
                        <div class="text-sm">
                            {{ formatDate(row.original.created_at) }}
                        </div>
                    </template>

                    <!-- Actions Column -->
                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton icon="lucide:eye" tooltip="View Details"
                                @click="handleShowModal(row.original)" />
                            <AppDataTableActionButton icon="lucide:message-circle" tooltip="Respond" variant="success"
                                @click="handleShowModal(row.original)" />
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete" variant="danger"
                                @click="handleDeleteReport(row.original)" />
                        </div>
                    </template>

                    <!-- Empty State -->
                    <template #empty>
                        <div class="text-center py-12">
                            <div class="flex justify-center mb-4">
                                <div class="p-4 bg-primary/10 rounded-full">
                                    <Icon icon="lucide:flag" class="size-12 text-primary" />
                                </div>
                            </div>
                            <h3 class="text-lg font-medium mb-2">No reports found</h3>
                            <p class="text-muted-foreground mb-4">There are no reports matching your criteria</p>
                            <AppButton label="Clear Filters" icon="lucide:x" @click="reset()" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>

        <!-- Report Details Modal -->
        <ReportDetailsModal v-model="showModal" :report="selectedItem" :statuses="statuses" :reasons="reasons"
            @responded="() => router.reload()" />
    </AppContainer>
</template>