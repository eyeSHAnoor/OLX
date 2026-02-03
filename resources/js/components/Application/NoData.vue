<script setup lang="ts">
import { Icon } from '@iconify/vue';

defineProps<{
    icon?: string
    message: string
}>();
</script>

<template>
    <div class="flex h-20 items-center justify-center gap-3">
        <Icon :icon="icon ?? 'iconoir:window-no-access'" class="size-7" />
        <span>{{ message }}</span>
    </div>
</template>
