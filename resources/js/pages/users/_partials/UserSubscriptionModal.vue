<!-- UserSubscriptionModal.vue -->
<script setup lang="ts">
import { CardContent } from '@/components/ui/card';
import { usePage, router } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';
import { Icon } from '@iconify/vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const { user } = defineProps<{
    user?: App.Data.UserData;
}>();

const model = defineModel<boolean>();

const page = usePage();
const form = useForm({});

// Get user's subscription
const subscription = computed(() => user?.subscription);
const plan = computed(() => subscription.value?.plan);

// Format helpers
const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatCurrency = (amount: string | number) => {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR',
        minimumFractionDigits: 0
    }).format(Number(amount)).replace('PKR', '₨');
};

const getPaymentMethodIcon = (method: string) => {
    const icons = {
        jazzcash: 'mdi:cash',
        easypaisa: 'mdi:bank-transfer',
        bank: 'mdi:bank'
    };
    return icons[method] || 'mdi:credit-card';
};

const getStatusBadgeClass = (status: string) => {
    const classes = {
        completed: 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        pending: 'bg-yellow-100 text-yellow-700 border border-yellow-200',
        failed: 'bg-red-100 text-red-700 border border-red-200',
        refunded: 'bg-purple-100 text-purple-700 border border-purple-200'
    };
    return classes[status] || 'bg-gray-100 text-gray-700 border border-gray-200';
};

const getStatusIcon = (status: string) => {
    const icons = {
        completed: 'mdi:check-circle',
        pending: 'mdi:clock-outline',
        failed: 'mdi:close-circle',
        refunded: 'mdi:refresh'
    };
    return icons[status] || 'mdi:help-circle';
};

// Actions
const approveSubscription = () => {
    if (!subscription.value?.id) return;

    form.post(route('subscriptions.complete', user?.id), {
        preserveScroll: true,
        onSuccess: () => {
            model.value = false;
        }
    });
};

const rejectSubscription = () => {
    if (!subscription.value?.id) return;

    form.post(route('subscriptions.reject', user?.id), {
        preserveScroll: true,
        onSuccess: () => {
            model.value = false;
        }
    });
};

const viewReceipt = () => {
    if (subscription.value?.id) {
        window.open(
            route('receipts.show', subscription.value.id),
            '_blank'
        );
    }
};

const downloadReceipt = () => {
    if (subscription.value?.id) {
        window.open(route('receipts.download', subscription.value.id), '_blank');
    }
};

watch(model, (isOpen) => {
    if (isOpen) {
        form.reset();
        form.clearErrors();
    }
});
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="!w-7/12 !max-w-3xl !overflow-y-auto px-7">
            <DialogHeader class="!px-0 !pb-0">
                <DialogTitle class="flex items-center gap-2">
                    <Icon icon="mdi:file-document" class="text-blue-600 text-xl" />
                    Subscription Details
                </DialogTitle>
                <DialogDescription v-if="user">
                    Viewing subscription for {{ user.name }}
                </DialogDescription>
            </DialogHeader>

            <div class="mt-3 grid gap-y-4">
                <!-- User Info Summary -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4 border border-blue-100">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center text-white text-lg font-semibold">
                            {{ user?.name?.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ user?.name }}</h3>
                            <p class="text-sm text-gray-600">{{ user?.email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">User ID</p>
                            <p class="text-sm font-mono">#{{ user?.id }}</p>
                        </div>
                    </div>
                </div>

                <!-- No Subscription State -->
                <div v-if="!subscription" class="text-center py-8">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <Icon icon="mdi:close-circle" class="text-3xl text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">No Active Subscription</h3>
                    <p class="text-sm text-gray-500">This user doesn't have any subscription yet.</p>
                </div>

                <!-- Subscription Details -->
                <template v-else>
                    <!-- Status Banner -->
                    <div class="rounded-lg p-4" :class="getStatusBadgeClass(subscription.payment_status)">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Icon :icon="getStatusIcon(subscription.payment_status)" class="text-xl" />
                                <div>
                                    <p class="font-semibold">Payment Status: {{ subscription.payment_status }}</p>
                                    <p class="text-sm opacity-90">Subscription #{{ subscription.id }}</p>
                                </div>
                            </div>
                            <span class="text-sm bg-white/30 px-3 py-1 rounded-full">
                                {{ subscription.payment_gateway }}
                            </span>
                        </div>
                    </div>

                    <!-- Main Details Card -->
                    <Card>
                        <CardContent class="space-y-4 pt-4">
                            <!-- Plan Details -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-xs text-gray-500 mb-1">Plan</p>
                                    <p class="font-semibold text-gray-900">{{ plan?.name || 'N/A' }}</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-lg">
                                    <p class="text-xs text-gray-500 mb-1">Amount Paid</p>
                                    <p class="font-semibold text-lg text-blue-600">{{
                                        formatCurrency(subscription.amount_paid) }}</p>
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Started At</p>
                                    <div class="flex items-center gap-2 text-sm">
                                        <Icon icon="mdi:calendar-start" class="text-gray-400" />
                                        {{ formatDate(subscription.starts_at) }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Ends At</p>
                                    <div class="flex items-center gap-2 text-sm">
                                        <Icon icon="mdi:calendar-end" class="text-gray-400" />
                                        {{ formatDate(subscription.ends_at) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Details -->
                            <Separator />

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">Payment Information</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="flex items-center gap-2 text-sm">
                                        <Icon icon="mdi:credit-card" class="text-gray-400" />
                                        <div>
                                            <p class="text-gray-500">Method</p>
                                            <p class="font-medium capitalize">{{ subscription.payment_method }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm">
                                        <Icon icon="mdi:identifier" class="text-gray-400" />
                                        <div>
                                            <p class="text-gray-500">Transaction ID</p>
                                            <p class="font-medium font-mono text-xs">{{ subscription.transaction_id ||
                                                'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Receipt -->
                            <div v-if="subscription.receipt_image">
                                <label class="text-sm font-medium block mb-2">Payment Receipt</label>
                                <div class="flex gap-2">
                                    <button @click="viewReceipt"
                                        class="flex-1 flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                                        <div
                                            class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200">
                                            <Icon icon="mdi:file-image" class="text-blue-600 text-xl" />
                                        </div>
                                        <div class="flex-1 text-left">
                                            <p class="text-sm font-medium text-gray-900">View Receipt</p>
                                            <p class="text-xs text-gray-500">Click to view receipt</p>
                                        </div>
                                        <Icon icon="mdi:open-in-new" class="text-gray-400 group-hover:text-blue-600" />
                                    </button>

                                    <button @click="downloadReceipt"
                                        class="p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group"
                                        title="Download Receipt">
                                        <Icon icon="mdi:download"
                                            class="text-gray-600 group-hover:text-blue-600 text-xl" />
                                    </button>
                                </div>
                            </div>

                            <!-- Plan Features -->
                            <div v-if="plan?.features?.length">
                                <Separator />
                                <h4 class="text-sm font-semibold text-gray-900 mb-3 mt-2">Plan Features</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <div v-for="(feature, index) in plan.features" :key="index"
                                        class="flex items-center gap-2 text-sm text-gray-600">
                                        <Icon icon="mdi:check-circle" class="text-emerald-500 text-sm" />
                                        {{ feature }}
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Payment Data -->
                            <div v-if="subscription.payment_data && Object.keys(subscription.payment_data).length > 0">
                                <Separator />
                                <h4 class="text-sm font-semibold text-gray-900 mb-3 mt-2">Additional Payment Details
                                </h4>
                                <pre
                                    class="text-xs bg-gray-50 p-3 rounded-lg overflow-auto max-h-32">{{ JSON.stringify(subscription.payment_data, null, 2) }}</pre>
                            </div>
                        </CardContent>
                    </Card>
                </template>

                <!-- Action Buttons for Pending Subscriptions -->
                <DialogFooter v-if="subscription?.payment_status === 'pending'" class="flex items-center gap-2">
                    <div class="flex items-center justify-between gap-2 w-full">
                        <AppButton label="Reject" icon="lucide:ban" variant="danger" size="sm"
                            :processing="form.processing" @click="rejectSubscription" />
                        <div class="ml-auto flex items-center gap-2">
                            <AppButton size="sm" variant="outline" label="Cancel" @click="model = false"
                                :disabled="form.processing" />
                            <AppButton size="sm" variant="success" :processing="form.processing" label="Approve Payment"
                                icon="mdi:check-circle" @click="approveSubscription" />
                        </div>
                    </div>
                </DialogFooter>

                <!-- Close Button for Non-pending -->
                <DialogFooter v-else>
                    <div class="flex items-center justify-end gap-2 w-full">
                        <AppButton size="sm" label="Close" @click="model = false" />
                    </div>
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>