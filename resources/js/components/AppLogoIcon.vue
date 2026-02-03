<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
    fullLogo?: boolean;
}

defineProps<Props>();
</script>

<template>
    <img :src="fullLogo ? '/images/full-logo.png' : '/images/logo.png'" alt="Logo" :class="className" v-bind="$attrs" />
</template>
