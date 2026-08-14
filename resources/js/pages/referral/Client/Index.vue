<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from "@/types";
import { usePage, router, Head } from "@inertiajs/vue3";
import { computed, onMounted, ref, watch } from "vue";
import Layout from "@/layouts/OlxLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import { useBreadcrumb } from "@/composables/useBreadcrumb";
import WithdrawModal from "../_partials/WithdrawModal.vue";
import ImageGalleryModal from "../_partials/ImageGalleryModal.vue";
import { useTheme } from "@/composables/useTheme";

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

interface WithdrawalHistory {
  id: number;
  requested_amount: number;
  status: string;
  proof_images: string[];
  created_at: string;
  transaction_id: string | null;
  payment_method: string | null;
}

const page = usePage<{
  codeAssignments: PaginatedData<DownlineUser>;
  directReferrals: PaginatedData<DownlineUser>;
  stats: {
    total_assignments: number;
    total_referrals: number;
    total_points_given: number;
    total_referrals_by_downline: number;
    total_earned: number;
    total_withdrawn: number;
    available_points: number;
    pending_points: number;
    has_pending_withdrawal: boolean;
  };
  currentUserPoints: number;
  canAssignCodes: boolean;
  auth: {
    user: {
      referral_code: string;
      name: string;
    };
  };
  withdrawalHistory: PaginatedData<WithdrawalHistory>;
}>();

const { theme } = useTheme();

console.log(page.props);
const showWithdrawModal = ref(false);
const showGalleryModal = ref(false);
const selectedWithdrawal = ref<WithdrawalHistory | null>(null);

function openWithdrawModal() {
  showWithdrawModal.value = true;
}

function handleWithdrawalSuccess() {
  router.reload({ only: ["currentUserPoints", "stats", "withdrawalHistory"] });
}

const codeAssignments = computed(() => page.props.codeAssignments);
const directReferrals = computed(() => page.props.directReferrals);
const stats = computed(() => page.props.stats);
const currentUserPoints = computed(() => page.props.currentUserPoints);
const canAssignCodes = computed(() => page.props.canAssignCodes ?? false);
const withdrawalHistory = computed(() => page.props.withdrawalHistory);

// Active tab
const activeTab = ref<"assignments" | "referrals" | "withdrawals">(
  canAssignCodes.value ? "assignments" : "referrals"
);

// Watch for changes in canAssignCodes to update active tab
watch(canAssignCodes, (newVal) => {
  if (!newVal && activeTab.value === "assignments") {
    activeTab.value = "referrals";
  }
});

// Columns for code assignments table
const assignmentsColumns = [
  { accessorKey: "name", header: "User", sortable: true },
  { accessorKey: "referral_code", header: "Referral Code", sortable: true },
  // { accessorKey: "referrals_count", header: "Their Referrals", sortable: true },
  { accessorKey: "points_balance", header: "Points Balance", sortable: true },
  { accessorKey: "actions", header: "", sortable: false },
];

// Columns for direct referrals table
const referralsColumns = [
  { accessorKey: "name", header: "User", sortable: true },
  { accessorKey: "referral_code", header: "Referral Code", sortable: true },
  // { accessorKey: "referrals_count", header: "Their Referrals", sortable: true },
  { accessorKey: "points_balance", header: "Points Balance", sortable: true },
  { accessorKey: "actions", header: "", sortable: false },
];

// Columns for withdrawal history
const withdrawalColumns = [
  { accessorKey: "requested_amount", header: "Amount", sortable: true },
  { accessorKey: "status", header: "Status", sortable: true },
  { accessorKey: "proof_images", header: "Proof Images", sortable: false },
  { accessorKey: "created_at", header: "Date", sortable: true },
  { accessorKey: "transaction_id", header: "Transaction ID", sortable: false },
];

const { set, resetList } = useBreadcrumb();

onMounted(() => {
  resetList();
  set([
    { label: "Home", href: "/dashboard" },
    { label: "My Referral Team", href: route("downline-referrals.index") },
  ]);
});

const copyMessage = ref<string | null>(null);
const copyMessageTimeout = ref<number | null>(null);

// Copy my referral link
const copyMyReferralLink = async () => {
  try {
    const link = `${window.location.origin}/register?ref=${page.props.auth.user.referral_code}`;
    await navigator.clipboard.writeText(link);

    // Show success message
    copyMessage.value = "Link copied!";

    // Clear any existing timeout
    if (copyMessageTimeout.value) {
      clearTimeout(copyMessageTimeout.value);
    }

    // Auto-hide message after 3 seconds
    copyMessageTimeout.value = window.setTimeout(() => {
      copyMessage.value = null;
    }, 3000);
  } catch (err) {
    console.error(err);
    copyMessage.value = "Failed to copy link";
    setTimeout(() => {
      copyMessage.value = null;
    }, 3000);
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
  return {
    success: (msg: string) => console.log(msg),
    error: (msg: string) => console.error(msg),
  };
};

function handleViewReferral(user: DownlineUser) {
  router.visit(route("users.referrals.show", user.id));
}

function handleViewReferralList(user: DownlineUser) {
  router.visit(route("referral.tree", user.id));
}

// Copy user's referral link
const copyUserReferralLink = async (user: DownlineUser) => {
  if (!user.referral_code) {
    useToast().error("This user doesn't have a referral code");
    return;
  }

  try {
    const link = `${window.location.origin}/register?ref=${user.referral_code}`;
    await navigator.clipboard.writeText(link);
    useToast().success(`Referral link for "${user.name}" copied to clipboard!`);
  } catch (err) {
    console.error(err);
    useToast().error("Failed to copy link");
  }
};

// Open gallery with withdrawal images
function openGalleryModal(withdrawal: WithdrawalHistory) {
  if (!withdrawal.proof_images || withdrawal.proof_images.length === 0) {
    useToast().error("No images available for this withdrawal");
    return;
  }
  selectedWithdrawal.value = withdrawal;
  showGalleryModal.value = true;
}

// Get status label
const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    pending: "Pending",
    approved: "Approved",
    completed: "Completed",
    rejected: "Rejected",
    active: "Active",
  };
  return labels[status] || status;
};

// Get status color
const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    pending: "bg-yellow-100 text-yellow-800",
    approved: "bg-blue-100 text-blue-800",
    completed: "bg-green-100 text-green-800",
    rejected: "bg-red-100 text-red-800",
    active: "bg-gray-100 text-gray-800",
  };
  return colors[status] || "bg-gray-100 text-gray-800";
};

// Format date
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

// Get tab count
const getTabCount = (tab: string) => {
  if (tab === "assignments") return stats.total_assignments;
  if (tab === "referrals") return stats.total_referrals;
  if (tab === "withdrawals") return withdrawalHistory.value?.total || 0;
  return 0;
};
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <Head title="My Referral Team" />

    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold" :class="theme.text">My Referral Team</h1>
      <p class="text-sm mt-1" :class="theme.textMuted">
        Manage your code assignments, track your direct referrals, and view withdrawal
        history
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-3 mb-6 flex-wrap">
      <button @click="copyMyReferralLink"
        class="inline-flex items-center justify-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors relative"
        :class="[theme.card, theme.border, theme.text, theme.hover]">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
        </svg>
        <span>{{ copyMessage ? copyMessage : "Copy My Referral Link" }}</span>
      </button>

      <!-- Withdraw Button -->
      <button @click="openWithdrawModal"
        class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
        :class="theme.button" :disabled="stats.has_pending_withdrawal || currentUserPoints < 100">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        Withdraw Points
        <span v-if="stats.has_pending_withdrawal" class="ml-2 text-xs">(Pending)</span>
      </button>

      <!-- Only show Assign Code & Points button if user can assign codes -->
      <button v-if="canAssignCodes" @click="handleAssignCode"
        class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
        :class="theme.button">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Assign Code & Points
      </button>
    </div>

    <!-- Stats Cards -->
    <div :class="[
      'grid gap-4 mb-6',
      canAssignCodes
        ? 'grid-cols-2 lg:grid-cols-4'
        : 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    ]">
      <!-- Withdrawn Points Card -->
      <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
        :class="[theme.card, theme.border]">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
              Withdrawn Points
            </p>
            <p class="text-2xl font-bold mt-1" :class="theme.text">
              {{ stats.total_withdrawn?.toLocaleString() || 0 }}
            </p>
            <p v-if="stats.pending_points > 0" class="text-xs text-yellow-600 mt-1">
              {{ stats.pending_points.toLocaleString() }} points pending
            </p>
          </div>
          <div class="p-2 rounded-lg" :class="theme.bgLight">
            <svg class="w-5 h-5" :class="theme.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Direct Referrals Card -->
      <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
        :class="[theme.card, theme.border]">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
              Direct Referrals
            </p>
            <p class="text-2xl font-bold mt-1" :class="theme.text">
              {{ stats.total_referrals }}
            </p>
          </div>
          <div class="p-2 rounded-lg" :class="theme.bgLight">
            <svg class="w-5 h-5" :class="theme.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Total Earned Card -->
      <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
        :class="[theme.card, theme.border]">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
              Total Earned
            </p>
            <p class="text-2xl font-bold mt-1" :class="theme.text">
              {{ stats.total_earned?.toLocaleString() || 0 }}
            </p>
          </div>
          <div class="p-2 rounded-lg" :class="theme.bgLight">
            <svg class="w-5 h-5" :class="theme.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Points Available Card -->
      <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
        :class="[theme.card, theme.border]">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
              Points Available
            </p>
            <p class="text-2xl font-bold mt-1" :class="theme.text">
              {{ currentUserPoints.toLocaleString() }}
            </p>
            <p v-if="stats.has_pending_withdrawal" class="text-xs text-yellow-600 mt-1">
              ⏳ Withdrawal in progress
            </p>
          </div>
          <div class="p-2 rounded-lg" :class="theme.bgLight">
            <svg class="w-5 h-5" :class="theme.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="border-b mb-6" :class="theme.border">
      <nav class="-mb-px flex space-x-8" aria-label="Tabs">
        <!-- Code Assignments Tab (only if user can assign codes) -->
        <button v-if="canAssignCodes" @click="activeTab = 'assignments'" :class="[
          'py-2 px-1 text-sm font-medium border-b-2 transition-colors whitespace-nowrap',
          activeTab === 'assignments'
            ? `border-blue-500 ${theme.textAccent}`
            : `border-transparent ${theme.textMuted} ${theme.hover} hover:border-gray-300`,
        ]">
          <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Code Assignments
            <span class="ml-1 py-0.5 px-2 rounded-full text-xs" :class="[theme.bgLight, theme.textMuted]">
              {{ stats.total_assignments }}
            </span>
          </span>
        </button>

        <!-- Direct Referrals Tab -->
        <button @click="activeTab = 'referrals'" :class="[
          'py-2 px-1 text-sm font-medium border-b-2 transition-colors whitespace-nowrap',
          activeTab === 'referrals'
            ? `border-blue-500 ${theme.textAccent}`
            : `border-transparent ${theme.textMuted} ${theme.hover} hover:border-gray-300`,
        ]">
          <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Direct Referrals
            <span class="ml-1 py-0.5 px-2 rounded-full text-xs" :class="[theme.bgLight, theme.textMuted]">
              {{ stats.total_referrals }}
            </span>
          </span>
        </button>

        <!-- Withdrawal History Tab -->
        <button @click="activeTab = 'withdrawals'" :class="[
          'py-2 px-1 text-sm font-medium border-b-2 transition-colors whitespace-nowrap',
          activeTab === 'withdrawals'
            ? `border-blue-500 ${theme.textAccent}`
            : `border-transparent ${theme.textMuted} ${theme.hover} hover:border-gray-300`,
        ]">
          <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Withdrawal History
            <span class="ml-1 py-0.5 px-2 rounded-full text-xs" :class="[theme.bgLight, theme.textMuted]">
              {{ withdrawalHistory?.total || 0 }}
            </span>
          </span>
        </button>
      </nav>
    </div>

    <!-- Code Assignments Tab Content -->
    <div v-if="canAssignCodes && activeTab === 'assignments'" class="rounded-lg shadow-sm border overflow-hidden py-4"
      :class="[theme.card, theme.border]">
      <div v-if="codeAssignments.data.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto" :class="theme.textMuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        <p class="mt-4 text-lg font-medium" :class="theme.text">No code assignments yet</p>
        <p class="mt-1 text-sm" :class="theme.textMuted">
          Assign your referral code to users to help them earn points
        </p>
      </div>

      <AppDataTableNew v-else :columns="assignmentsColumns" :data="codeAssignments?.data"
        search-placeholder="Search by name, email or code..." :pagination-data="codeAssignments"
        :class="`[&_thead]:${theme.bgLight} [&_th]:text-left [&_th]:text-xs [&_th]:font-medium [&_th]:${theme.textMuted} [&_th]:uppercase [&_th]:tracking-wider [&_td]:py-3 [&_td]:px-3 [&_tr]:border-b [&_tr]:${theme.border}`">
        <template #name-cell="{ row }">
          <div class="flex flex-col">
            <span class="font-medium" :class="theme.text">{{ row.original.name }}</span>
            <span class="text-sm" :class="theme.textMuted">{{ row.original.email }}</span>
          </div>
        </template>

        <template #referral_code-cell="{ row }">
          <code v-if="row.original.referral_code" class="text-xs px-2 py-1 rounded font-mono"
            :class="[theme.bgLight, theme.text]">
            {{ row.original.referral_code }}
          </code>
          <span v-else class="text-xs italic" :class="theme.textMuted">No code</span>
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
            {{ row.original.points_balance.toLocaleString() }} Pkr
          </span>
        </template>

        <template #actions-cell="{ row }">
          <div class="flex items-center justify-end gap-2">
            <button @click="copyUserReferralLink(row.original)"
              class="p-1.5 rounded transition-colors hover:text-green-600 hover:bg-green-50" :class="theme.textMuted"
              title="Copy User's Referral Link">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
              </svg>
            </button>
            <button @click="handleViewReferral(row.original)"
              class="p-1.5 rounded transition-colors hover:text-blue-600 hover:bg-blue-50" :class="theme.textMuted"
              title="View Tree">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </button>
            <button @click="handleEdit(row.original)"
              class="p-1.5 rounded transition-colors hover:text-blue-600 hover:bg-blue-50" :class="theme.textMuted"
              title="Edit Code & Points">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
              </svg>
            </button>
            <button v-if="row.original.referral_code" @click="handleRevokeCode(row.original)"
              class="p-1.5 rounded transition-colors hover:text-red-600 hover:bg-red-50" :class="theme.textMuted"
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

    <!-- Direct Referrals Tab Content -->
    <div v-if="activeTab === 'referrals'" class="rounded-lg shadow-sm border overflow-hidden py-4"
      :class="[theme.card, theme.border]">
      <div v-if="directReferrals.data.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto" :class="theme.textMuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <p class="mt-4 text-lg font-medium" :class="theme.text">No direct referrals yet</p>
        <p class="mt-1 text-sm" :class="theme.textMuted">
          Share your referral link to get users to sign up through you
        </p>
      </div>

      <AppDataTableNew v-else :columns="referralsColumns" :data="directReferrals?.data"
        search-placeholder="Search by name, email or code..." :pagination-data="directReferrals"
        :class="`[&_thead]:${theme.bgLight} [&_th]:text-left [&_th]:text-xs [&_th]:font-medium [&_th]:${theme.textMuted} [&_th]:uppercase [&_th]:tracking-wider [&_td]:py-3 [&_td]:px-3 [&_tr]:border-b [&_tr]:${theme.border}`">
        <template #name-cell="{ row }">
          <div class="flex flex-col">
            <span class="font-medium" :class="theme.text">{{ row.original.name }}</span>
            <span class="text-sm" :class="theme.textMuted">{{ row.original.email }}</span>
          </div>
        </template>

        <template #referral_code-cell="{ row }">
          <code v-if="row.original.referral_code" class="text-xs px-2 py-1 rounded font-mono"
            :class="[theme.bgLight, theme.text]">
            {{ row.original.referral_code }}
          </code>
          <span v-else class="text-xs italic" :class="theme.textMuted">No code</span>
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
            {{ row.original.points_balance.toLocaleString() }} Pkr
          </span>
        </template>

        <template #actions-cell="{ row }">
          <div class="flex items-center justify-end gap-2">
            <button @click="handleViewReferralList(row.original)"
              class="p-1.5 rounded transition-colors hover:text-blue-600 hover:bg-blue-50" :class="theme.textMuted"
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

    <!-- Withdrawal History Tab Content -->
    <div v-if="activeTab === 'withdrawals'" class="rounded-lg shadow-sm border overflow-hidden py-4"
      :class="[theme.card, theme.border]">
      <div v-if="!withdrawalHistory?.data?.length" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto" :class="theme.textMuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <p class="mt-4 text-lg font-medium" :class="theme.text">No withdrawal history</p>
        <p class="mt-1 text-sm" :class="theme.textMuted">
          You haven't made any withdrawals yet. Start earning points to withdraw!
        </p>
      </div>

      <AppDataTableNew v-else :columns="withdrawalColumns" :data="withdrawalHistory.data"
        :pagination-data="withdrawalHistory"
        :class="`[&_thead]:${theme.bgLight} [&_th]:text-left [&_th]:text-xs [&_th]:font-medium [&_th]:${theme.textMuted} [&_th]:uppercase [&_th]:tracking-wider [&_td]:py-3 [&_td]:px-3 [&_tr]:border-b [&_tr]:${theme.border}`">
        <template #requested_amount-cell="{ row }">
          <span class="font-medium" :class="theme.text">
            {{ row.original.total_withdrawn?.toLocaleString() || 0 }}
            Pkr
          </span>
          <span class="font-normal" :class="theme.text" v-if="row.original.requested_amount">{{
            row.original.requested_amount?.toLocaleString() }} pending</span>
        </template>

        <template #status-cell="{ row }">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
            :class="getStatusColor(row.original.status)">
            {{ getStatusLabel(row.original.status) }}
          </span>
        </template>

        <template #proof_images-cell="{ row }">
          <div class="flex items-center gap-2">
            <span class="text-sm" :class="theme.textMuted">
              {{ row.original.proof_images?.length || 0 }} images
            </span>
            <button v-if="row.original.proof_images?.length" @click="openGalleryModal(row.original)"
              class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors"
              title="View Images">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              View
            </button>
            <span v-else class="text-sm" :class="theme.textMuted">No images</span>
          </div>
        </template>

        <template #created_at-cell="{ row }">
          <span class="text-sm" :class="theme.textMuted">
            {{ formatDate(row.original.created_at) }}
          </span>
        </template>

        <template #transaction_id-cell="{ row }">
          <code v-if="row.original.transaction_id" class="text-xs px-2 py-1 rounded font-mono"
            :class="[theme.bgLight, theme.textMuted]">
            {{ row.original.transaction_id }}
          </code>
          <span v-else class="text-sm" :class="theme.textMuted">—</span>
        </template>
      </AppDataTableNew>
    </div>

    <!-- Withdraw Modal -->
    <WithdrawModal :show="showWithdrawModal" :available-points="currentUserPoints" :min-withdrawal="100"
      @close="showWithdrawModal = false" @success="handleWithdrawalSuccess" />

    <!-- Image Gallery Modal -->
    <ImageGalleryModal :show="showGalleryModal" :withdrawal="selectedWithdrawal" @close="showGalleryModal = false" />
  </div>
</template>