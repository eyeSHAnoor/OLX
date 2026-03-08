<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto py-8 px-4">
            <h1 class="text-2xl font-bold mb-6">Report User</h1>

            <div v-if="reportedUser" class="bg-gray-50 p-4 rounded-lg mb-6">
                <div class="flex items-center">
                    <img :src="reportedUser.avatar ?? '/default-avatar.png'" class="w-12 h-12 rounded-full mr-4" />
                    <div>
                        <h3 class="font-semibold">{{ reportedUser.name }}</h3>
                        <p class="text-sm text-gray-600">{{ reportedUser.email }}</p>
                    </div>
                </div>
                <p v-if="ad" class="mt-2 text-sm text-gray-600">
                    Reporting for ad: "{{ ad.title }}"
                </p>
            </div>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Report *
                    </label>
                    <select v-model="form.reason"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': form.errors.reason }">
                        <option value="">Select a reason</option>
                        <option v-for="(label, value) in reasons" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                    <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">
                        {{ form.errors.reason }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Additional Details (Optional)
                    </label>
                    <textarea v-model="form.message" rows="4"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :class="{ 'border-red-500': form.errors.message }"
                        placeholder="Please provide any additional details that might help us investigate..."></textarea>
                    <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">
                        {{ form.errors.message }}
                    </p>
                </div>

                <div class="flex justify-end gap-4">
                    <button type="button" @click="$inertia.visit('/')"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit" :disabled="form.processing"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50">
                        {{ form.processing ? 'Submitting...' : 'Submit Report' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    reportedUser: Object,
    ad: Object,
    reasons: Object,
})

const form = useForm({
    reported_user_id: props.reportedUser?.id,
    ad_id: props.ad?.id,
    reason: '',
    message: '',
})

const submit = () => {
    form.post('/reports', {
        preserveScroll: true,
        onSuccess: () => {
            // Optionally close modal or redirect
        },
    })
}
</script>