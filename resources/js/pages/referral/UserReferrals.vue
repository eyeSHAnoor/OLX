<script setup lang="ts">
import { InertiaPageProps } from "@/types";
import { usePage, router, Head } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useBreadcrumb } from "@/composables/useBreadcrumb";
import ReferralTreeNode from "./_partials/ReferralTreeNode.vue";

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
    allReferrals: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            referral_code: string | null;
            points_balance: number;
            created_at: string;
            profile_image: string | null;
            referrer: {
                id: number;
                name: string;
            } | null;
        }>;
        links: any[];
    };
}

const page = usePage<PageProps>();

console.log(page.props);
const referrer = computed(() => page.props.referrer);
const tree = computed(() => page.props.tree);
const stats = computed(() => page.props.stats);
const allReferrals = computed(() => page.props.allReferrals);

// Tab state
const activeTab = ref<"tree" | "referrals">("tree");

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
    router.visit(route("referrals.index"));
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
    <AppContainer>

        <Head :title="`${referrer.name}'s Referral Tree`" />

        <!-- Header -->
        <PageHeading>
            <template #title>{{ referrer.name }}'s Referral Network</template>
            <template #description> View code assignments and referral history </template>
            <template #links>
                <AppButton label="Back to Referrals" variant="outline" icon="lucide:arrow-left" @click="goBack" />
            </template>
        </PageHeading>
        <!-- Root User Card -->
        <div
            class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6 hover:shadow-md transition-shadow">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-16 h-16 rounded-full flex items-center justify-center shadow-sm flex-shrink-0 overflow-hidden">
                            <img v-if="referrer.profile_image" :src="`/storage/${referrer.profile_image}`"
                                :alt="referrer.name" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full bg-blue-600 flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="text-xl font-bold text-gray-900">{{ referrer.name }}</h3>
                                <span
                                    class="text-xs bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full font-medium border border-blue-200">
                                    ROOT USER
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">{{ referrer.email }}</p>
                            <div class="flex items-center gap-2 mt-2 flex-wrap">
                                <span class="text-xs bg-gray-100 text-gray-700 px-2.5 py-0.5 rounded-full font-medium">
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
            <Card class="bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-blue-600">Direct Assignees</p>
                            <p class="text-2xl font-bold text-blue-900">{{ stats.total_assignees }}</p>
                        </div>
                        <Icon name="lucide:user-plus" class="size-6 text-blue-400" />
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-indigo-50 to-indigo-100 border-indigo-200">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-indigo-600">Total Network</p>
                            <p class="text-2xl font-bold text-indigo-900">
                                {{ stats.total_tree_assignees }}
                            </p>
                        </div>
                        <Icon name="lucide:network" class="size-6 text-indigo-400" />
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-green-50 to-green-100 border-green-200">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-green-600">Referrals</p>
                            <p class="text-2xl font-bold text-green-900">{{ stats.total_referrals }}</p>
                        </div>
                        <Icon name="lucide:users" class="size-6 text-green-400" />
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-purple-600">Points Earned</p>
                            <p class="text-2xl font-bold text-purple-900">
                                {{ stats.total_points_earned?.toLocaleString() }}
                            </p>
                        </div>
                        <Icon name="lucide:gift" class="size-6 text-purple-400" />
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-yellow-50 to-yellow-100 border-yellow-200">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-yellow-600">Visited</p>
                            <p class="text-2xl font-bold text-yellow-900">{{ stats.total_visited }}</p>
                        </div>
                        <Icon name="lucide:eye" class="size-6 text-yellow-400" />
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-pink-50 to-pink-100 border-pink-200">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-pink-600">Conversion</p>
                            <p class="text-2xl font-bold text-pink-900">{{ stats.conversion_rate }}%</p>
                        </div>
                        <Icon name="lucide:trending-up" class="size-6 text-pink-400" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'tree'" :class="[
                    'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
                    activeTab === 'tree'
                        ? 'border-brand-teal text-brand-teal'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                ]">
                    <div class="flex items-center gap-2">
                        <Icon name="lucide:git-branch" class="size-4" />
                        Current Code Assignment
                    </div>
                </button>
                <button @click="activeTab = 'referrals'" :class="[
                    'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
                    activeTab === 'referrals'
                        ? 'border-brand-teal text-brand-teal'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                ]">
                    <div class="flex items-center gap-2">
                        <Icon name="lucide:users" class="size-4" />
                        All Referrals
                        <span v-if="allReferrals?.data?.length"
                            class="ml-1 bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">
                            {{ allReferrals.data.length }}
                        </span>
                    </div>
                </button>
            </nav>
        </div>

        <!-- Tab Content: Tree View -->
        <div v-if="activeTab === 'tree'">
            <!-- Tree Controls -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <Icon name="lucide:git-branch" class="size-5 text-muted-foreground" />
                    <h3 class="text-lg font-semibold">Code Assignment Network</h3>
                </div>
                <div class="flex gap-2">
                    <AppButton label="Expand All" variant="outline" size="sm" icon="lucide:chevrons-down-down"
                        @click="expandAll" />
                    <AppButton label="Collapse All" variant="outline" size="sm" icon="lucide:chevrons-up-up"
                        @click="collapseAll" />
                </div>
            </div>

            <Card>
                <CardContent class="p-6">
                    <!-- Empty State -->
                    <div v-if="tree.assignees.length === 0" class="text-center py-12">
                        <Icon name="lucide:network" class="size-20 text-muted-foreground/20 mx-auto mb-4" />
                        <p class="text-lg font-semibold text-muted-foreground">
                            No code assignments yet
                        </p>
                        <p class="text-sm text-muted-foreground mt-1">
                            {{ referrer.name }} hasn't assigned their referral code to anyone yet
                        </p>
                    </div>

                    <!-- Tree Structure -->
                    <div v-else class="space-y-3">
                        <template v-for="(node, index) in tree.assignees" :key="node.user.id">
                            <ReferralTreeNode :node="node" :level="1" :expanded-nodes="expandedNodes"
                                @toggle="toggleNode" @view-user="viewUserReferrals" @view-tree="viewUserTree" />
                        </template>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Tab Content: All Referrals -->
        <div v-if="activeTab === 'referrals'">
            <Card>
                <CardContent class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">All Referrals</h3>
                        <span class="text-sm text-muted-foreground">
                            Total: {{ allReferrals?.data?.length || 0 }} referrals
                        </span>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!allReferrals?.data?.length" class="text-center py-12">
                        <Icon name="lucide:users" class="size-20 text-muted-foreground/20 mx-auto mb-4" />
                        <p class="text-lg font-semibold text-muted-foreground">No referrals yet</p>
                        <p class="text-sm text-muted-foreground mt-1">
                            {{ referrer.name }} hasn't referred anyone yet
                        </p>
                    </div>

                    <!-- Referrals List -->
                    <div v-else class="space-y-3">
                        <div v-for="referral in allReferrals.data" :key="referral.id"
                            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-blue to-brand-teal flex items-center justify-center text-white font-semibold overflow-hidden">
                                    <img v-if="referral.profile_image" :src="`/storage/${referral.profile_image}`"
                                        :alt="referral.name" class="w-full h-full object-cover" />
                                    <span v-else>{{ referral.name?.charAt(0) || "?" }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ referral.name }}</p>
                                    <p class="text-sm text-gray-500">{{ referral.email }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                            Code: {{ referral.referral_code || "N/A" }}
                                        </span>
                                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                                            Points: {{ referral.points_balance || 0 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">
                                    Joined {{ new Date(referral.created_at).toLocaleDateString() }}
                                </p>
                                <button @click="viewUserTree(referral.id)"
                                    class="mt-1 text-xs text-brand-teal hover:text-brand-blue font-medium">
                                    View Tree →
                                </button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppContainer>
</template>
