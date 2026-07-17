<script setup lang="ts">
import { PropType } from "vue";

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

const props = defineProps({
    node: {
        type: Object as PropType<TreeNode>,
        required: true,
    },
    level: {
        type: Number,
        required: true,
    },
    expandedNodes: {
        type: Object as PropType<Set<number>>,
        required: true,
    },
});

const emit = defineEmits<{
    toggle: [nodeId: number];
    "view-user": [userId: number];
    "view-tree": [userId: number];
}>();

const isExpanded = (nodeId: number) => props.expandedNodes.has(nodeId);
const hasChildren = (node: TreeNode) => node.assignees && node.assignees.length > 0;

const getNodeBadgeColor = (level: number) => {
    const colors = [
        "bg-blue-100 text-blue-700 border-blue-300",
        "bg-green-100 text-green-700 border-green-300",
        "bg-purple-100 text-purple-700 border-purple-300",
        "bg-orange-100 text-orange-700 border-orange-300",
        "bg-pink-100 text-pink-700 border-pink-300",
        "bg-teal-100 text-teal-700 border-teal-300",
        "bg-indigo-100 text-indigo-700 border-indigo-300",
    ];
    return colors[level % colors.length];
};

const getBorderColor = (level: number) => {
    const colors = [
        "border-l-blue-400",
        "border-l-green-400",
        "border-l-purple-400",
        "border-l-orange-400",
        "border-l-pink-400",
        "border-l-teal-400",
        "border-l-indigo-400",
    ];
    return colors[level % colors.length];
};

const formatDate = (date: string) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

function handleViewReferralList(user: ReferralData) {
    router.visit(route("referral.tree", user.id));
}
</script>

<template>
    <div class="relative">
        <!-- Node Card -->
        <div :class="[
            'border-l-4 rounded-r-lg p-4 bg-card hover:bg-muted/50 transition-all duration-200 cursor-pointer',
            getBorderColor(level),
            isExpanded(node.user.id) ? 'shadow-md ring-1 ring-black/5' : 'shadow-sm',
        ]" @click="emit('toggle', node.user.id)">
            <div class="flex items-start gap-3">
                <!-- Expand/Collapse Icon -->
                <div class="mt-1.5 flex-shrink-0">
                    <Icon v-if="hasChildren(node)" :name="isExpanded(node.user.id) ? 'lucide:chevron-down' : 'lucide:chevron-right'
                        " class="size-5 text-muted-foreground transition-transform duration-200" />
                    <div v-else class="w-5" />
                </div>

                <!-- User Avatar with Level Indicator -->
                <div class="relative flex-shrink-0">
                    <div :class="[
                        'w-10 h-10 rounded-full flex items-center justify-center border-2 overflow-hidden',
                        getNodeBadgeColor(level),
                    ]">
                        <img v-if="node.user.profile_image" :src="`/storage/${node.user.profile_image}`"
                            :alt="node.user.name" class="w-full h-full object-cover" />
                        <Icon v-else name="lucide:user" class="size-5" />
                    </div>
                    <span
                        class="absolute -top-1 -right-1 text-[10px] font-bold bg-white rounded-full w-4 h-4 flex items-center justify-center border shadow-sm">
                        {{ level }}
                    </span>
                </div>

                <!-- User Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-sm">{{ node.user.name }}</p>
                        <span :class="[
                            'text-[10px] px-1.5 py-0.5 rounded-full font-medium border',
                            getNodeBadgeColor(level),
                        ]">
                            Level {{ level }}
                        </span>
                        <span v-if="node.total_downline > 0"
                            class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full font-medium">
                            {{ node.total_downline }} downline
                        </span>
                    </div>
                    <p class="text-xs text-muted-foreground truncate">{{ node.user.email }}</p>

                    <!-- Stats Row -->
                    <div class="flex items-center gap-3 mt-2 text-xs">
                        <span class="flex items-center gap-1 text-muted-foreground">
                            <Icon name="lucide:calendar" class="size-3" />
                            {{ formatDate(node.user.created_at) }}
                        </span>
                        <button @click="handleViewReferralList(node)" v-if="node.referral_stats.total_referrals > 0"
                            class="flex items-center gap-1 text-blue-600 font-medium">
                            <Icon name="lucide:users" class="size-3" />
                            {{ node.referral_stats.total_referrals }} refs
                        </button>
                        <span v-if="node.referral_stats.total_points_earned > 0"
                            class="flex items-center gap-1 text-green-600 font-medium">
                            <Icon name="lucide:gift" class="size-3" />
                            {{ node.referral_stats.total_points_earned }} pts
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-1 flex-shrink-0">
                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-medium">
                        {{ node.user.points_balance?.toLocaleString() }} pts
                    </span>
                    <button class="p-1.5 hover:bg-muted rounded-lg transition-colors" title="View referrals"
                        @click.stop="emit('view-user', node.user.id)">
                        <Icon name="lucide:external-link" class="size-4 text-muted-foreground" />
                    </button>
                    <button v-if="hasChildren(node)" class="p-1.5 hover:bg-muted rounded-lg transition-colors"
                        title="View tree" @click.stop="emit('view-tree', node.user.id)">
                        <Icon name="lucide:git-branch" class="size-4 text-muted-foreground" />
                    </button>
                </div>
            </div>

            <!-- Code Badge -->
            <div v-if="node.user.referral_code" class="mt-2 ml-12">
                <span class="text-[10px] bg-muted px-2 py-0.5 rounded-full font-mono">
                    Code: {{ node.user.referral_code }}
                </span>
            </div>
        </div>

        <!-- Children (Recursive) -->
        <div v-if="isExpanded(node.user.id) && hasChildren(node)"
            class="ml-6 pl-6 border-l-2 border-dashed border-muted-foreground/20 mt-2 space-y-2">
            <!-- Assignees Count Badge -->
            <div class="flex items-center gap-2 mb-3 -ml-6 pl-6">
                <div class="flex items-center gap-1.5 bg-muted/50 px-2 py-1 rounded-full">
                    <Icon name="lucide:users" class="size-3 text-muted-foreground" />
                    <span class="text-[10px] font-medium text-muted-foreground">
                        {{ node.assignees.length }} assignee{{
                            node.assignees.length !== 1 ? "s" : ""
                        }}
                    </span>
                </div>
            </div>

            <!-- Recursive Child Nodes -->
            <template v-for="childNode in node.assignees" :key="childNode.user.id">
                <ReferralTreeNode :node="childNode" :level="level + 1" :expanded-nodes="expandedNodes"
                    @toggle="(nodeId: number) => emit('toggle', nodeId)"
                    @view-user="(userId: number) => emit('view-user', userId)"
                    @view-tree="(userId: number) => emit('view-tree', userId)" />
            </template>
        </div>
    </div>
</template>
