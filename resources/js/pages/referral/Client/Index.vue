<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from "@/types";
import { usePage, router, Head } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import Layout from "@/layouts/OlxLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { useBreadcrumb } from "@/composables/useBreadcrumb";

defineOptions({ layout: Layout });

interface DownlineUser {
    id: number;
    name: string;
    email: string;
    referral_code: string | null;
    points_balance: number;
    referrals_count: number;
    created_at: string;
}

const page = usePage<{
    codeAssignments: PaginatedData<DownlineUser>;
    directReferrals: PaginatedData<DownlineUser>;
    stats: {
        total_assignments: number;
        total_referrals: number;
        total_points_given: number;
        total_referrals_by_downline: number;
    };
    currentUserPoints: number;
}>();

const codeAssignments = computed(() => page.props.codeAssignments);
const directReferrals = computed(() => page.props.directReferrals);
const stats = computed(() => page.props.stats);
const currentUserPoints = computed(() => page.props.currentUserPoints);

// Active tab
const activeTab = ref<"assignments" | "referrals">("assignments");

// Columns for code assignments table
const assignmentsColumns = [
    { accessorKey: "name", header: "User", sortable: true },
    { accessorKey: "referral_code", header: "Referral Code", sortable: true },
    { accessorKey: "referrals_count", header: "Their Referrals", sortable: true },
    { accessorKey: "points_balance", header: "Points Balance", sortable: true },
    { accessorKey: "actions", header: "", sortable: false },
];

// Columns for direct referrals table
const referralsColumns = [
    { accessorKey: "name", header: "User", sortable: true },
    { accessorKey: "referral_code", header: "Referral Code", sortable: true },
    { accessorKey: "referrals_count", header: "Their Referrals", sortable: true },
    { accessorKey: "points_balance", header: "Points Balance", sortable: true },
    { accessorKey: "actions", header: "", sortable: false },
];

const { set, resetList } = useBreadcrumb();

onMounted(() => {
    resetList();
    set([
        { label: "Home", href: "/dashboard" },
        { label: "My Referral Team", href: route("downline-referrals.index") },
    ]);
});

// Copy my referral link
const copyMyReferralLink = async () => {
    try {
        const link = `${window.location.origin}/register?ref=${page.props.auth.user.referral_code}`;
        await navigator.clipboard.writeText(link);
        // Use toast notification instead of alert
        useToast().success("Referral link copied to clipboard!");
    } catch (err) {
        console.error(err);
        useToast().error("Failed to copy link");
    }
};

// Navigate to assign code/points
function handleAssignCode() {
    router.visit(route("downline-referrals.create"));
}

// Edit user
function handleEdit(user: DownlineUser) {
    router.visit(route("downline-referrals.edit", user.id));
}

// View user's referral tree
function handleViewTree(user: DownlineUser) {
    router.visit(route("users.referrals.tree", user.id));
}

// Revoke code
async function handleRevokeCode(user: DownlineUser) {
    const alert = useAlertDialog();
    const confirmed = await alert.show({
        title: "Revoke Referral Code",
        description: `Are you sure you want to revoke the referral code from "${user.name}"? Their own referrals will be cancelled.`,
        confirmText: "Revoke",
        cancelText: "Cancel",
    });
    if (confirmed) {
        router.delete(route("downline-referrals.destroy", user.id), {
            preserveScroll: true,
        });
    }
}

// Use toast composable
const useToast = () => {
    // Implement your toast notification system
    return {
        success: (msg: string) => console.log(msg),
        error: (msg: string) => console.error(msg),
    };
};

function handleViewReferral(user: ReferralData) {
    router.visit(route("users.referrals.show", user.id));
}

function handleViewReferralList(user: ReferralData) {
    router.visit(route("referral.tree", user.id));
}
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <Head title="My Referral Team" />

        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">My Referral Team</h1>
            <p class="text-sm text-gray-600 mt-1">
                Manage your code assignments and track your direct referrals
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
            <button @click="copyMyReferralLink"
                class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
                Copy My Referral Link
            </button>
            <button @click="handleAssignCode"
                class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Assign Code & Points
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Code Assignments -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code Assignments
                        </p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ stats.total_assignments }}
                        </p>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Direct Referrals -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Direct Referrals
                        </p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ stats.total_referrals }}
                        </p>
                    </div>
                    <div class="p-2 bg-green-50 rounded-lg">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Points Given -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Points Given
                        </p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ stats.total_points_given.toLocaleString() }}
                        </p>
                    </div>
                    <div class="p-2 bg-purple-50 rounded-lg">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- My Balance -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Points given per user
                        </p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">
                            {{ currentUserPoints.toLocaleString() }}
                        </p>
                    </div>
                    <div class="p-2 bg-orange-50 rounded-lg">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'assignments'" :class="[
                    'py-2 px-1 text-sm font-medium border-b-2 transition-colors',
                    activeTab === 'assignments'
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                ]">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        Code Assignments
                        <span class="ml-1 py-0.5 px-2 rounded-full text-xs bg-gray-100 text-gray-600">
                            {{ stats.total_assignments }}
                        </span>
                    </span>
                </button>
                <button @click="activeTab = 'referrals'" :class="[
                    'py-2 px-1 text-sm font-medium border-b-2 transition-colors',
                    activeTab === 'referrals'
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                ]">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Direct Referrals
                        <span class="ml-1 py-0.5 px-2 rounded-full text-xs bg-gray-100 text-gray-600">
                            {{ stats.total_referrals }}
                        </span>
                    </span>
                </button>
            </nav>
        </div>

        <!-- Code Assignments Tab -->
        <div v-if="activeTab === 'assignments'"
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden py-4">
            <div v-if="codeAssignments.data.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <p class="mt-4 text-lg font-medium text-gray-900">No code assignments yet</p>
                <p class="mt-1 text-sm text-gray-500">
                    Assign your referral code to users to help them earn points
                </p>
            </div>

            <AppDataTableNew v-else :columns="assignmentsColumns" :data="codeAssignments?.data"
                search-placeholder="Search by name, email or code..." :pagination-data="codeAssignments"
                class="[&_thead]:bg-gray-50 [&_th]:text-left [&_th]:text-xs [&_th]:font-medium [&_th]:text-gray-500 [&_th]:uppercase [&_th]:tracking-wider [&_td]:py-3 [&_td]:px-3 [&_tr]:border-b [&_tr]:border-gray-200">
                <template #name-cell="{ row }">
                    <div class="flex flex-col">
                        <span class="font-medium text-gray-900">{{ row.original.name }}</span>
                        <span class="text-sm text-gray-500">{{ row.original.email }}</span>
                    </div>
                </template>

                <template #referral_code-cell="{ row }">
                    <code v-if="row.original.referral_code"
                        class="text-xs bg-gray-100 px-2 py-1 rounded font-mono text-gray-700">
            {{ row.original.referral_code }}
          </code>
                    <span v-else class="text-xs text-gray-400 italic">No code</span>
                </template>

                <template #referrals_count-cell="{ row }">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                        'bg-green-100 text-green-800': row.original.referrals_count > 0,
                        'bg-gray-100 text-gray-500': row.original.referrals_count === 0,
                    }">
                        {{ row.original.referrals_count }}
                    </span>
                </template>

                <template #points_balance-cell="{ row }">
                    <span class="font-medium" :class="{
                        'text-blue-600': row.original.points_balance > 0,
                        'text-gray-500': row.original.points_balance === 0,
                    }">
                        {{ row.original.points_balance.toLocaleString() }} pts
                    </span>
                </template>

                <template #actions-cell="{ row }">
                    <div class="flex items-center justify-end gap-2">
                        <button @click="handleViewReferral(row.original)"
                            class="p-1.5 text-gray-400 hover:text-blue-600 rounded hover:bg-blue-50 transition-colors"
                            title="View Tree">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </button>
                        <button @click="handleEdit(row.original)"
                            class="p-1.5 text-gray-400 hover:text-blue-600 rounded hover:bg-blue-50 transition-colors"
                            title="Edit Code & Points">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <button v-if="row.original.referral_code" @click="handleRevokeCode(row.original)"
                            class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50 transition-colors"
                            title="Revoke Code">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </template>
            </AppDataTableNew>
        </div>

        <!-- Direct Referrals Tab -->
        <div v-if="activeTab === 'referrals'"
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden py-4">
            <div v-if="directReferrals.data.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p class="mt-4 text-lg font-medium text-gray-900">No direct referrals yet</p>
                <p class="mt-1 text-sm text-gray-500">
                    Share your referral link to get users to sign up through you
                </p>
            </div>

            <AppDataTableNew v-else :columns="referralsColumns" :data="directReferrals?.data"
                search-placeholder="Search by name, email or code..." :pagination-data="directReferrals"
                class="[&_thead]:bg-gray-50 [&_th]:text-left [&_th]:text-xs [&_th]:font-medium [&_th]:text-gray-500 [&_th]:uppercase [&_th]:tracking-wider [&_td]:py-3 [&_td]:px-3 [&_tr]:border-b [&_tr]:border-gray-200">
                <template #name-cell="{ row }">
                    <div class="flex flex-col">
                        <span class="font-medium text-gray-900">{{ row.original.name }}</span>
                        <span class="text-sm text-gray-500">{{ row.original.email }}</span>
                    </div>
                </template>

                <template #referral_code-cell="{ row }">
                    <code v-if="row.original.referral_code"
                        class="text-xs bg-gray-100 px-2 py-1 rounded font-mono text-gray-700">
            {{ row.original.referral_code }}
          </code>
                    <span v-else class="text-xs text-gray-400 italic">No code</span>
                </template>

                <template #referrals_count-cell="{ row }">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                        'bg-green-100 text-green-800': row.original.referrals_count > 0,
                        'bg-gray-100 text-gray-500': row.original.referrals_count === 0,
                    }">
                        {{ row.original.referrals_count }}
                    </span>
                </template>

                <template #points_balance-cell="{ row }">
                    <span class="font-medium" :class="{
                        'text-blue-600': row.original.points_balance > 0,
                        'text-gray-500': row.original.points_balance === 0,
                    }">
                        {{ row.original.points_balance.toLocaleString() }} pts
                    </span>
                </template>

                <template #actions-cell="{ row }">
                    <div class="flex items-center justify-end gap-2">
                        <button @click="handleViewReferralList(row.original)"
                            class="p-1.5 text-gray-400 hover:text-blue-600 rounded hover:bg-blue-50 transition-colors"
                            title="View Tree">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </button>
                    </div>
                </template>
            </AppDataTableNew>
        </div>
    </div>
</template>
