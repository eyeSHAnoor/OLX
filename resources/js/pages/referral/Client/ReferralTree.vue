<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { Head, router } from "@inertiajs/vue3";
import Layout from "@/layouts/OlxLayout.vue";
import { useTheme } from '@/Composables/useTheme'

const { theme } = useTheme()
defineOptions({ layout: Layout });

interface User {
    id: number;
    name: string;
    email: string;
    points_balance: number;
    referral_code: string | null;
    created_at: string;
    referred_by: number | null;
    code_assigned_by: number | null;
    status: string;
    profile_image: string | null;
}

interface TreeNode {
    user: User;
    assignees: TreeNode[];
    total_downline: number;
    has_more: boolean;
    level: number;
}

interface Props {
    referrer: User;
    tree: TreeNode;
    stats: {
        direct_referrals: number;
        code_assignees: number;
        total_downline: number;
        total_referrals: number;
        completed_referrals: number;
        visited_referrals: number;
        cancelled_referrals: number;
        total_points_earned: number;
        conversion_rate: number;
        total_visited: number;
    };
    isOwnTree: boolean;
}

const props = defineProps<Props>();

// Track expanded nodes
const expandedNodes = ref<Set<number>>(new Set());
const searchQuery = ref("");
const viewMode = ref<"tree" | "list">("tree");

// Auto-expand first level
onMounted(() => {
    if (props.tree.assignees.length > 0) {
        props.tree.assignees.forEach((assignee) => {
            expandedNodes.value.add(assignee.user.id);
        });
    }
});

// Toggle node
const toggleNode = (nodeId: number) => {
    if (expandedNodes.value.has(nodeId)) {
        expandedNodes.value.delete(nodeId);
    } else {
        expandedNodes.value.add(nodeId);
    }
};

// Expand all
const expandAll = () => {
    const addAllNodes = (nodes: TreeNode[]) => {
        nodes.forEach((node) => {
            expandedNodes.value.add(node.user.id);
            if (node.assignees.length > 0) {
                addAllNodes(node.assignees);
            }
        });
    };
    addAllNodes(props.tree.assignees);
};

// Collapse all
const collapseAll = () => {
    expandedNodes.value.clear();
};

// Get all users from tree (flattened)
const getAllUsers = (nodes: TreeNode[]): User[] => {
    let users: User[] = [];
    nodes.forEach((node) => {
        users.push(node.user);
        if (node.assignees.length > 0) {
            users = users.concat(getAllUsers(node.assignees));
        }
    });
    return users;
};

// Flattened list of all referrals
const allReferrals = computed(() => {
    return getAllUsers(props.tree.assignees);
});

// Filtered referrals based on search
const filteredReferrals = computed(() => {
    if (!searchQuery.value) return allReferrals.value;
    const query = searchQuery.value.toLowerCase();
    return allReferrals.value.filter(
        (user) =>
            user.name.toLowerCase().includes(query) ||
            user.email.toLowerCase().includes(query) ||
            (user.referral_code && user.referral_code.toLowerCase().includes(query))
    );
});

// Copy referral link
const copyReferralLink = async (code: string) => {
    try {
        const link = `${window.location.origin}/register?ref=${code}`;
        await navigator.clipboard.writeText(link);
        alert("Referral link copied to clipboard!");
    } catch (err) {
        console.error("Failed to copy:", err);
    }
};

// Navigate to user
const viewUser = (userId: number) => {
    router.visit(route("users.show", userId));
};

// Get level badge color
const getLevelColor = (level: number) => {
    const colors = [
        "bg-blue-100 text-blue-700",
        "bg-green-100 text-green-700",
        "bg-purple-100 text-purple-700",
        "bg-orange-100 text-orange-700",
        "bg-pink-100 text-pink-700",
        "bg-indigo-100 text-indigo-700",
        "bg-red-100 text-red-700",
        "bg-teal-100 text-teal-700",
    ];
    return colors[level % colors.length] || "bg-gray-100 text-gray-700";
};

// Get status badge
const getStatusBadge = (status: string) => {
    const badges: Record<string, string> = {
        active: "bg-green-100 text-green-700",
        inactive: "bg-gray-100 text-gray-700",
        suspended: "bg-red-100 text-red-700",
    };
    return badges[status] || "bg-gray-100 text-gray-700";
};

// Format date
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <Head :title="`${referrer.name}'s Referral Network`" />

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold" :class="theme.text">
                        {{ referrer.name }}'s Referral Network
                    </h1>
                    <p class="text-sm mt-1" :class="theme.textMuted">
                        {{
                            isOwnTree ? "Your complete referral network" : "Complete referral network"
                        }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <button @click="router.visit(route('downline-referrals.index'))"
                        class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium transition-colors"
                        :class="[theme.card, theme.border, theme.text, theme.hover]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </button>
                </div>
            </div>
        </div>

        <!-- Root User Card -->
        <div class="rounded-lg shadow-sm border overflow-hidden mb-6 hover:shadow-md transition-shadow"
            :class="[theme.card, theme.border]">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-16 h-16 rounded-full flex items-center justify-center shadow-sm flex-shrink-0 overflow-hidden">
                            <img v-if="referrer.profile_image" :src="`/storage/${referrer.profile_image}`"
                                :alt="referrer.name" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center" :class="theme.button">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-xl font-bold" :class="theme.text">{{ referrer.name }}</h3>
                                <span
                                    class="text-xs bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full font-medium border border-blue-200">
                                    ROOT USER
                                </span>
                                <span
                                    class="text-xs bg-green-100 text-green-700 px-2.5 py-0.5 rounded-full font-medium">
                                    {{ stats.total_downline }} in network
                                </span>
                            </div>
                            <p class="text-sm" :class="theme.textMuted">{{ referrer.email }}</p>
                            <div class="flex items-center gap-2 mt-2 flex-wrap">
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-medium"
                                    :class="[theme.bgLight, theme.text]">
                                    Code: {{ referrer.referral_code || "No code" }}
                                </span>
                                <span class="text-xs bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full font-medium">
                                    Balance: {{ referrer.points_balance?.toLocaleString() }} pts
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
            <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
                :class="[theme.card, theme.border]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
                            Direct
                        </p>
                        <p class="text-2xl font-bold mt-1" :class="theme.text">
                            {{ stats.direct_referrals }}
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

            <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
                :class="[theme.card, theme.border]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
                            Total Network
                        </p>
                        <p class="text-2xl font-bold mt-1" :class="theme.text">
                            {{ stats.total_downline }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg" :class="theme.bgLight">
                        <svg class="w-5 h-5" :class="theme.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
                :class="[theme.card, theme.border]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
                            Completed
                        </p>
                        <p class="text-2xl font-bold mt-1" :class="theme.text">
                            {{ stats.completed_referrals }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg" :class="theme.bgLight">
                        <svg class="w-5 h-5" :class="theme.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
                :class="[theme.card, theme.border]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
                            Points Earned
                        </p>
                        <p class="text-2xl font-bold mt-1" :class="theme.text">
                            {{ stats.total_points_earned.toLocaleString() }}
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

            <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
                :class="[theme.card, theme.border]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
                            Conversion
                        </p>
                        <p class="text-2xl font-bold mt-1" :class="theme.text">
                            {{ stats.conversion_rate }}%
                        </p>
                    </div>
                    <div class="p-2 rounded-lg" :class="theme.bgLight">
                        <svg class="w-5 h-5" :class="theme.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
                :class="[theme.card, theme.border]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
                            Visited
                        </p>
                        <p class="text-2xl font-bold mt-1" :class="theme.text">{{ stats.total_visited }}</p>
                    </div>
                    <div class="p-2 rounded-lg" :class="theme.bgLight">
                        <svg class="w-5 h-5" :class="theme.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Controls -->
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" :class="theme.textMuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <h3 class="text-lg font-semibold" :class="theme.text">Referral Network</h3>
                    <span class="text-sm" :class="theme.textMuted">({{ allReferrals.length }} members)</span>
                </div>

                <!-- View Mode Toggle -->
                <div class="flex border rounded-md overflow-hidden" :class="theme.border">
                    <button @click="viewMode = 'tree'" :class="[
                        'px-3 py-1.5 text-sm transition-colors',
                        viewMode === 'tree'
                            ? theme.button
                            : `${theme.card} ${theme.text} ${theme.hover}`,
                    ]">
                        Tree
                    </button>
                    <button @click="viewMode = 'list'" :class="[
                        'px-3 py-1.5 text-sm transition-colors',
                        viewMode === 'list'
                            ? theme.button
                            : `${theme.card} ${theme.text} ${theme.hover}`,
                    ]">
                        List
                    </button>
                </div>
            </div>

            <div class="flex gap-2">
                <!-- Search -->
                <div class="relative">
                    <input v-model="searchQuery" type="text" placeholder="Search members..."
                        class="pl-9 pr-4 py-1.5 border rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        :class="[theme.input, theme.border]" />
                    <svg class="w-4 h-4 absolute left-3 top-2" :class="theme.textMuted" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <button v-if="viewMode === 'tree'" @click="expandAll"
                    class="inline-flex items-center px-3 py-1.5 border rounded-md text-sm font-medium transition-colors"
                    :class="[theme.card, theme.border, theme.text, theme.hover]">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    Expand All
                </button>
                <button v-if="viewMode === 'tree'" @click="collapseAll"
                    class="inline-flex items-center px-3 py-1.5 border rounded-md text-sm font-medium transition-colors"
                    :class="[theme.card, theme.border, theme.text, theme.hover]">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                    Collapse All
                </button>
            </div>
        </div>

        <!-- Tree View -->
        <div v-if="viewMode === 'tree'" class="rounded-lg shadow-sm border overflow-hidden"
            :class="[theme.card, theme.border]">
            <div class="px-6 py-6">
                <!-- Empty State -->
                <div v-if="tree.assignees.length === 0" class="text-center py-12">
                    <svg class="w-20 h-20 mx-auto" :class="theme.textMuted" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <p class="mt-4 text-lg font-medium" :class="theme.text">No referrals yet</p>
                    <p class="mt-1 text-sm" :class="theme.textMuted">
                        Share your referral link to build your network
                    </p>
                    <div class="mt-4 inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium transition-colors cursor-pointer"
                        :class="[theme.card, theme.border, theme.text, theme.hover]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                        </svg>
                        Copy Referral Link
                    </div>
                </div>

                <!-- Tree Structure -->
                <div v-else class="space-y-3">
                    <template v-for="node in tree.assignees" :key="node.user.id">
                        <div class="relative pl-8 border-l-2 ml-6" :class="theme.border">
                            <div class="absolute -left-1.5 top-6 w-3 h-3 rounded-full" :class="theme.bgLight"></div>
                            <div class="space-y-3">
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                                    :class="[theme.card, theme.border]" @click="toggleNode(node.user.id)">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 transition-transform duration-200"
                                                    :class="[{ 'rotate-180': expandedNodes.has(node.user.id) }, theme.textMuted]"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center overflow-hidden"
                                                :class="node.user.profile_image ? '' : theme.bgLight">
                                                <img v-if="node.user.profile_image"
                                                    :src="`/storage/${node.user.profile_image}`" :alt="node.user.name"
                                                    class="w-full h-full object-cover" />
                                                <span v-else class="text-sm font-medium" :class="theme.text">
                                                    {{ node.user.name.charAt(0).toUpperCase() }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="font-medium" :class="theme.text">{{
                                                        node.user.name
                                                        }}</span>
                                                    <span class="text-xs" :class="getLevelColor(node.level)">
                                                        Level {{ node.level }}
                                                    </span>
                                                    <span class="text-xs" :class="getStatusBadge(node.user.status)">
                                                        {{ node.user.status || "Active" }}
                                                    </span>
                                                    <span v-if="node.total_downline > 0" class="text-xs"
                                                        :class="theme.textMuted">
                                                        ({{ node.total_downline }} downline)
                                                    </span>
                                                </div>
                                                <p class="text-sm" :class="theme.textMuted">{{ node.user.email }}</p>
                                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                                    <span class="text-xs" :class="theme.textMuted">
                                                        Code: {{ node.user.referral_code || "No code" }}
                                                    </span>
                                                    <span class="text-xs text-blue-600">
                                                        {{ node.user.points_balance?.toLocaleString() }} pts
                                                    </span>
                                                    <span class="text-xs" :class="theme.textMuted">
                                                        Joined: {{ formatDate(node.user.created_at) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button @click.stop="viewUser(node.user.id)"
                                                class="text-sm text-blue-600 hover:text-blue-800 px-2 py-1 rounded hover:bg-blue-50 transition-colors">
                                                View
                                            </button>
                                            <button v-if="node.user.referral_code"
                                                @click.stop="copyReferralLink(node.user.referral_code)"
                                                class="text-sm px-2 py-1 rounded transition-colors"
                                                :class="[theme.textMuted, theme.hover]">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Children -->
                                <div v-if="expandedNodes.has(node.user.id) && node.assignees.length > 0"
                                    class="ml-8 space-y-2 border-l-2 pl-4" :class="theme.border">
                                    <div v-for="child in node.assignees" :key="child.user.id" class="relative pl-4">
                                        <div class="absolute -left-1.5 top-3 w-3 h-3 rounded-full"
                                            :class="theme.bgLight"></div>
                                        <div class="border rounded-lg p-3 hover:shadow-sm transition-shadow"
                                            :class="[theme.card, theme.border]">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full flex items-center justify-center overflow-hidden"
                                                        :class="child.user.profile_image ? '' : theme.bgLight">
                                                        <img v-if="child.user.profile_image"
                                                            :src="`/storage/${child.user.profile_image}`"
                                                            :alt="child.user.name" class="w-full h-full object-cover" />
                                                        <span v-else class="text-xs font-medium" :class="theme.text">
                                                            {{ child.user.name.charAt(0).toUpperCase() }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span class="font-medium text-sm" :class="theme.text">{{
                                                                child.user.name
                                                                }}</span>
                                                            <span class="text-xs" :class="getLevelColor(child.level)">
                                                                Level {{ child.level }}
                                                            </span>
                                                            <span v-if="child.total_downline > 0" class="text-xs"
                                                                :class="theme.textMuted">
                                                                ({{ child.total_downline }} downline)
                                                            </span>
                                                        </div>
                                                        <p class="text-xs" :class="theme.textMuted">{{ child.user.email
                                                        }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-blue-600">
                                                        {{ child.user.points_balance?.toLocaleString() }} pts
                                                    </span>
                                                    <button @click.stop="viewUser(child.user.id)"
                                                        class="text-xs text-blue-600 hover:text-blue-800">
                                                        View
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- List View -->
        <div v-else class="rounded-lg shadow-sm border overflow-hidden" :class="[theme.card, theme.border]">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y" :class="theme.border">
                    <thead :class="theme.bgLight">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :class="theme.textMuted">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :class="theme.textMuted">
                                Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :class="theme.textMuted">
                                Level
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :class="theme.textMuted">
                                Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :class="theme.textMuted">
                                Points
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :class="theme.textMuted">
                                Joined
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider"
                                :class="theme.textMuted">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" :class="theme.border">
                        <tr v-if="filteredReferrals.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center" :class="theme.textMuted">
                                {{
                                    searchQuery
                                        ? "No members found matching your search"
                                        : "No referrals found"
                                }}
                            </td>
                        </tr>
                        <tr v-for="user in filteredReferrals" :key="user.id" class="transition-colors"
                            :class="theme.hover">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 overflow-hidden"
                                        :class="user.profile_image ? '' : theme.bgLight">
                                        <img v-if="user.profile_image" :src="`/storage/${user.profile_image}`"
                                            :alt="user.name" class="w-full h-full object-cover" />
                                        <span v-else class="text-xs font-medium" :class="theme.text">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-medium" :class="theme.text">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :class="theme.textMuted">
                                {{ user.email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs" :class="getLevelColor(user.level || 1)">
                                    Level {{ user.level || 1 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-mono px-2 py-1 rounded" :class="[theme.bgLight, theme.text]">
                                    {{ user.referral_code || "No code" }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                {{ user.points_balance?.toLocaleString() || 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" :class="theme.textMuted">
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button @click="viewUser(user.id)" class="text-blue-600 hover:text-blue-800">
                                    View
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3 border-t" :class="[theme.border, theme.bgLight]">
                <p class="text-sm" :class="theme.textMuted">
                    Showing {{ filteredReferrals.length }} of {{ allReferrals.length }} total
                    members
                </p>
            </div>
        </div>
    </div>
</template>