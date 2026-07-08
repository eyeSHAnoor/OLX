<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from "@/types";
import { usePage, router, Head } from "@inertiajs/vue3";
import { computed, onMounted } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useBreadcrumb } from "@/composables/useBreadcrumb";

defineOptions({ layout: Layout });

interface ReferredUser {
    id: number;
    name: string;
    email: string;
    points_balance: number;
    created_at: string;
}

interface ReferralData {
    id: number;
    referrer_id: number;
    referred_user_id: number;
    status: string;
    points_awarded: number;
    link_code: string;
    visited_at: string;
    created_at: string;
    referred_user: ReferredUser | null;
}

interface PageProps extends InertiaPageProps {
    referrer: {
        id: number;
        name: string;
        email: string;
        referral_code: string | null;
        points_balance: number;
    };
    referrals: PaginatedData<ReferralData>;
    stats: {
        total_referrals: number;
        total_visited: number;
        total_points_earned: number;
        conversion_rate: number;
    };
}

const page = usePage<PageProps>();
const referrer = computed(() => page.props.referrer);
const referrals = computed(() => page.props.referrals);
const stats = computed(() => page.props.stats);

// Generate referral link
const referralLink = computed(() => {
    if (referrer.value.referral_code) {
        return `${window.location.origin}/register?ref=${referrer.value.referral_code}`;
    }
    return null;
});

// Copy to clipboard
const copyLink = async () => {
    if (referralLink.value) {
        await navigator.clipboard.writeText(referralLink.value);
        // You can add a toast notification here
    }
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

// Get status badge class
const getStatusClass = (status: string) => {
    switch (status) {
        case "completed":
            return "bg-green-100 text-green-700";
        case "visited":
            return "bg-yellow-100 text-yellow-700";
        case "cancelled":
            return "bg-red-100 text-red-700";
        default:
            return "bg-gray-100 text-gray-700";
    }
};

// Columns for data table
const columns = [
    { accessorKey: "referred_user", header: "Referred User", sortable: false },
    { accessorKey: "status", header: "Status", sortable: true },
    { accessorKey: "points_awarded", header: "Points Awarded", sortable: true },
    { accessorKey: "visited_at", header: "Visited At", sortable: true },
    { accessorKey: "created_at", header: "Completed At", sortable: true },
];

// Breadcrumbs
const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: "Home", href: "/dashboard" },
        { label: "Referrals", href: route("referrals.index") },
        {
            label: `${referrer.value.name}'s Referrals`,
            href: route("users.referrals.show", referrer.value.id),
        },
    ]);
});

// Navigate back
const goBack = () => {
    router.visit(route("referrals.index"));
};
</script>

<template>
    <AppContainer>

        <Head :title="`${referrer.name}'s Referrals`" />

        <!-- Header -->
        <PageHeading>
            <template #title>{{ referrer.name }}'s Referrals</template>
            <template #description> View all users referred by {{ referrer.name }} </template>
            <template #links>
                <AppButton label="Back to Referrals" variant="outline" icon="lucide:arrow-left" @click="goBack" />
            </template>
        </PageHeading>

        <!-- Referrer Info Card -->
        <Card class="mb-6">
            <CardContent class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-brand-teal rounded-full flex items-center justify-center">
                            <Icon name="lucide:user" class="size-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">{{ referrer.name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ referrer.email }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                    Code: {{ referrer.referral_code || "No code" }}
                                </span>
                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                                    Balance: {{ referrer.points_balance.toLocaleString() }} pts
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Link -->
                    <div v-if="referralLink" class="flex items-center gap-2">
                        <code class="text-xs bg-muted px-3 py-1.5 rounded-lg max-w-md truncate">
                    {{ referralLink }}
                </code>
                        <AppButton variant="outline" size="sm" icon="lucide:copy" @click="copyLink" label="Copy Link" />
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <Card class="bg-gradient-to-br from-green-50 to-green-100 border-green-200">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-green-600">Successful Referrals</p>
                            <p class="text-2xl font-bold text-green-900">{{ stats.total_referrals }}</p>
                        </div>
                        <div class="p-2 bg-green-200 rounded-full">
                            <Icon name="lucide:user-check" class="size-5 text-green-700" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-yellow-50 to-yellow-100 border-yellow-200">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-yellow-600">Visited (Not Registered)</p>
                            <p class="text-2xl font-bold text-yellow-900">{{ stats.total_visited }}</p>
                        </div>
                        <div class="p-2 bg-yellow-200 rounded-full">
                            <Icon name="lucide:eye" class="size-5 text-yellow-700" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-purple-600">Points Earned</p>
                            <p class="text-2xl font-bold text-purple-900">
                                {{ stats.total_points_earned.toLocaleString() }}
                            </p>
                        </div>
                        <div class="p-2 bg-purple-200 rounded-full">
                            <Icon name="lucide:gift" class="size-5 text-purple-700" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200">
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-blue-600">Conversion Rate</p>
                            <p class="text-2xl font-bold text-blue-900">{{ stats.conversion_rate }}%</p>
                        </div>
                        <div class="p-2 bg-blue-200 rounded-full">
                            <Icon name="lucide:trending-up" class="size-5 text-blue-700" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Referrals Table -->
        <Card>
            <CardHeader>
                <CardTitle>Referral History</CardTitle>
                <CardDescription>
                    All users who visited or registered through {{ referrer.name }}'s referral link
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left p-4 text-sm font-medium text-muted-foreground">
                                    Referred User
                                </th>
                                <th class="text-center p-4 text-sm font-medium text-muted-foreground">
                                    Status
                                </th>
                                <th class="text-center p-4 text-sm font-medium text-muted-foreground">
                                    Points Awarded
                                </th>
                                <th class="text-center p-4 text-sm font-medium text-muted-foreground">
                                    Visited At
                                </th>
                                <th class="text-center p-4 text-sm font-medium text-muted-foreground">
                                    Completed At
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="referral in referrals.data" :key="referral.id"
                                class="border-b hover:bg-muted/50 transition-colors">
                                <!-- Referred User Info -->
                                <td class="p-4">
                                    <div v-if="referral.referred_user">
                                        <p class="font-semibold">{{ referral.referred_user.name }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ referral.referred_user.email }}
                                        </p>
                                        <p class="text-xs text-muted-foreground mt-0.5">
                                            Balance: {{ referral.referred_user.points_balance }} pts
                                        </p>
                                    </div>
                                    <div v-else>
                                        <p class="text-sm text-muted-foreground italic">Not registered yet</p>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="p-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium"
                                        :class="getStatusClass(referral.status)">
                                        <Icon v-if="referral.status === 'completed'" name="lucide:check-circle"
                                            class="size-3" />
                                        <Icon v-else-if="referral.status === 'visited'" name="lucide:clock"
                                            class="size-3" />
                                        <Icon v-else name="lucide:x-circle" class="size-3" />
                                        {{
                                            referral.status.charAt(0).toUpperCase() + referral.status.slice(1)
                                        }}
                                    </span>
                                </td>

                                <!-- Points Awarded -->
                                <td class="p-4 text-center">
                                    <span class="font-semibold" :class="referral.points_awarded > 0
                                            ? 'text-green-600'
                                            : 'text-muted-foreground'
                                        ">
                                        {{
                                            referral.points_awarded > 0 ? `+${referral.points_awarded}` : "-"
                                        }}
                                        pts
                                    </span>
                                </td>

                                <!-- Visited At -->
                                <td class="p-4 text-center text-sm">
                                    {{ formatDate(referral.visited_at) }}
                                </td>

                                <!-- Completed At -->
                                <td class="p-4 text-center text-sm">
                                    <span v-if="referral.status === 'completed'">
                                        {{ formatDate(referral.created_at) }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="referrals.data.length === 0">
                                <td colspan="5" class="p-8 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <Icon name="lucide:users" class="size-12 text-muted-foreground/50" />
                                        <p class="text-lg font-semibold text-muted-foreground">
                                            No referrals yet
                                        </p>
                                        <p class="text-sm text-muted-foreground">
                                            Share the referral link to start earning points
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="referrals.last_page > 1" class="mt-6 flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Showing {{ referrals.from }} to {{ referrals.to }} of
                        {{ referrals.total }} referrals
                    </p>
                    <Pagination :current-page="referrals.current_page" :last-page="referrals.last_page"
                        :base-url="route('users.referrals.show', referrer.id)" />
                </div>
            </CardContent>
        </Card>
    </AppContainer>
</template>
