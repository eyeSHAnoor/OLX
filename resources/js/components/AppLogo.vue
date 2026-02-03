<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    fullLogo?: boolean;
}

defineProps<Props>();

</script>

<template>
    <div class="flex aspect-square items-center justify-center rounded-md image-">
        <AppLogoIcon fullLogo class="w-auto object-contain" />
    </div>
</template>
