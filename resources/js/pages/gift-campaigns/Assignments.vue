<script setup lang="ts">
import { InertiaPageProps } from "@/types";
import { usePage, router, useForm } from "@inertiajs/vue3";
import { computed, ref, onMounted, watch } from "vue";
import Layout from "@/layouts/AppLayout.vue";
import { useAlertDialog } from "@/composables/useAlertDialog";
import {
    Gift,
    Users,
    CheckCircle,
    Clock,
    Search,
    Filter,
    XCircle,
    Truck,
    Package,
    Eye,
    MessageCircle,
    ChevronDown,
    ChevronUp,
    User,
    Phone,
    Mail,
    Calendar,
    MapPin,
    Star,
    Award,
    GiftIcon,
} from "lucide-vue-next";

defineOptions({ layout: Layout });

interface AssignedUser {
    id: number;
    assignment_id: number;
    name: string;
    email: string;
    phone: string | null;
    profile: {
        avatar: string | null;
        city: string | null;
        address: string | null;
    } | null;
    gift_name: string;
    gift_image: string | null;
    gift_description: string | null;
    assigned_at: string;
    status: string;
    notes: string | null;
    assigned_by_name: string;
    delivered_at: string | null;
    subscription_months: number;
    current_plan: string;
}

interface GiftStat {
    gift_name: string;
    allocated: number;
    assigned: number;
    remaining: number;
}

interface PageProps extends InertiaPageProps {
    period: App.Data.GiftPeriodData;
    assignments: PaginatedData<AssignedUser>;
    giftStats: GiftStat[];
    gifts: App.Data.GiftData[];
}

const page = usePage<PageProps>();
const period = computed(() => page.props.period);
const assignments = computed(() => page.props.assignments);
const giftStats = computed(() => page.props.giftStats);
const gifts = computed(() => page.props.gifts);

// Search and filter
const searchQuery = ref("");
const selectedStatus = ref<string>("");
const selectedGift = ref<string>("");
const expandedUser = ref<number | null>(null);

// Filtered assignments
const filteredAssignments = computed(() => {
    let data = assignments.value?.data || [];

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        data = data.filter(
            (assignment) =>
                assignment.name.toLowerCase().includes(query) ||
                assignment.email.toLowerCase().includes(query) ||
                assignment.phone?.toLowerCase().includes(query) ||
                assignment.gift_name.toLowerCase().includes(query)
        );
    }

    if (selectedStatus.value) {
        data = data.filter((assignment) => assignment.status === selectedStatus.value);
    }

    if (selectedGift.value) {
        data = data.filter((assignment) => assignment.gift_name === selectedGift.value);
    }

    return data;
});

// Status statistics
const statusStats = computed(() => {
    const allAssignments = assignments.value?.data || [];
    return {
        total: allAssignments.length,
        assigned: allAssignments.filter((a) => a.status === "assigned").length,
        delivered: allAssignments.filter((a) => a.status === "delivered").length,
        cancelled: allAssignments.filter((a) => a.status === "cancelled").length,
    };
});

// Unique gift names for filter
const giftNames = computed(() => {
    const names = new Set((assignments.value?.data || []).map((a) => a.gift_name));
    return Array.from(names);
});

// Toggle user details expansion
const toggleUserDetails = (userId: number) => {
    if (expandedUser.value === userId) {
        expandedUser.value = null;
    } else {
        expandedUser.value = userId;
    }
};

// Update assignment status
const updateStatusForm = useForm({
    status: "",
    notes: "",
});

const showStatusModal = ref(false);
const selectedAssignment = ref<AssignedUser | null>(null);

const openStatusModal = (assignment: AssignedUser) => {
    selectedAssignment.value = assignment;
    updateStatusForm.status = assignment.status;
    updateStatusForm.notes = assignment.notes || "";
    showStatusModal.value = true;
};

const updateStatus = () => {
    if (!selectedAssignment.value) return;

    updateStatusForm.patch(
        route(
            "gift-campaigns.update-assignment-status",
            selectedAssignment.value.assignment_id
        ),
        {
            preserveScroll: true,
            onSuccess: () => {
                showStatusModal.value = false;
                selectedAssignment.value = null;
                updateStatusForm.reset();
            },
        }
    );
};

// Get status badge
const getStatusBadge = (status: string) => {
    const badges = {
        assigned: {
            class: "bg-yellow-100 text-yellow-800",
            icon: Clock,
            label: "Assigned",
        },
        delivered: {
            class: "bg-green-100 text-green-800",
            icon: CheckCircle,
            label: "Delivered",
        },
        cancelled: {
            class: "bg-red-100 text-red-800",
            icon: XCircle,
            label: "Cancelled",
        },
        candidate: {
            class: "bg-blue-100 text-blue-800",
            icon: User,
            label: "Candidate",
        },
    };
    return badges[status] || badges.assigned;
};

// Format date
const formatDate = (date: string | null) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Format date without time
const formatDateOnly = (date: string | null) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

// Get unique statuses for filter
const statusOptions = computed(() => {
    const statuses = new Set((assignments.value?.data || []).map((a) => a.status));
    return Array.from(statuses);
});

// Breadcrumbs
const { set, resetList } = useBreadcrumb();
onMounted(() => {
    resetList();
    set([
        { label: "Home", href: "/dashboard" },
        { label: "Gift Campaigns", href: route("gift-campaigns.index") },
        {
            label: period.value?.name || "Campaign",
            href: route("gift-campaigns.eligible-users", period.value?.id),
        },
        { label: "Assignments", href: "#" },
    ]);
});
</script>

<template>
    <AppContainer>

        <Head :title="`Assignments - ${period?.name}`" />

        <PageHeading>
            <template #title>{{ period?.name }} - Assignments</template>
            <template #subtitle> Manage all gift assignments for this campaign </template>
            <template #links>
                <div class="flex items-center gap-2">
                    <Button as-child size="sm" variant="outline"
                        @click="router.visit(route('gift-campaigns.eligible-users', period?.id))">
                        <Icon icon="lucide:users" class="size-4" /> Eligible Users
                    </Button>
                    <Button as-child size="sm" variant="outline" @click="router.visit(route('gift-campaigns.index'))">
                        <Icon icon="lucide:arrow-left" class="size-4" /> Back
                    </Button>
                </div>
            </template>
        </PageHeading>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Total Assigned</p>
                            <p class="text-2xl font-bold">{{ statusStats.total }}</p>
                        </div>
                        <div class="size-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <Users class="size-6 text-blue-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Pending</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ statusStats.assigned }}</p>
                        </div>
                        <div class="size-12 rounded-full bg-yellow-100 flex items-center justify-center">
                            <Clock class="size-6 text-yellow-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Delivered</p>
                            <p class="text-2xl font-bold text-green-600">{{ statusStats.delivered }}</p>
                        </div>
                        <div class="size-12 rounded-full bg-green-100 flex items-center justify-center">
                            <CheckCircle class="size-6 text-green-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-muted-foreground">Cancelled</p>
                            <p class="text-2xl font-bold text-red-600">{{ statusStats.cancelled }}</p>
                        </div>
                        <div class="size-12 rounded-full bg-red-100 flex items-center justify-center">
                            <XCircle class="size-6 text-red-600" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Gift Stats -->
        <Card class="mt-4">
            <CardHeader>
                <CardTitle>Gift Distribution</CardTitle>
                <CardDescription>Overview of gift allocation and assignment progress</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="stat in giftStats" :key="stat.gift_name" class="p-4 border rounded-lg">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="size-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                <Gift class="size-5 text-primary" />
                            </div>
                            <div>
                                <h4 class="font-medium text-sm">{{ stat.gift_name }}</h4>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Allocated</span>
                                <span class="font-medium">{{ stat.allocated }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Assigned</span>
                                <span class="font-medium text-blue-600">{{ stat.assigned }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Remaining</span>
                                <span class="font-medium text-green-600">{{ stat.remaining }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full transition-all" :style="{
                                    width:
                                        stat.allocated > 0
                                            ? (stat.assigned / stat.allocated) * 100 + '%'
                                            : '0%',
                                }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Assignments Table -->
        <Card class="mt-4">
            <CardHeader>
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle>Assigned Users</CardTitle>
                        <CardDescription>
                            {{ filteredAssignments.length }} users assigned in this campaign
                        </CardDescription>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <!-- Filters -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <!-- Search -->
                    <div class="flex-1 min-w-[200px]">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                            <input v-model="searchQuery" type="text"
                                placeholder="Search by name, email, phone, or gift..."
                                class="w-full pl-10 pr-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" />
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="w-[150px]">
                        <select v-model="selectedStatus"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">All Status</option>
                            <option v-for="status in statusOptions" :key="status" :value="status">
                                {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                            </option>
                        </select>
                    </div>

                    <!-- Gift Filter -->
                    <div class="w-[200px]">
                        <select v-model="selectedGift"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">All Gifts</option>
                            <option v-for="giftName in giftNames" :key="giftName" :value="giftName">
                                {{ giftName }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Users List -->
                <div class="space-y-4">
                    <div v-for="assignment in filteredAssignments" :key="assignment.assignment_id"
                        class="border rounded-lg overflow-hidden">
                        <!-- User Row -->
                        <div class="flex items-center gap-4 p-4 cursor-pointer hover:bg-muted/30 transition-colors"
                            @click="toggleUserDetails(assignment.id)">
                            <!-- User Avatar -->
                            <div
                                class="size-12 rounded-full bg-primary/10 flex items-center justify-center overflow-hidden flex-shrink-0">
                                <img v-if="assignment.profile?.avatar" :src="`/storage/${assignment.profile.avatar}`"
                                    :alt="assignment.name" class="w-full h-full object-cover" />
                                <span v-else class="text-lg font-medium text-primary">
                                    {{ assignment.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>

                            <!-- User Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold truncate">{{ assignment.name }}</h3>
                                    <span :class="getStatusBadge(assignment.status).class"
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full">
                                        <component :is="getStatusBadge(assignment.status).icon" class="size-3 mr-1" />
                                        {{ getStatusBadge(assignment.status).label }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-muted-foreground mt-1">
                                    <span class="flex items-center gap-1">
                                        <Mail class="size-3" />
                                        {{ assignment.email }}
                                    </span>
                                    <span v-if="assignment.phone" class="flex items-center gap-1">
                                        <Phone class="size-3" />
                                        {{ assignment.phone }}
                                    </span>
                                </div>
                            </div>

                            <!-- Gift Info -->
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <div class="text-right">
                                    <p class="text-sm font-medium">{{ assignment.gift_name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDate(assignment.assigned_at) }}
                                    </p>
                                </div>
                                <div class="size-10 rounded-lg overflow-hidden bg-muted flex-shrink-0">
                                    <img v-if="assignment.gift_image" :src="`/storage/${assignment.gift_image}`"
                                        :alt="assignment.gift_name" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center">
                                        <Gift class="size-5 text-muted-foreground" />
                                    </div>
                                </div>
                            </div>

                            <!-- Expand Toggle -->
                            <component :is="expandedUser === assignment.id ? ChevronUp : ChevronDown"
                                class="size-5 text-muted-foreground flex-shrink-0" />

                            <!-- Actions -->
                            <button @click.stop="openStatusModal(assignment)"
                                class="p-2 hover:bg-muted rounded-md flex-shrink-0" title="Update Status">
                                <Eye class="size-4 text-muted-foreground" />
                            </button>
                        </div>

                        <!-- Expanded Details -->
                        <div v-if="expandedUser === assignment.id" class="border-t bg-muted/20 p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- User Details -->
                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold flex items-center gap-2">
                                        <User class="size-4" />
                                        User Details
                                    </h4>
                                    <div class="space-y-1 text-sm pl-6">
                                        <p>
                                            <span class="text-muted-foreground">Name:</span>
                                            {{ assignment.name }}
                                        </p>
                                        <p>
                                            <span class="text-muted-foreground">Email:</span>
                                            {{ assignment.email }}
                                        </p>
                                        <p v-if="assignment.phone">
                                            <span class="text-muted-foreground">Phone:</span>
                                            {{ assignment.phone }}
                                        </p>
                                        <p v-if="assignment.profile?.city">
                                            <span class="text-muted-foreground">City:</span>
                                            {{ assignment.profile.city }}
                                        </p>
                                        <p v-if="assignment.profile?.address">
                                            <span class="text-muted-foreground">Address:</span>
                                            {{ assignment.profile.address }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Subscription Details -->
                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold flex items-center gap-2">
                                        <Award class="size-4" />
                                        Subscription
                                    </h4>
                                    <div class="space-y-1 text-sm pl-6">
                                        <p>
                                            <span class="text-muted-foreground">Plan:</span>
                                            {{ assignment.current_plan || "N/A" }}
                                        </p>
                                        <p>
                                            <span class="text-muted-foreground">Months Active:</span>
                                            {{ assignment.subscription_months || "N/A" }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Gift Details -->
                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold flex items-center gap-2">
                                        <GiftIcon class="size-4" />
                                        Gift Details
                                    </h4>
                                    <div class="space-y-1 text-sm pl-6">
                                        <p>
                                            <span class="text-muted-foreground">Gift:</span>
                                            {{ assignment.gift_name }}
                                        </p>
                                        <p v-if="assignment.gift_description">
                                            <span class="text-muted-foreground">Description:</span>
                                            {{ assignment.gift_description }}
                                        </p>
                                        <p>
                                            <span class="text-muted-foreground">Assigned:</span>
                                            {{ formatDate(assignment.assigned_at) }}
                                        </p>
                                        <p>
                                            <span class="text-muted-foreground">Status:</span>
                                            <span :class="getStatusBadge(assignment.status).class"
                                                class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full">
                                                {{ getStatusBadge(assignment.status).label }}
                                            </span>
                                        </p>
                                        <p v-if="assignment.delivered_at">
                                            <span class="text-muted-foreground">Delivered:</span>
                                            {{ formatDate(assignment.delivered_at) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Assignment Info -->
                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold flex items-center gap-2">
                                        <Package class="size-4" />
                                        Assignment Info
                                    </h4>
                                    <div class="space-y-1 text-sm pl-6">
                                        <p>
                                            <span class="text-muted-foreground">Assigned By:</span>
                                            {{ assignment.assigned_by_name }}
                                        </p>
                                        <p v-if="assignment.notes">
                                            <span class="text-muted-foreground">Notes:</span>
                                            {{ assignment.notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredAssignments.length === 0" class="text-center py-12">
                        <Users class="size-16 text-muted-foreground mx-auto mb-4 opacity-50" />
                        <p class="text-lg font-medium text-muted-foreground">No assignments found</p>
                        <p class="text-sm text-muted-foreground mt-1">
                            Try adjusting your filters or assign gifts to eligible users
                        </p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="assignments?.links" class="mt-6">
                    <Pagination :links="assignments.links" />
                </div>
            </CardContent>
        </Card>

        <!-- Status Update Modal -->
        <div v-if="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" @click="showStatusModal = false"></div>
            <div class="relative bg-background rounded-lg shadow-lg p-6 w-full max-w-md mx-4">
                <h2 class="text-lg font-semibold mb-4">Update Assignment Status</h2>

                <div class="space-y-4">
                    <!-- Current Info -->
                    <div class="p-3 bg-muted/50 rounded-lg">
                        <p class="text-sm font-medium">{{ selectedAssignment?.name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ selectedAssignment?.gift_name }}
                        </p>
                    </div>

                    <!-- Status Select -->
                    <div>
                        <label class="text-sm font-medium block mb-2">Status *</label>
                        <select v-model="updateStatusForm.status"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="assigned">Assigned (Pending)</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <p v-if="updateStatusForm.errors.status" class="text-sm text-destructive mt-1">
                            {{ updateStatusForm.errors.status }}
                        </p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="text-sm font-medium block mb-2">Notes</label>
                        <textarea v-model="updateStatusForm.notes" rows="3"
                            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Add notes about this status change..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <AppButton label="Cancel" variant="outline" @click="showStatusModal = false"
                        :disabled="updateStatusForm.processing" />
                    <AppButton label="Update Status" icon="lucide:check" :processing="updateStatusForm.processing"
                        @click="updateStatus" class="bg-brand-orange hover:bg-brand-orange/80" />
                </div>
            </div>
        </div>
    </AppContainer>
</template>
