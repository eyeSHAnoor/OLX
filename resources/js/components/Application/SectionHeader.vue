<script setup lang="ts">
interface Props {
    title: string
    icon: string
    containerClass?: string
    titleClass?: string
    iconClass?: string
    separatorClass?: string
}

withDefaults(defineProps<Props>(), {
    containerClass: '',
    titleClass: 'inline-flex items-center gap-x-1 text-lg font-semibold',
    iconClass: 'size-5',
    separatorClass: 'mt-1'
})
</script>

<template>
    <div :class="['col-span-full', containerClass ]">
        <h4 class="inline-flex items-center gap-x-1 text-lg font-semibold">
            <Icon :icon="icon" class="size-5" />
            {{ title }}
        </h4>
        <Separator class="mt-1" />
    </div>
</template>

