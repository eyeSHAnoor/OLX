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

const props = defineProps<{
    report: any;
    statuses?: Record<string, string>;
}>();

const emit = defineEmits(['submitted']);

const form = useForm({
    status: props.report?.status || 'reviewed',
    response_message: '',
    notify_reporter: true,
    take_action_against_user: false,
    action_type: null as string | null,
});

const submitResponse = () => {
    form.post(route('reports.respond', props.report?.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('response_message', 'action_type');
            emit('submitted');
            setTimeout(() => {
                model.value = false;
            }, 500);
        },
    });
};

// Reset form when modal closes
watch(model, (isOpen) => {
    if (!isOpen) {
        form.clearErrors();
        form.reset('response_message', 'action_type');
    }
});

// Reset action type when take_action_against_user is false
watch(() => form.take_action_against_user, (newVal) => {
    if (!newVal) {
        form.action_type = null;
    }
});
</script>

<template>
    <Dialog v-model:open="model">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100">
                        <Icon icon="lucide:message-circle" class="h-4 w-4 text-red-600" />
                    </div>
                    <span>Respond to Report #{{ report?.id }}</span>
                </DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submitResponse" class="space-y-4 py-4">
                <!-- Status Update -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">
                        Update Status <span class="text-red-500">*</span>
                    </label>
                    <select v-model="form.status"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                        :class="{ 'border-red-500': form.errors.status }">
                        <option v-for="(label, value) in statuses" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                    <p v-if="form.errors.status" class="text-xs text-red-600">
                        {{ form.errors.status }}
                    </p>
                </div>

                <!-- Response Message -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">
                        Response Message <span class="text-red-500">*</span>
                    </label>
                    <textarea v-model="form.response_message" rows="4"
                        class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                        :class="{ 'border-red-500': form.errors.response_message }"
                        placeholder="Write your response to the reporter..."></textarea>
                    <p v-if="form.errors.response_message" class="text-xs text-red-600">
                        {{ form.errors.response_message }}
                    </p>
                </div>

                <!-- Notify Reporter -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="notify_reporter" v-model="form.notify_reporter"
                        class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500" />
                    <label for="notify_reporter" class="text-sm font-medium text-gray-700">
                        Notify reporter via email
                    </label>
                </div>

                <!-- Take Action Against User -->
                <div class="space-y-3 border-t pt-3">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="take_action" v-model="form.take_action_against_user"
                            class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500" />
                        <label for="take_action" class="text-sm font-medium text-gray-700">
                            Take action against reported user
                        </label>
                    </div>

                    <!-- Action Type Dropdown (shown only if take_action is checked) -->
                    <div v-if="form.take_action_against_user" class="ml-6 space-y-2">
                        <label class="text-sm font-medium text-gray-700">
                            Action Type <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.action_type"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"
                            :class="{ 'border-red-500': form.errors.action_type }">
                            <option value="">Select action</option>
                            <option value="warn">Send Warning</option>
                            <option value="suspend">Suspend Account (7 days)</option>
                            <option value="ban">Ban Account Permanently</option>
                            <option value="scam">Scam</option>
                            <option value="fake_listing">Fake Listing</option>
                        </select>
                        <p v-if="form.errors.action_type" class="text-xs text-red-600">
                            {{ form.errors.action_type }}
                        </p>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="form.errors.take_action_against_user" class="rounded-md bg-red-50 p-3 text-sm text-red-600">
                    {{ form.errors.take_action_against_user }}
                </div>

                <!-- Success Message -->
                <div v-if="form.recentlySuccessful" class="rounded-md bg-green-50 p-3 text-sm text-green-600">
                    Response sent successfully!
                </div>
            </form>

            <DialogFooter class="flex gap-2">
                <Button variant="outline" @click="model = false" :disabled="form.processing" class="flex-1">
                    Cancel
                </Button>
                <Button @click="submitResponse" :disabled="form.processing"
                    class="flex-1 bg-red-600 text-white hover:bg-red-700">
                    <Icon v-if="form.processing" icon="lucide:loader-2" class="mr-2 h-4 w-4 animate-spin" />
                    {{ form.processing ? 'Sending...' : 'Send Response' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>