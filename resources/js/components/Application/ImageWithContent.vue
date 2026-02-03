<script setup lang="ts">
const props = defineProps({
    image: {
        type: String,
        default: '',
    },
})

const { getImage } = useHelpers();


</script>

<template>
    <div class="grid grid-cols-[35%_auto] gap-x-2 text-sm">
        <div :class="['inline-flex h-full w-full items-center justify-center', !image && 'bg-neutral-100']">
            <img :src="getImage(image)" class="h-24 w-auto rounded object-contain" />
        </div>
        <div class="w-full h-full">
            <slot />
        </div>
    </div>
</template>

