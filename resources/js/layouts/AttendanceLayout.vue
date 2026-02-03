<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AttendanceHeader from '@/components/AttendanceHeader.vue';
// import type { BreadcrumbItemType } from '@/types';
import FlashMessage from '@/components/Application/FlashMessage.vue';
import BaseAlertDialog from '@/components/Application/BaseAlertDialog.vue';

// withDefaults(defineProps<{
//     breadcrumbs?: BreadcrumbItemType[];
// }>(), {
//     breadcrumbs: () => [],
// });

</script>

<template>
    <AppShell class="layout mb-10 flex-col">
        <AttendanceHeader />
        <AppContent>
            <slot />
        </AppContent>

        <FlashMessage />

        <BaseAlertDialog />
    </AppShell>
</template>
