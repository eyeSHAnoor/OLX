<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ResponseModal from './ResponseModal.vue';

const model = defineModel<boolean>({ default: false });

const props = defineProps<{
    report: any;
    statuses?: Record<string, string>;
    reasons?: Record<string, string>;
}>();

const emit = defineEmits(['responded']);

// Response modal state
const showResponseModal = ref(false);

// Format date
const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Get status badge class
const getStatusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'reviewed': 'bg-blue-100 text-blue-800',
        'resolved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

// Get reason label
const getReasonLabel = (reason: string) => {
    return props.reasons?.[reason] || reason.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

// Get reason icon
const getReasonIcon = (reason: string) => {
    const icons: Record<string, string> = {
        'scam': 'lucide:alert-triangle',
        'spam': 'lucide:mail',
        'abusive': 'lucide:alert-octagon',
        'fake_listing': 'lucide:copy-x',
        'inappropriate': 'lucide:ban',
        'other': 'lucide:help-circle',
    };
    return icons[reason] || 'lucide:flag';
};

// Open response modal
const openResponseModal = () => {
    showResponseModal.value = true;
};

// Handle response submitted
const handleResponseSubmitted = () => {
    showResponseModal.value = false;
    model.value = false;
    emit('responded');
};

// View ad
const viewAd = () => {
    if (props.report?.ad) {
        window.open(`/ads/${props.report.ad.id}`, '_blank');
    }
};

// View user profile
const viewUserProfile = (userId: number) => {
    window.open(`/user/${userId}`, '_blank');
};

// Get initials for avatar
const getInitials = (name: string) => {
    if (!name) return 'U';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

// Get avatar color based on name
const getAvatarColor = (name: string) => {
    const colors = [
        'bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500',
        'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500'
    ];
    if (!name) return colors[0];
    const index = name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) % colors.length;
    return colors[index];
};
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="flex max-h-[90vh] w-full max-w-[90vw] flex-col lg:max-w-[900px]">
            <DialogHeader class="px-6 pt-6 pb-0">
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold sm:text-xl">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100">
                        <Icon icon="lucide:flag" class="h-4 w-4 text-red-600" />
                    </div>
                    <span>Report Details #{{ report?.id }}</span>

                    <!-- Status Badge -->
                    <span v-if="report" class="ml-2 px-2 py-1 text-xs rounded-full"
                        :class="getStatusBadgeClass(report.status)">
                        {{ statuses?.[report.status] || report.status }}
                    </span>
                </DialogTitle>
            </DialogHeader>

            <!-- Content with Scroll -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
                <div v-if="report" class="space-y-6">
                    <!-- Report Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Reason Card -->
                        <div class="bg-muted/30 p-4 rounded-lg">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-red-100 rounded-full">
                                    <Icon :icon="getReasonIcon(report.reason)" class="size-5 text-red-600" />
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground">Reason</p>
                                    <p class="text-sm font-medium">{{ getReasonLabel(report.reason) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Date Card -->
                        <div class="bg-muted/30 p-4 rounded-lg">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-blue-100 rounded-full">
                                    <Icon icon="lucide:calendar" class="size-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-xs text-muted-foreground">Submitted On</p>
                                    <p class="text-sm font-medium">{{ formatDate(report.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reported User Section -->
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-muted/50 px-4 py-2 border-b">
                            <h3 class="text-sm font-medium flex items-center gap-2">
                                <Icon icon="lucide:user" class="size-4" />
                                Reported User
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full text-white font-semibold text-sm"
                                        :class="getAvatarColor(report.reported_user?.name)">
                                        {{ getInitials(report.reported_user?.name) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium">{{ report.reported_user?.name }}</p>
                                            <span class="text-xs bg-gray-100 px-2 py-0.5 rounded">
                                                ID: {{ report.reported_user?.id }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-muted-foreground">{{ report.reported_user?.email }}</p>
                                        <p v-if="report.reported_user?.phone" class="text-sm text-muted-foreground">
                                            📞 {{ report.reported_user?.phone }}
                                        </p>
                                        <p class="text-xs text-muted-foreground mt-1">
                                            Joined: {{ formatDate(report.reported_user?.created_at) }}
                                        </p>
                                    </div>
                                </div>
                                <Button variant="outline" size="sm" @click="viewUserProfile(report.reported_user?.id)">
                                    <Icon icon="lucide:external-link" class="size-4 mr-1" />
                                    View Profile
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Reporter Section -->
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-muted/50 px-4 py-2 border-b">
                            <h3 class="text-sm font-medium flex items-center gap-2">
                                <Icon icon="lucide:user-check" class="size-4" />
                                Reported By
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full text-white font-semibold text-sm"
                                        :class="getAvatarColor(report.reporter?.name)">
                                        {{ getInitials(report.reporter?.name) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium">{{ report.reporter?.name }}</p>
                                            <span class="text-xs bg-gray-100 px-2 py-0.5 rounded">
                                                ID: {{ report.reporter?.id }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-muted-foreground">{{ report.reporter?.email }}</p>
                                        <p v-if="report.reporter?.phone" class="text-sm text-muted-foreground">
                                            📞 {{ report.reporter?.phone }}
                                        </p>
                                    </div>
                                </div>
                                <Button variant="outline" size="sm" @click="viewUserProfile(report.reporter?.id)">
                                    <Icon icon="lucide:external-link" class="size-4 mr-1" />
                                    View Profile
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Reported Ad Section (if exists) -->
                    <div v-if="report.ad" class="border rounded-lg overflow-hidden">
                        <div class="bg-muted/50 px-4 py-2 border-b">
                            <h3 class="text-sm font-medium flex items-center gap-2">
                                <Icon icon="lucide:image" class="size-4" />
                                Reported Ad
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                            <img v-if="report.ad.images?.[0]"
                                                :src="`/storage/${report.ad.images[0].path}`"
                                                class="w-full h-full object-cover" />
                                            <Icon v-else icon="lucide:image" class="w-full h-full p-2 text-gray-400" />
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ report.ad.ad_title }}</p>
                                            <p class="text-sm text-muted-foreground">
                                                Rs. {{ Number(report.ad.price).toLocaleString() }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                Posted by: {{ report.ad.user?.name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <Button variant="outline" size="sm" @click="viewAd">
                                    <Icon icon="lucide:external-link" class="size-4 mr-1" />
                                    View Ad
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Reporter's Message -->
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-muted/50 px-4 py-2 border-b">
                            <h3 class="text-sm font-medium flex items-center gap-2">
                                <Icon icon="lucide:message-square" class="size-4" />
                                Reporter's Message
                            </h3>
                        </div>
                        <div class="p-4">
                            <p v-if="report.message" class="text-sm whitespace-pre-line">{{ report.message }}</p>
                            <p v-else class="text-sm text-muted-foreground italic">No additional message provided.</p>
                        </div>
                    </div>

                    <!-- Admin Response (if exists) -->
                    <div v-if="report.admin_response" class="border rounded-lg overflow-hidden border-green-200">
                        <div class="bg-green-50 px-4 py-2 border-b border-green-200">
                            <h3 class="text-sm font-medium flex items-center gap-2 text-green-700">
                                <Icon icon="lucide:message-circle" class="size-4" />
                                Admin Response
                            </h3>
                        </div>
                        <div class="p-4 bg-green-50/30">
                            <p class="text-sm whitespace-pre-line">{{ report.admin_response }}</p>
                            <p v-if="report.responded_at" class="text-xs text-muted-foreground mt-2">
                                Responded on: {{ formatDate(report.responded_at) }}
                            </p>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="border rounded-lg overflow-hidden">
                        <div class="bg-muted/50 px-4 py-2 border-b">
                            <h3 class="text-sm font-medium flex items-center gap-2">
                                <Icon icon="lucide:clock" class="size-4" />
                                Timeline
                            </h3>
                        </div>
                        <div class="p-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2 text-sm">
                                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    <span class="text-muted-foreground">Reported:</span>
                                    <span class="font-medium">{{ formatDate(report.created_at) }}</span>
                                </div>
                                <div v-if="report.responded_at" class="flex items-center gap-2 text-sm">
                                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                    <span class="text-muted-foreground">Responded:</span>
                                    <span class="font-medium">{{ formatDate(report.responded_at) }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm">
                                    <div class="w-2 h-2 rounded-full"
                                        :class="report.status === 'resolved' ? 'bg-green-500' : 'bg-yellow-500'">
                                    </div>
                                    <span class="text-muted-foreground">Current Status:</span>
                                    <span class="font-medium px-2 py-0.5 rounded-full text-xs"
                                        :class="getStatusBadgeClass(report.status)">
                                        {{ statuses?.[report.status] || report.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex items-center justify-between gap-2 border-t px-6 py-4">
                <div class="flex gap-2">
                    <Button variant="outline" @click="model = false">
                        Close
                    </Button>
                </div>
                <Button @click="openResponseModal" class="bg-red-600 text-white hover:bg-red-700">
                    <Icon icon="lucide:message-circle" class="size-4 mr-2" />
                    Respond to Report
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Response Modal -->
    <ResponseModal v-model="showResponseModal" :report="report" :statuses="statuses"
        @submitted="handleResponseSubmitted" />
</template>