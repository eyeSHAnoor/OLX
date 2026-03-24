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
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const model = defineModel<boolean>({ default: false });
useForceTheme('light');
const props = defineProps<{
    ad?: any;
    user?: any;
    reasons?: Record<string, string>;
}>();

const emit = defineEmits(['submitted']);

const defaultReasons = {
    scam: 'Scam or Fraud',
    spam: 'Spam',
    abusive: 'Abusive Behavior',
    fake_listing: 'Fake Listing',
    inappropriate: 'Inappropriate Content',
    other: 'Other',
};

const reasons = props.reasons || defaultReasons;

const form = useForm({
    reported_user_id: props.ad?.user_id || props.user?.id,
    ad_id: props.ad?.id || null,
    reason: '',
    message: '',
});

const submitReport = () => {
    form.post('/reports', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('reason', 'message');
            emit('submitted');
            setTimeout(() => {
                closeModal();
            }, 2000);
        },
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset('reason', 'message');
    model.value = false;
};

// Reset form when modal closes
watch(model, (isOpen) => {
    if (!isOpen) {
        form.clearErrors();
        form.reset('reason', 'message');
    }
});
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="flex max-h-[90vh] w-full max-w-[90vw] flex-col sm:max-w-[500px]">
            <DialogHeader class=" pb-0">
                <DialogTitle class="flex items-center gap-2 text-lg font-semibold sm:text-xl">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100">
                        <Icon icon="lucide:flag" class="h-4 w-4 text-red-600" />
                    </div>
                    <span>Report {{ ad ? 'Ad' : 'User' }}</span>
                </DialogTitle>
            </DialogHeader>

            <!-- Reported Item Summary -->
            <div v-if="ad || user" class=" rounded-lg bg-muted/30 pt-3">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-200">
                        <img v-if="ad?.images?.[0]" :src="`/storage/${ad.images[0].path}`"
                            class="h-full w-full object-cover" />
                        <Icon v-else icon="lucide:image" class="h-6 w-6 text-gray-400" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p v-if="ad" class="truncate text-sm font-medium text-gray-900">
                            {{ ad.ad_title }}
                        </p>
                        <p v-if="user" class="truncate text-sm font-medium text-gray-900">
                            {{ user.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            Posted by {{ ad?.user?.name || user?.name }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Content with Scroll -->
            <div class="flex-1 overflow-y-auto py-4">
                <form @submit.prevent="submitReport" class="space-y-4">
                    <!-- Reason Select -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">
                            Reason for Report <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.reason"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            :class="{ 'border-red-500': form.errors.reason }">
                            <option value="">Select a reason</option>
                            <option v-for="(label, value) in reasons" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                        <p v-if="form.errors.reason" class="text-xs text-red-600">
                            {{ form.errors.reason }}
                        </p>
                    </div>

                    <!-- Message Field -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-700">
                            Additional Details <span class="text-gray-400">(Optional)</span>
                        </label>
                        <textarea v-model="form.message" rows="4"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            :class="{ 'border-red-500': form.errors.message }"
                            placeholder="Please provide any additional details that might help us investigate..."></textarea>
                        <p v-if="form.errors.message" class="text-xs text-red-600">
                            {{ form.errors.message }}
                        </p>
                    </div>

                    <!-- Error Message -->
                    <div v-if="form.errors.reported_user_id" class="rounded-md bg-red-50 p-3 text-sm text-red-600">
                        {{ form.errors.reported_user_id }}
                    </div>

                    <!-- Success Message -->
                    <div v-if="form.recentlySuccessful" class="rounded-md bg-green-50 p-3 text-sm text-green-600">
                        Report submitted successfully. Thank you for helping keep our community safe!
                    </div>
                </form>
            </div>

            <DialogFooter class="flex flex-col-reverse gap-2 border-t px-6 py-4 sm:flex-row sm:justify-end">
                <Button variant="outline" @click="closeModal" :disabled="form.processing" class="w-full sm:w-auto">
                    Cancel
                </Button>
                <Button @click="submitReport" :disabled="form.processing"
                    class="w-full bg-red-600 text-white hover:bg-red-700 sm:w-auto">
                    <Icon v-if="form.processing" icon="lucide:loader-2" class="mr-2 h-4 w-4 animate-spin" />
                    {{ form.processing ? 'Submitting...' : 'Submit Report' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Custom scrollbar for the content area */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>