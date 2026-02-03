<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import { BreadcrumbItemType, InertiaPageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<InertiaPageProps>();
const appSettings = computed(() => page.props.settings);
//
// let inactivityTimer = null
//
// const activityEvents = ['mousemove', 'keydown', 'mousedown', 'touchstart']
//
// const resetTimer = () => {
//     if (!appSettings.value?.enabled_inactive_session_logout) return
//
//     clearTimeout(inactivityTimer)
//
//     const timeoutInMs = (appSettings.value?.inactive_session_time ?? 2) * 60 * 1000
//
//     inactivityTimer = setTimeout(() => {
//         router.post(route('logout'))
//     }, timeoutInMs)
// }
//
// onMounted(() => {
//     if (!appSettings.value?.enabled_inactive_session_logout) return
//
//     resetTimer()
//     activityEvents.forEach(event =>
//         window.addEventListener(event, resetTimer)
//     )
// })
//
// onUnmounted(() => {
//     clearTimeout(inactivityTimer)
//     activityEvents.forEach(event =>
//         window.removeEventListener(event, resetTimer)
//     )
// })


</script>

<template>
    <AppShell class="flex-col mb-10 layout">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>

        <FlashMessage />

        <BaseAlertDialog />

    </AppShell>
</template>
