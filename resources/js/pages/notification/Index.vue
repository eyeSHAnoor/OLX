<script setup lang="ts">
import { InertiaPageProps, PaginatedData } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import Layout from '@/layouts/AppLayout.vue'
import AppContainer from '@/components/Application/AppContainer.vue'
import PageHeading from '@/components/Application/PageHeading.vue'
import { Icon } from '@iconify/vue'
import useModal from '@/composables/useModal'

defineOptions({ layout: Layout })

const { handleShowModal, showModal, selectedItem } = useModal()
const { formatDate } = useHelpers()

// Props from Laravel
interface PageProps extends InertiaPageProps {
    notifications: PaginatedData<{
        id: number
        title: string
        message: string
        type: string
        url: string | null
        action_by: { id: number; name: string } | null
        requested_by: { id: number; name: string } | null
        created_at: string
        updated_at: string
    }>
}

const page = usePage<PageProps>()
const notifications = computed(() => page.props.notifications)
</script>

<template>
    <AppContainer>

        <Head title="Notifications" />

        <PageHeading>
            <template #title>Notifications</template>
            <template #links>
            </template>
        </PageHeading>

        <div class="mt-4 grid grid-cols-1 gap-4">
            <Card v-for="notification in notifications.data" :key="notification.id"
                class="hover:shadow-md transition-shadow cursor-pointer" @click="handleShowModal(notification)">
                <CardContent>
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-semibold text-sm">{{ notification.title }}</h3>
                        <span class="text-xs text-gray-500">{{ notification.type }}</span>
                    </div>
                    <div class="text-xs text-gray-400 flex justify-between">
                        <p class="text-gray-700 mb-2 text-xs">{{ notification.message }}</p>
                        <div>Created: {{ formatDate(notification.created_at) }}</div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Modal for editing/viewing notification -->
        <NotificationModal :key="showModal ? 'modal-open' : 'modal-closed'" :notification="selectedItem"
            v-model="showModal" />
    </AppContainer>
</template>
