<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="text-muted-foreground ml-1 text-xs">
            {{ message }}
        </p>
    </div>
</template>
