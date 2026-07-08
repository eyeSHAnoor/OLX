<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from "@/types";
import { usePage, router, Head } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { useBreadcrumb } from "@/composables/useBreadcrumb";

defineOptions({ layout: Layout });

interface ReferralData {
    id: number;
    name: string;
    email: string;
    referral_code: string | null;
    points_balance: number;
    referred_by: number | null;
    referrer_name: string | null;
    total_referrals_count: number;
    total_points_earned: number;
    points_per_referral: number;
    created_at: string;
}

const page = usePage<{
    referrers: PaginatedData<ReferralData>;
}>();

const referrers = computed(() => page.props.referrers);

// Initialize search filter
const { form, reset, isFiltered } = useSearchFilter(route("referrals.index"));

// Copy state tracking
const copiedId = ref<number | null>(null);

// Columns for data table
const columns = [
    { accessorKey: "name", header: "User", sortable: true, mobileTitle: "User" },
    {
        accessorKey: "referral_code",
        header: "Referral Code",
        sortable: true,
        mobileTitle: "Code",
    },
    {
        accessorKey: "total_referrals_count",
        header: "Referrals",
        sortable: true,
        mobileTitle: "Referrals",
    },
    {
        accessorKey: "points_balance",
        header: "Points Balance",
        sortable: true,
        mobileTitle: "Balance",
    },
    {
        accessorKey: "total_points_earned",
        header: "Earned from Referrals",
        sortable: true,
        mobileTitle: "Earned",
    },
    {
        accessorKey: "referred_by",
        header: "Referred By",
        sortable: false,
        mobileTitle: "Referred",
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
        { label: "Referrals", href: route("referrals.index") },
    ]);
});

// Copy referral link to clipboard
const copyReferralLink = async (code: string, id: number) => {
    try {
        const link = `${window.location.origin}/register?ref=${code}`;
        await navigator.clipboard.writeText(link);
        copiedId.value = id;
        setTimeout(() => (copiedId.value = null), 2000);
    } catch (err) {
        console.error("Failed to copy link:", err);
    }
};

// DELETE handler for removing referral code
async function handleRemoveReferral(user: ReferralData) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: "Remove Referral Code",
        description: `Are you sure you want to remove the referral code from "${user.name}"? This will cancel all their referral records.`,
        confirmText: "Remove",
        cancelText: "Cancel",
    });

    if (confirmed) {
        router.delete(route("users.referral.destroy", user.id), {
            preserveScroll: true,
        });
    }
}

// Navigate to edit referral
function handleEditReferral(user: ReferralData) {
    router.visit(route("users.referral.edit", user.id));
}

function handleViewReferral(user: ReferralData) {
    router.visit(route("users.referrals.show", user.id));
}

// Navigate to create referral
function handleCreateReferral() {
    router.visit(route("users.referral.create"));
}
</script>

<template>
    <AppContainer>

        <Head title="Referrals Management" />

        <PageHeading>
            <template #title>Referrals Management</template>
            <template #description>
                Assign referral codes and track user referral performance
            </template>
            <template #links>
                <AppButton label="Assign Referral Code" icon="radix-icons:plus-circled" @click="handleCreateReferral"
                    class="" />
            </template>
        </PageHeading>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <Card class="bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-blue-600">Users with Codes</p>
                            <p class="text-3xl font-bold text-blue-900">{{ referrers?.total || 0 }}</p>
                        </div>
                        <div class="p-3 bg-blue-200 rounded-full">
                            <Icon name="lucide:users" class="size-6 text-blue-700" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-green-50 to-green-100 border-green-200">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-green-600">Total Referrals</p>
                            <p class="text-3xl font-bold text-green-900">
                                {{
                                    referrers?.data?.reduce((sum, r) => sum + r.total_referrals_count, 0) ||
                                0
                                }}
                            </p>
                        </div>
                        <div class="p-3 bg-green-200 rounded-full">
                            <Icon name="lucide:user-plus" class="size-6 text-green-700" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-purple-600">Points from Referrals</p>
                            <p class="text-3xl font-bold text-purple-900">
                                {{
                                    referrers?.data
                                        ?.reduce((sum, r) => sum + r.total_points_earned, 0)
                                        ?.toLocaleString() || 0
                                }}
                            </p>
                        </div>
                        <div class="p-3 bg-purple-200 rounded-full">
                            <Icon name="lucide:gift" class="size-6 text-purple-700" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-orange-50 to-orange-100 border-orange-200">
                <CardContent class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-orange-600">Total Points Balance</p>
                            <p class="text-3xl font-bold text-orange-900">
                                {{
                                    referrers?.data
                                        ?.reduce((sum, r) => sum + r.points_balance, 0)
                                        ?.toLocaleString() || 0
                                }}
                            </p>
                        </div>
                        <div class="p-3 bg-orange-200 rounded-full">
                            <Icon name="lucide:dollar-sign" class="size-6 text-orange-700" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <Card class="mt-4">
            <CardContent>
                <AppDataTableNew :columns="columns" :data="referrers?.data"
                    search-placeholder="Search users by name, email, or referral code..."
                    v-model:search="form.filter.global" :pagination-data="referrers" v-model:perPage="form.perPage"
                    @resetFilter="reset()" :isFiltered="isFiltered">
                    <template #filters>
                        <!-- Add any additional filters here if needed -->
                    </template>

                    <template #name-cell="{ row }">
                        <div class="flex flex-col">
                            <span class="font-semibold">{{ row.original.name }}</span>
                            <span class="text-sm text-muted-foreground">{{ row.original.email }}</span>
                        </div>
                    </template>

                    <template #referral_code-cell="{ row }">
                        <div class="flex items-center gap-2">
                            <code v-if="row.original.referral_code"
                                class="text-xs bg-muted px-2 py-1 rounded font-mono">
                {{ row.original.referral_code }}
              </code>
                            <span v-else class="text-xs text-muted-foreground italic">No code</span>
                        </div>
                    </template>

                    <template #total_referrals_count-cell="{ row }">
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold"
                            :class="{
                                'bg-green-100 text-green-700': row.original.total_referrals_count > 0,
                                'bg-gray-100 text-gray-500': row.original.total_referrals_count === 0,
                            }">
                            <Icon name="lucide:user-plus" class="size-3" />
                            {{ row.original.total_referrals_count }}
                        </span>
                    </template>

                    <template #points_balance-cell="{ row }">
                        <span class="font-semibold" :class="{
                            'text-blue-600': row.original.points_balance > 0,
                            'text-muted-foreground': row.original.points_balance === 0,
                        }">
                            {{ row.original.points_balance.toLocaleString() }} pts
                        </span>
                    </template>

                    <template #total_points_earned-cell="{ row }">
                        <span class="font-bold" :class="{
                            'text-purple-600': row.original.total_points_earned > 0,
                            'text-muted-foreground': row.original.total_points_earned === 0,
                        }">
                            {{ row.original.total_points_earned.toLocaleString() }} pts
                        </span>
                    </template>

                    <template #referred_by-cell="{ row }">
                        <span v-if="row.original.referrer_name" class="text-sm font-medium">
                            {{ row.original.referrer_name }}
                        </span>
                        <span v-else class="text-xs text-muted-foreground"> Direct / Admin </span>
                    </template>

                    <template #actions-cell="{ row }">
                        <div class="flex items-center justify-end gap-2">
                            <AppDataTableActionButton v-if="row.original.referral_code"
                                :icon="copiedId === row.original.id ? 'lucide:check' : 'lucide:copy'" :tooltip="copiedId === row.original.id ? 'Link Copied!' : 'Copy Referral Link'
                                    " :class="copiedId === row.original.id ? 'text-green-600' : ''"
                                @click="copyReferralLink(row.original.referral_code, row.original.id)" />
                            <AppDataTableActionButton icon="lucide:users" tooltip="View Referrals"
                                @click="handleViewReferral(row.original)" />
                            <AppDataTableActionButton icon="lucide:edit" tooltip="Edit Referral Code & Points"
                                @click="handleEditReferral(row.original)" />
                            <AppDataTableActionButton v-if="row.original.referral_code" icon="lucide:trash-2"
                                tooltip="Remove Referral Code" variant="danger"
                                @click="handleRemoveReferral(row.original)" />
                        </div>
                    </template>
                </AppDataTableNew>
            </CardContent>
        </Card>
    </AppContainer>
</template>
