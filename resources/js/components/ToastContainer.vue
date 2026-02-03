<template>
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
        <div v-for="t in toasts" :key="t.id" class="bg-white shadow px-4 py-2 rounded border">
            <div class="font-semibold">{{ t.title }}</div>
            <div v-if="t.description" class="text-sm text-gray-600">{{ t.description }}</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToast';

const { toasts } = useToast();
</script>

<style scoped>
/* Optional: add transitions if you like */
</style>
