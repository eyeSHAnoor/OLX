<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from "@/types";
import { usePage } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { router } from "@inertiajs/vue3";

defineOptions({ layout: Layout });

interface PageProps extends InertiaPageProps {
    periods: PaginatedData<App.Data.GiftPeriodData>;
    gifts: App.Data.GiftData[];
}

const page = usePage<PageProps>();
// console.log("Page Props:", page.props); // Debugging line to check the structure of page.props
const periods = computed(() => page.props.periods);
const gifts = computed(() => page.props.gifts);

// Use search filter
const { form, reset, isFiltered } = useSearchFilter(route("gift-campaigns.index"));

// Columns for data table
const columns = [
    {
        accessorKey: "name",
        header: "Campaign Name",
        sortable: true,
        mobileTitle: "Name",
        cellClass: "font-medium",
    },
    {
        accessorKey: "gifts",
        header: "Gifts",
        sortable: false,
        mobileTitle: "Gifts",
    },
    {
        accessorKey: "period",
        header: "Period",
        sortable: false,
        mobileTitle: "Period",
    },
    {
        accessorKey: "status",
        header: "Status",
        sortable: true,
        mobileTitle: "Status",
    },
    {
        accessorKey: "assignments_count",
        header: "Assignments",
        sortable: true,
        mobileTitle: "Assignments",
    },
    {
        accessorKey: "progress",
        header: "Progress",
        sortable: false,
        mobileTitle: "Progress",
    },
    {
        accessorKey: "created_at",
        header: "Created",
        sortable: true,
        mobileTitle: "Created",
    },
    {
        accessorKey: "actions",
        header: "",
        sortable: false,
        mobileTitle: "Actions",
    },
];

// Modal and breadcrumbs
const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: "Home", href: "/dashboard" },
        { label: "Gift Campaigns", href: route("gift-campaigns.index") },
    ]);
});

// DELETE handler
async function handleDeleteCampaign(period: App.Data.GiftPeriodData) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: "Delete Campaign",
        description: `Are you sure you want to delete "${period.name}"? This will remove all assignments and cannot be undone.`,
        confirmText: "Delete",
        cancelText: "Cancel",
    });

    if (confirmed) {
        router.delete(route("gift-campaigns.destroy", period.id), {
            preserveScroll: true,
        });
    }
}

// Format date
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

// Get campaign status
const getCampaignStatus = (period: App.Data.GiftPeriodData) => {
    const now = new Date();
    const startDate = new Date(period.start_date);
    const endDate = new Date(period.end_date);

    if (!period.is_active) return "inactive";
    if (now < startDate) return "upcoming";
    if (now > endDate) return "ended";
    return "active";
};

const getStatusBadge = (status: string) => {
    const badges = {
        active: "bg-green-100 text-green-800",
        upcoming: "bg-blue-100 text-blue-800",
        ended: "bg-gray-100 text-gray-800",
        inactive: "bg-red-100 text-red-800",
    };
    return badges[status] || "bg-gray-100 text-gray-800";
};

// Calculate assignment progress
const getProgress = (period: any) => {
    const totalAllocated =
        period.campaign_gifts?.reduce(
            (sum: number, cg: any) => sum + cg.allocated_quantity,
            0
        ) || 0;
    const totalAssigned =
        totalAllocated -
        (period.campaign_gifts?.reduce(
            (sum: number, cg: any) => sum + cg.remaining_quantity,
            0
        ) || 0);

    if (totalAllocated === 0) return 0;
    return Math.round((totalAssigned / totalAllocated) * 100);
};

const clearFilter = (filterKey: string) => {
    form.filter[filterKey] = "";
    form.get(route("gift-campaigns.index"), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppContainer>

        <Head title="Gift Campaigns" />

        <PageHeading>
            <template #title>Gift Campaigns</template>
            <template #subtitle>
                Manage gift distribution campaigns for loyal subscribers
            </template>
            <template #links>
                <Button as-child size="sm">
                    <Link :href="route('gift-campaigns.create')">
                    <Icon icon="radix-icons:plus-circled" class="size-4" /> New Campaign
                    </Link>
                </Button>
                <Button as-child size="sm">
                    <Link :href="route('gifts.index')">
                    <Icon icon="radix-icons:plus-circled" class="size-4" /> Add GIFTS
                    </Link>
                </Button>
            </template>
        </PageHeading>

        <Card class="mt-4">
            <CardContent>
                <AppDataTableNew :columns="columns" :data="periods?.data"
                    search-placeholder="Search campaigns by name..." v-model:search="form.filter.global"
                    :pagination-data="periods" v-model:perPage="form.perPage" @resetFilter="reset()"
                    :isFiltered="isFiltered">
                    <template #filters>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Status Filter -->
                        </div>

                        <!-- Active Filters Display -->
                        <div v-if="isFiltered" class="flex flex-wrap gap-2 mb-4">
                            <div v-if="form.filter.is_active === '1'"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                Status: Active
                                <button @click="clearFilter('is_active')" class="ml-1 hover:text-green-600">
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>
                            </div>
                            <div v-if="form.filter.is_active === '0'"
                                class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm">
                                Status: Inactive
                                <button @click="clearFilter('is_active')" class="ml-1 hover:text-red-600">
                                    <Icon icon="lucide:x" class="size-3" />
                                </button>
                            </div>
                        </div>
                    </template>

                    <template #name-cell="{ row }">
                        <div class="flex items-center gap-2">
                            <div
                                class="size-10 flex-shrink-0 rounded-full bg-primary/10 flex items-center justify-center">
                                <Icon icon="lucide:gift" class="size-5 text-primary" />
                            </div>
                            <div>
                                <span class="font-medium block">{{ row.original.name }}</span>
                            </div>
                        </div>
                    </template>

                    <template #gifts-cell="{ row }">
                        <div class="flex flex-wrap gap-1">
                            <span v-for="gift in row.original.campaign_gifts" :key="gift.id"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                <Icon icon="lucide:gift" class="size-3 mr-1" />
                                {{ gift.gift?.name }} ({{ gift.allocated_quantity }})
                            </span>
                            <span v-if="!row.original.campaign_gifts?.length"
                                class="text-sm text-muted-foreground italic">
                                No gifts
                            </span>
                        </div>
                    </template>

                    <template #period-cell="{ row }">
                        <div class="text-sm">
                            <div class="flex items-center gap-1">
                                <Icon icon="lucide:calendar" class="size-3 text-muted-foreground" />
                                <span>{{ formatDate(row.original.start_date) }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-muted-foreground">
                                <Icon icon="lucide:arrow-right" class="size-3" />
                                <span>{{ formatDate(row.original.end_date) }}</span>
                            </div>
                        </div>
                    </template>

                    <template #status-cell="{ row }">
                        <span :class="getStatusBadge(getCampaignStatus(row.original))"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full">
                            <span :class="{
                                'bg-green-500': getCampaignStatus(row.original) === 'active',
                                'bg-blue-500': getCampaignStatus(row.original) === 'upcoming',
                                'bg-gray-500': getCampaignStatus(row.original) === 'ended',
                                'bg-red-500': getCampaignStatus(row.original) === 'inactive',
                            }" class="size-2 rounded-full mr-1.5">
                            </span>
                            {{
                                getCampaignStatus(row.original).charAt(0).toUpperCase() +
                                getCampaignStatus(row.original).slice(1)
                            }}
                        </span>
                    </template>

                    <template #assignments_count-cell="{ row }">
                        <div class="flex items-center gap-1">
                            <Icon icon="lucide:users" class="size-3 text-muted-foreground" />
                            <span>{{ row.original.assignments_count || 0 }}</span>
                        </div>
                    </template>

                    <template #progress-cell="{ row }">
                        <div class="w-full max-w-[150px]">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium">{{ getProgress(row.original) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full transition-all"
                                    :style="{ width: getProgress(row.original) + '%' }"></div>
                            </div>
                        </div>
                    </template>

                    <template #created_at-cell="{ row }">
                        <DateTimeCell :value="row.original.created_at" />
                    </template>

                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton icon="lucide:users" tooltip="View Eligible Users" @click="
                                router.visit(route('gift-campaigns.eligible-users', row.original))
                                " />
                            <AppDataTableActionButton icon="lucide:list" tooltip="View Assignments"
                                @click="router.visit(route('gift-campaigns.assignments', row.original))" />
                            <AppDataTableActionButton icon="lucide:edit" tooltip="Edit Campaign"
                                @click="router.visit(route('gift-campaigns.edit', row.original))" />
                            <AppDataTableActionButton icon="lucide:trash-2" tooltip="Delete Campaign" variant="danger"
                                @click="handleDeleteCampaign(row.original)" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>
    </AppContainer>
</template>
