<script setup lang="ts">
import { InertiaPageProps } from "@/types";
import { usePage, router, Head } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import Layout from "@/layouts/OlxLayout.vue";
import { useBreadcrumb } from "@/composables/useBreadcrumb";
import ReferralTreeNode from "../_partials/ReferralTreeNode.vue";
import { useTheme } from '@/Composables/useTheme'

const { theme } = useTheme()
defineOptions({ layout: Layout });

// Types
interface TreeUser {
    id: number;
    name: string;
    email: string;
    points_balance: number;
    referral_code: string | null;
    created_at: string;
    referrer_id: number | null;
    code_assigned_by: number | null;
}

interface ReferralStats {
    total_referrals: number;
    total_points_earned: number;
    total_assignees: number;
}

interface TreeNode {
    user: TreeUser;
    referral_stats: ReferralStats;
    assignees: TreeNode[];
    has_more: boolean;
    total_downline: number;
}

interface PageProps extends InertiaPageProps {
    referrer: {
        id: number;
        name: string;
        email: string;
        referral_code: string | null;
        points_balance: number;
    };
    tree: TreeNode;
    stats: {
        total_referrals: number;
        total_visited: number;
        total_points_earned: number;
        conversion_rate: number;
        total_assignees: number;
        total_tree_assignees: number;
    };
}

const page = usePage<PageProps>();

console.log(page.props);
const referrer = computed(() => page.props.referrer);
const tree = computed(() => page.props.tree);
const stats = computed(() => page.props.stats);

// Track expanded nodes using Set
const expandedNodes = ref<Set<number>>(new Set());

// Auto-expand first level
onMounted(() => {
    // Auto expand first level assignees
    if (tree.value.assignees.length > 0) {
        tree.value.assignees.forEach((assignee) => {
            expandedNodes.value.add(assignee.user.id);
        });
    }
});

const toggleNode = (nodeId: number) => {
    if (expandedNodes.value.has(nodeId)) {
        expandedNodes.value.delete(nodeId);
    } else {
        expandedNodes.value.add(nodeId);
    }
};

const expandAll = () => {
    const addAllNodes = (nodes: TreeNode[]) => {
        nodes.forEach((node) => {
            expandedNodes.value.add(node.user.id);
            if (node.assignees.length > 0) {
                addAllNodes(node.assignees);
            }
        });
    };
    addAllNodes(tree.value.assignees);
};

const collapseAll = () => {
    expandedNodes.value.clear();
};

// Navigation
const viewUserReferrals = (userId: number) => {
    router.visit(route("users.referrals.show", userId));
};

const viewUserTree = (userId: number) => {
    router.visit(route("users.referrals.show", userId));
};

const goBack = () => {
    router.visit(route("downline-referrals.index"));
};

// Breadcrumbs
const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: "Home", href: "/dashboard" },
        { label: "Referrals", href: route("referrals.index") },
        {
            label: `${referrer.value.name}'s Referral Tree`,
            href: route("users.referrals.show", referrer.value.id),
        },
    ]);
});
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <Head :title="`${referrer.name}'s Referral Tree`" />

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold" :class="theme.text">
                        {{ referrer.name }}'s Referral Tree
                    </h1>
                    <p class="text-sm mt-1" :class="theme.textMuted">
                        Complete code assignment network showing all users who received codes and
                        their downlines
                    </p>
                </div>
                <button @click="goBack"
                    class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    :class="[theme.card, theme.border, theme.text, theme.hover]">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Referrals
                </button>
            </div>
        </div>

        <!-- Root User Card -->
        <div class="rounded-lg shadow-sm border overflow-hidden mb-6 hover:shadow-md transition-shadow"
            :class="[theme.card, theme.border]">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center shadow-sm flex-shrink-0"
                            :class="theme.button">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-xl font-bold" :class="theme.text">{{ referrer.name }}</h3>
                                <span
                                    class="text-xs bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full font-medium border border-blue-200">
                                    ROOT USER
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
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
                :class="[theme.card, theme.border]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
                            Direct Assignees
                        </p>
                        <p class="text-2xl font-bold mt-1" :class="theme.text">
                            {{ stats.total_assignees }}
                        </p>
                    </div>
                    <div class="p-2 rounded-lg" :class="theme.bgLight">
                        <svg class="w-5 h-5" :class="theme.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
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
                            {{ stats.total_tree_assignees }}
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
                            Referrals
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

            <div class="rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
                :class="[theme.card, theme.border]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider" :class="theme.textMuted">
                            Points Earned
                        </p>
                        <p class="text-2xl font-bold mt-1" :class="theme.text">
                            {{ stats.total_points_earned?.toLocaleString() }}
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
        </div>

        <!-- Tree Controls -->
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" :class="theme.textMuted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <h3 class="text-lg font-semibold" :class="theme.text">Code Assignment Network</h3>
            </div>
            <div class="flex gap-2">
                <button @click="expandAll"
                    class="inline-flex items-center px-3 py-1.5 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    :class="[theme.card, theme.border, theme.text, theme.hover]">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    Expand All
                </button>
                <button @click="collapseAll"
                    class="inline-flex items-center px-3 py-1.5 border rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    :class="[theme.card, theme.border, theme.text, theme.hover]">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                    Collapse All
                </button>
            </div>
        </div>

        <!-- Tree View -->
        <div class="rounded-lg shadow-sm border overflow-hidden" :class="[theme.card, theme.border]">
            <div class="px-6 py-6">
                <!-- Empty State -->
                <div v-if="tree.assignees.length === 0" class="text-center py-12">
                    <svg class="w-20 h-20 mx-auto" :class="theme.textMuted" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    <p class="mt-4 text-lg font-medium" :class="theme.text">No code assignments yet</p>
                    <p class="mt-1 text-sm" :class="theme.textMuted">
                        {{ referrer.name }} hasn't assigned their referral code to anyone yet
                    </p>
                </div>

                <!-- Tree Structure -->
                <div v-else class="space-y-3">
                    <template v-for="(node, index) in tree.assignees" :key="node.user.id">
                        <ReferralTreeNode :node="node" :level="1" :expanded-nodes="expandedNodes" @toggle="toggleNode"
                            @view-user="viewUserReferrals" @view-tree="viewUserTree" />
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>